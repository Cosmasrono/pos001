<?php

namespace App\Http\Controllers;
use App\Models\ProductBranchStock;
use App\Models\Product;
use App\Models\Category;
use App\Models\Branch;
use Illuminate\Http\Request;
use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;

class ProductController extends Controller
{
    public function index(Request $request): View
    {
        $user = auth()->user();
        $filter = $request->get('filter', 'active'); // all, active, inactive
        $canSeeAllBranches = !$user->branch_id || $user->isOwner() || $user->isPlatformAdmin();
        
        // Build base query - always load all products with their branch stocks
        $query = Product::with('category', 'branchStocks.branch');
        
        // If user is restricted to a single branch, filter to show only their branch stocks
        if ($user && $user->branch_id && !$canSeeAllBranches) {
            $query->with(['branchStocks' => function($q) use ($user) {
                $q->where('branch_id', $user->branch_id);
            }]);
        }

        // Apply status filter
        if ($filter === 'active') {
            $query->where('is_active', true);
        } elseif ($filter === 'inactive') {
            $query->where('is_active', false);
        }
        // 'all' shows both active and inactive

        $query->latest();

        $products = $query->paginate(15)->appends(['filter' => $filter]);

        return view('products.index', [
            'products' => $products,
            'filter' => $filter
        ]);
    }

    public function create()
    {
        $user = auth()->user();
        $canAllocateAcrossBranches = !$user->branch_id || $user->isOwner() || $user->isPlatformAdmin();

        // No more blocking. If branches don't exist or the user's branch is gone,
        // store() will auto-create a Main Branch when the form is submitted.
        $branches = Branch::where('is_active', true)
            ->orderByDesc('is_main')
            ->orderBy('name')
            ->get();

        $categories = Category::orderBy('name')->get();

        return view('products.create', [
            'branches'   => $branches,
            'categories' => $categories,
            'canAllocateAcrossBranches' => $canAllocateAcrossBranches,
        ]);
    }

public function store(Request $request): RedirectResponse
{
    $validated = $request->validate([
        'name'                    => 'required|string|max:255',
        'sku'                     => 'nullable|unique:products|string|max:100',
        'barcode'                 => 'nullable|unique:products|string|max:100',
        'description'             => 'nullable|string',
        'category_id'             => 'required|exists:categories,id',
        'cost_price'              => 'nullable|numeric|min:0',
        'selling_price'           => 'required|numeric|min:0',
        'reorder_level'           => 'required|integer|min:0',
        'total_stock'             => 'nullable|integer|min:0',
        'branch_quantities'       => 'nullable|array',
        'branch_quantities.*'     => 'nullable|integer|min:0',
    ]);

    $validated['cost_price'] ??= 0;

    // Auto-generate SKU based on timestamp: ddmmyy-hhmmss-microseconds
    if (empty($validated['sku'])) {
        $now = now();
        $validated['sku'] = $now->format('dmy-His') . '-' . substr(microtime(false), 2, 6);
    }

    $user = auth()->user();
    $canAllocateAcrossBranches = !$user->branch_id || $user->isOwner() || $user->isPlatformAdmin();

    // Get all branch quantities from request and force integer keys/values
    $rawBranchQtys = $request->input('branch_quantities', []);
    $branchQuantities = collect($rawBranchQtys)->mapWithKeys(function ($qty, $id) {
        return [(int) $id => max(0, (int) $qty)];
    });

    $inputTotalStock = $request->input('total_stock');

    if ($canAllocateAcrossBranches) {
        // Multi-branch allocator (SuperAdmin / Owner): handle total_stock and Main Branch remainder.
        // If no branches exist at all OR none flagged as main, auto-create one so we never block the user.
        $mainBranch = Branch::where('is_active', true)->where('is_main', true)->first()
                      ?? Branch::where('is_active', true)->where('name', 'LIKE', '%Main%')->first()
                      ?? Branch::where('is_active', true)->first()
                      ?? $this->ensureDefaultBranch($user);

        // If total_stock is missing or 0, calculate it from the sum of branch quantities
        if (empty($inputTotalStock) || (int)$inputTotalStock === 0) {
            $totalStock = $branchQuantities->sum();
        } else {
            $totalStock = (int) $inputTotalStock;
        }

        $mainBranchId = $mainBranch->id;
        if (!$branchQuantities->has($mainBranchId)) {
            $branchQuantities->put($mainBranchId, 0);
        }
        $otherAllocations = $branchQuantities->except($mainBranchId)->sum();
        $branchQuantities[$mainBranchId] = max(0, $totalStock - $otherAllocations);

        $validated['quantity_in_stock'] = $totalStock;
    } else {
        // Single-branch user: try their assigned branch; if missing/inactive, auto-create one.
        $userBranch = Branch::find($user->branch_id);
        if (!$userBranch || !$userBranch->is_active) {
            $userBranch = $this->ensureDefaultBranch($user);
            // Re-attach the user to the auto-created branch so future actions don't fall back again.
            $user->forceFill(['branch_id' => $userBranch->id])->save();
        }

        $qty = $branchQuantities->get($user->branch_id, $branchQuantities->first() ?? 0);
        $totalStock = (int) $qty;
        $validated['quantity_in_stock'] = $totalStock;
        $branchQuantities = collect([(int)$userBranch->id => $totalStock]);
    }

    $product = Product::create($validated);

    // Record stock for each branch
    foreach ($branchQuantities as $branchId => $qty) {
        if ($qty >= 0) {
            ProductBranchStock::updateOrCreate(
                ['product_id' => $product->id, 'branch_id' => $branchId],
                ['quantity_in_stock' => $qty, 'initial_allocation' => $qty]
            );
        }
    }

    return redirect()->route('products.index')
        ->with('success', 'Product created successfully with ' . number_format($totalStock) . ' total units allocated.');
}

/**
 * Make sure at least one active branch exists. If none does, create a "Main Branch"
 * for the current user's company so product creation never gets blocked.
 */
protected function ensureDefaultBranch($user): Branch
{
    $branch = Branch::where('is_active', true)->first();
    if ($branch) {
        return $branch;
    }

    return Branch::create([
        'name'       => 'Main Branch',
        'code'       => 'MAIN',
        'is_main'    => true,
        'is_active'  => true,
        'company_id' => $user->company_id ?? null, // BelongsToCompany trait will fill if scoped
        'owner_id'   => $user->id,
    ]);
}


    public function show($idOrSku): View
    {
        $product = Product::where('id', $idOrSku)
            ->orWhere('sku', $idOrSku)
            ->orWhere('barcode', $idOrSku)
            ->firstOrFail();

        $product->load('category', 'branchStocks.branch', 'stockMovements');
        return view('products.show', ['product' => $product]);
    }

    public function edit(Product $product): View
    {
        $branches = Branch::all();
        $categories = Category::orderBy('name')->get();
        $product->load('branchStocks');
        return view('products.edit', [
            'product' => $product, 
            'branches' => $branches,
            'categories' => $categories
        ]);
    }

    public function update(Request $request, Product $product): RedirectResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'sku' => 'required|unique:products,sku,' . $product->id . '|string|max:100',
            'barcode' => 'nullable|unique:products,barcode,' . $product->id . '|string|max:100',
            'description' => 'nullable|string',
            'category_id' => 'required|exists:categories,id',
            'cost_price' => 'nullable|numeric|min:0',
            'selling_price' => 'required|numeric|min:0',
            'reorder_level' => 'required|integer|min:0',
            'is_active' => 'boolean',
            'total_initial_stock' => 'required|integer|min:0',
        ]);

        $validated['cost_price'] ??= 0;
        $validated['quantity_in_stock'] = $validated['total_initial_stock'];

        $product->update($validated);

        $user = auth()->user();
        $canAllocateAcrossBranches = !$user->branch_id || $user->isOwner() || $user->isPlatformAdmin();
        $totalStock = $validated['total_initial_stock'];

        // If user can allocate across branches, redistribute to all branches
        if ($canAllocateAcrossBranches) {
            $product->branchStocks()->delete();
            
            $branches = Branch::where('is_active', true)->get();
            $allocatedTotal = 0;

            foreach ($branches as $branch) {
                $percentage = $branch->stock_distribution_percentage ?? 0;
                
                if ($percentage > 0) {
                    $qty = (int) round(($totalStock * $percentage) / 100);
                    
                    if ($qty > 0) {
                        ProductBranchStock::create([
                            'product_id'        => $product->id,
                            'branch_id'         => $branch->id,
                            'quantity_in_stock' => $qty,
                            'initial_allocation' => $qty,
                        ]);
                        $allocatedTotal += $qty;
                    }
                }
            }

            // If no branches had percentages, distribute equally
            if ($allocatedTotal === 0 && $branches->isNotEmpty()) {
                $qtyPerBranch = (int) floor($totalStock / $branches->count());
                
                foreach ($branches as $branch) {
                    if ($qtyPerBranch > 0) {
                        ProductBranchStock::create([
                            'product_id'        => $product->id,
                            'branch_id'         => $branch->id,
                            'quantity_in_stock' => $qtyPerBranch,
                            'initial_allocation' => $qtyPerBranch,
                        ]);
                    }
                }
            }
        } else {
            // If user is a branch user, update only their branch's stock
            $branchStock = $product->branchStocks()->where('branch_id', $user->branch_id)->first();
            
            if ($branchStock) {
                $branchStock->update([
                    'quantity_in_stock' => $totalStock,
                    'initial_allocation' => $totalStock,
                ]);
            } else {
                // Create if doesn't exist
                ProductBranchStock::create([
                    'product_id'        => $product->id,
                    'branch_id'         => $user->branch_id,
                    'quantity_in_stock' => $totalStock,
                    'initial_allocation' => $totalStock,
                ]);
            }
        }

        return redirect()->route('products.show', $product)
            ->with('success', 'Product updated successfully');
    }

    public function destroy(Product $product): RedirectResponse
    {
        $product->update(['is_active' => false]);
        return redirect()->route('products.index')
            ->with('success', 'Product deactivated successfully');
    }

    public function addStock(Request $request): RedirectResponse
    {
        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id' => 'required|exists:branches,id', // Now requires branch
            'quantity' => 'required|integer|min:1',
            'reference' => 'nullable|string|max:255',
        ]);

        $stock = ProductBranchStock::firstOrCreate(
            ['product_id' => $validated['product_id'], 'branch_id' => $validated['branch_id']],
            ['quantity_in_stock' => 0]
        );

        $stock->increment('quantity_in_stock', $validated['quantity']);
        $stock->increment('initial_allocation', $validated['quantity']);

        // Log stock movement (assuming StockMovement model)
        if (class_exists('App\Models\StockMovement')) {
            \App\Models\StockMovement::create([
                'product_id' => $validated['product_id'],
                'branch_id' => $validated['branch_id'],
                'type' => 'in',
                'quantity' => $validated['quantity'],
                'notes' => ($validated['reference'] ?? 'Manual Stock Addition') . ' - Added by ' . auth()->user()->name,
                'user_id' => auth()->id(),
            ]);
        }

        return redirect()->route('products.show', $validated['product_id'])
            ->with('success', "Added {$validated['quantity']} units to branch");
    }
}