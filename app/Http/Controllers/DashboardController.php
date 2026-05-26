<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Models\Product;
use App\Models\Shift;
use App\Models\Branch;
use App\Models\ProductBranchStock;
use App\Models\StockMovement;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $user        = auth()->user();
        $isPowerUser = $user->isSuperAdmin() || $user->isOwner();

        // NOTE: Every query below is AUTOMATICALLY scoped to the user's
        // company by the CompanyScope global scope. You no longer write
        // ->where('company_id', ...) anywhere. The only manual filter
        // that remains is the cashier-sees-own-sales business rule,
        // applied via the $isPowerUser gate.

        // ── Today's Sales ────────────────────────────────────────────
        // Company-scoped automatically; cashier sees only their own.
        $salesQuery = Sale::whereDate('created_at', today());
        if (! $isPowerUser) {
            $salesQuery->where('cashier_id', $user->id);
        }
        $todaySales = $salesQuery->sum('total_amount');

        // ── Products / Stock (company-wide, shared within company) ───
        // Previously counted EVERY company's products — now correctly
        // limited to this company by the global scope.
        $totalProducts = Product::where('is_active', true)->count();

        $lowStockProducts = ProductBranchStock::query()
            ->join('products', 'product_branch_stocks.product_id', '=', 'products.id')
            ->whereColumn('product_branch_stocks.quantity_in_stock', '<=', 'products.reorder_level')
            ->where('products.is_active', true)
            ->distinct('product_branch_stocks.product_id')
            ->count('product_branch_stocks.product_id');

        // ── Active shift (personal) ──────────────────────────────────
        $activeShift = Shift::where('status', 'open')
            ->where('cashier_id', $user->id)
            ->first();

        // ── Recent Sales ─────────────────────────────────────────────
        $recentSalesQuery = Sale::latest()->take(10)->with(['cashier', 'customer', 'branch']);
        if (! $isPowerUser) {
            $recentSalesQuery->where('cashier_id', $user->id);
        }
        $recentSales = $recentSalesQuery->get();

        // ── Month-to-date stats ──────────────────────────────────────
        $startOfMonth = now()->startOfMonth();
        $endOfMonth   = now()->endOfMonth();

        $mtdRevenueQuery = Sale::whereBetween('created_at', [$startOfMonth, $endOfMonth])
            ->where('status', 'completed');
        if (! $isPowerUser) {
            $mtdRevenueQuery->where('cashier_id', $user->id);
        }
        $mtdRevenue = $mtdRevenueQuery->sum('total_amount');

        // ── COGS ─────────────────────────────────────────────────────
        // sale_items has no company_id, but it is joined to `sales`,
        // which IS company-scoped. To make the scope apply to the join,
        // we drive the query through the Sale model instead of the raw
        // DB facade (the DB facade bypasses Eloquent scopes entirely!).
        $mtdCogsQuery = Sale::query()
            ->join('sale_items', 'sale_items.sale_id', '=', 'sales.id')
            ->join('products', 'sale_items.product_id', '=', 'products.id')
            ->whereBetween('sales.created_at', [$startOfMonth, $endOfMonth])
            ->where('sales.status', 'completed');

        if (! $isPowerUser) {
            $mtdCogsQuery->where('sales.cashier_id', $user->id);
        }
        $mtdCogs = $mtdCogsQuery->sum(\DB::raw('sale_items.quantity * products.cost_price'));

        // ── Expenses (company-scoped automatically) ──────────────────
        $mtdExpenses = \App\Models\Expense::whereBetween('expense_date', [$startOfMonth, $endOfMonth])
            ->where('status', 'approved')
            ->sum('amount');

        $mtdProfit = ($mtdRevenue - $mtdCogs) - $mtdExpenses;

        // ── Active shifts across the company (power users only) ──────
        $allActiveShifts = null;
        if ($isPowerUser) {
            $allActiveShifts = Shift::with(['cashier', 'branch'])
                ->where('status', 'open')
                ->get();
        }

        $isSystemActive = \App\Models\Setting::isSystemActive();

        // ── Subscription (read from the user's own company) ──────────
        $company            = $user->company;
        $subscriptionStatus = $company?->subscription_status ?? 'expired';

        $subscriptionExpiresAt = $subscriptionStatus === 'trial'
            ? $company?->trial_ends_at
            : $company?->subscription_expires_at;

        $trialDaysRemaining = $company?->trialDaysRemaining();

        // ── AI Daily Brief ───────────────────────────────────────────
        $aiBrief = null;
        if ($company && $user->isOwner()) {
            $aiBrief = app(\App\Services\AiBriefService::class)->getOrGenerateToday($company);
        }

        return view('dashboard.index', [
            'todaySales'            => $todaySales,
            'totalProducts'         => $totalProducts,
            'lowStockProducts'      => $lowStockProducts,
            'activeShift'           => $activeShift,
            'allActiveShifts'       => $allActiveShifts,
            'recentSales'           => $recentSales,
            'mtdProfit'             => $mtdProfit,
            'mtdRevenue'            => $mtdRevenue,
            'isSystemActive'        => $isSystemActive,
            'subscriptionStatus'    => $subscriptionStatus,
            'subscriptionExpiresAt' => $subscriptionExpiresAt,
            'trialDaysRemaining'    => $trialDaysRemaining,
            'aiBrief'               => $aiBrief,
        ]);
    }

    public function superAdminInventory(): View
    {
        // IMPORTANT: This page is also now company-scoped. If a SuperAdmin
        // belongs to a company, they see only that company's inventory.
        // If you intend this to be a true cross-tenant view, that is a
        // deliberate exception — see the notes after this file.

        $totalActiveProducts = Product::where('is_active', true)->count();
        $totalStockUnits     = ProductBranchStock::sum('quantity_in_stock');

        $inventoryCostValue = ProductBranchStock::query()
            ->join('products', 'product_branch_stocks.product_id', '=', 'products.id')
            ->sum(\DB::raw('product_branch_stocks.quantity_in_stock * products.cost_price'));

        $inventorySellingValue = ProductBranchStock::query()
            ->join('products', 'product_branch_stocks.product_id', '=', 'products.id')
            ->sum(\DB::raw('product_branch_stocks.quantity_in_stock * products.selling_price'));

        $lowStockItems = ProductBranchStock::with(['product', 'branch'])
            ->join('products', 'product_branch_stocks.product_id', '=', 'products.id')
            ->whereColumn('product_branch_stocks.quantity_in_stock', '<=', 'products.reorder_level')
            ->where('products.is_active', true)
            ->select('product_branch_stocks.*')
            ->orderBy('product_branch_stocks.quantity_in_stock', 'asc')
            ->get();

        $branches = Branch::where('is_active', true)
            ->with(['productStocks.product'])
            ->get()
            ->map(function ($branch) use ($totalStockUnits) {
                $stocks     = $branch->productStocks;
                $units      = $stocks->sum('quantity_in_stock');
                $costVal    = $stocks->sum(fn ($s) => $s->quantity_in_stock * ($s->product->cost_price ?? 0));
                $sellingVal = $stocks->sum(fn ($s) => $s->quantity_in_stock * ($s->product->selling_price ?? 0));
                $pct        = $totalStockUnits > 0 ? round(($units / $totalStockUnits) * 100, 1) : 0;

                return [
                    'id'            => $branch->id,
                    'name'          => $branch->name,
                    'product_count' => $stocks->count(),
                    'total_units'   => $units,
                    'cost_value'    => $costVal,
                    'selling_value' => $sellingVal,
                    'pct_of_total'  => $pct,
                ];
            });

        $recentMovements = StockMovement::with(['product', 'user'])
            ->join('branches', 'stock_movements.branch_id', '=', 'branches.id')
            ->select('stock_movements.*', 'branches.name as branch_name')
            ->latest('stock_movements.created_at')
            ->limit(20)
            ->get();

        $branchSalesToday = Branch::where('is_active', true)
            ->with(['sales' => fn ($q) => $q->whereDate('created_at', today())->where('status', 'completed')])
            ->get()
            ->map(fn ($b) => [
                'name'         => $b->name,
                'sales_count'  => $b->sales->count(),
                'total_amount' => $b->sales->sum('total_amount'),
            ]);

        return view('dashboard.superadmin-inventory', compact(
            'totalActiveProducts',
            'totalStockUnits',
            'inventoryCostValue',
            'inventorySellingValue',
            'lowStockItems',
            'branches',
            'recentMovements',
            'branchSalesToday'
        ));
    }
}