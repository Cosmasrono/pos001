<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\Branch;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\Http\Request;
use Illuminate\Http\RedirectResponse;
use Illuminate\View\View;
use Illuminate\Support\Facades\DB;

class StockAdjustmentController extends Controller
{
    /**
     * Show the stock adjustment / write-off form.
     */
    public function index(Request $request): View
    {
        $user = auth()->user();
        $branchId = $user->branch_id ?? null;

        // Query recent write-off movements (theft, damage, expiry, etc.)
        $writeoffTypes = ['adjustment', 'damage'];
        $logsQuery = StockMovement::with(['product', 'user', 'branch'])
            ->whereIn('type', $writeoffTypes)
            ->latest();

        if ($branchId) {
            $logsQuery->where('branch_id', $branchId);
        }

        $logs = $logsQuery->paginate(20);

        // Stats
        $totalWrittenOff = StockMovement::whereIn('type', $writeoffTypes)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereDate('created_at', today())
            ->sum(DB::raw('ABS(quantity)'));

        $monthWrittenOff = StockMovement::whereIn('type', $writeoffTypes)
            ->when($branchId, fn($q) => $q->where('branch_id', $branchId))
            ->whereMonth('created_at', now()->month)
            ->sum(DB::raw('ABS(quantity)'));

        $branches = Branch::where('is_active', true)->get();
        $products = Product::where('is_active', true)->orderBy('name')->get();

        return view('stock.adjustments', compact(
            'logs', 'products', 'branches', 'branchId',
            'totalWrittenOff', 'monthWrittenOff'
        ));
    }

    /**
     * Record a stock write-off (theft, damage, expiry, etc.)
     */
    public function store(Request $request): RedirectResponse
    {
        $user = auth()->user();

        $validated = $request->validate([
            'product_id' => 'required|exists:products,id',
            'branch_id'  => 'required|exists:branches,id',
            'quantity'   => 'required|integer|min:1',
            'reason'     => 'required|in:theft,damage,expiry,miscounted,lost,other',
            'notes'      => 'nullable|string|max:500',
        ]);

        // Map reason to movement type
        $typeMap = [
            'theft'      => 'adjustment',
            'damage'     => 'damage',
            'expiry'     => 'damage',
            'miscounted' => 'adjustment',
            'lost'       => 'adjustment',
            'other'      => 'adjustment',
        ];
        $movementType = $typeMap[$validated['reason']];

        DB::transaction(function () use ($validated, $movementType, $user) {
            // Deduct from branch stock
            $stock = ProductBranchStock::firstOrCreate(
                ['product_id' => $validated['product_id'], 'branch_id' => $validated['branch_id']],
                ['quantity_in_stock' => 0]
            );

            if ($stock->quantity_in_stock < $validated['quantity']) {
                throw new \Exception("Cannot write off more than available stock ({$stock->quantity_in_stock} units).");
            }

            $stock->decrement('quantity_in_stock', $validated['quantity']);

            // Log the movement (negative quantity = stock removed)
            StockMovement::create([
                'product_id' => $validated['product_id'],
                'branch_id'  => $validated['branch_id'],
                'type'       => $movementType,
                'quantity'   => -abs($validated['quantity']),
                'notes'      => "[" . strtoupper($validated['reason']) . "] " . ($validated['notes'] ?? ''),
                'user_id'    => $user->id,
            ]);
        });

        return back()->with('success', 'Stock write-off recorded successfully.');
    }
}
