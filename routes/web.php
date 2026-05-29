<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\Auth;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\CategoryController;
use App\Http\Controllers\SupplierController;
use App\Http\Controllers\PurchaseOrderController;
use App\Http\Controllers\SalesController;
use App\Http\Controllers\ShiftController;
use App\Http\Controllers\ExpenseController;
use App\Http\Controllers\ExpenseCategoryController;
use App\Http\Controllers\PromotionController;
use App\Http\Controllers\AuditLogController;
use App\Http\Controllers\SystemControlController;
use App\Http\Controllers\UserController;
use App\Http\Controllers\BranchController;
use App\Http\Controllers\StockTransferController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\AIInventoryController;

Route::get('/', function () {
    return view('welcome');
});

// Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('login', [AuthenticatedSessionController::class, 'store'])->middleware('throttle:5,1');
    Route::get('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'create'])->name('register');
    Route::post('register', [App\Http\Controllers\Auth\RegisteredUserController::class, 'store'])->middleware('throttle:3,1');

    Route::get('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'create'])
        ->name('password.request');

    Route::post('forgot-password', [App\Http\Controllers\Auth\PasswordResetLinkController::class, 'store'])
        ->middleware('throttle:3,1')
        ->name('password.email');

    Route::get('reset-password/{token}', [App\Http\Controllers\Auth\NewPasswordController::class, 'edit'])
        ->name('password.reset');

    Route::post('reset-password', [App\Http\Controllers\Auth\NewPasswordController::class, 'store'])
        ->name('password.store');
});

Route::middleware(['auth'])->group(function () {
    Route::get('/dashboard', [App\Http\Controllers\DashboardController::class, 'index'])->name('dashboard');
 Route::get('/superadmin/inventory', [App\Http\Controllers\DashboardController::class, 'superAdminInventory'])
    ->middleware('role:owner,super_admin,manager')
    ->name('superadmin.inventory');
    // Branch Management (Owner / Super Admin / Manager only)
    Route::resource('branches', App\Http\Controllers\BranchController::class)
        ->middleware('role:owner,super_admin,manager');
    Route::post('branches/{branch}/toggle-status', [App\Http\Controllers\BranchController::class, 'toggleStatus'])
        ->middleware('role:owner,super_admin,manager')
        ->name('branches.toggle-status');

    // Product and Inventory Management (Restricted for Cashiers)
    Route::resource('products', ProductController::class);
    Route::resource('stock-transfers', StockTransferController::class);
    Route::post('products/add-stock', [ProductController::class, 'addStock'])->name('stock.add');
    Route::post('products/{product}/batch-transfer', [ProductController::class, 'batchTransfer'])->name('products.batch-transfer');
    Route::post('categories/quick-store', [CategoryController::class, 'quickStore'])->name('categories.quick-store');
    Route::resource('categories', CategoryController::class);
    Route::resource('suppliers', SupplierController::class);
    Route::resource('purchase-orders', PurchaseOrderController::class);
    Route::get('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receiveForm'])->name('purchase-orders.receive-form');
    Route::post('purchase-orders/{purchase_order}/receive', [PurchaseOrderController::class, 'receive'])->name('purchase-orders.receive');
    Route::get('stock-transfers/stock-info', [StockTransferController::class, 'getStockInfo'])->name('stock-transfers.stock-info');
 

    // Stock Write-offs / Adjustments (Theft, Damage, Expiry, etc.)
    Route::get('stock/adjustments', [App\Http\Controllers\StockAdjustmentController::class, 'index'])->name('stock.adjustments.index');
    Route::post('stock/adjustments', [App\Http\Controllers\StockAdjustmentController::class, 'store'])->name('stock.adjustments.store');

    Route::get('pos', [SalesController::class, 'create'])->name('sales.pos');
    Route::get('pos/products', [SalesController::class, 'getProducts'])->name('pos.products');
    Route::get('pos/search', [SalesController::class, 'searchProduct'])->name('pos.search');
    Route::get('sales/create', [SalesController::class, 'create'])->name('sales.create');
    Route::post('sales', [SalesController::class, 'store'])->name('sales.store');
    Route::get('sales', [SalesController::class, 'index'])->name('sales.index');
    Route::get('sales/{sale}', [SalesController::class, 'show'])->name('sales.show');
    Route::get('sales/{sale}/receipt', [SalesController::class, 'receipt'])->name('sales.receipt');

    Route::get('/ai/branch-sales', [AIInventoryController::class, 'branchSalesAnalysis'])->name('ai.branch-sales');
    Route::get('/ai/pricing-health', [AIInventoryController::class, 'kenyanMarketPricing'])->name('ai.pricing-health');

    // Invoice Management
    Route::resource('invoices', \App\Http\Controllers\InvoiceController::class);
    Route::get('invoices/{invoice}/print', [\App\Http\Controllers\InvoiceController::class, 'print'])->name('invoices.print');
    Route::post('invoices/{invoice}/send', [\App\Http\Controllers\InvoiceController::class, 'markAsSent'])->name('invoices.send');
    Route::post('invoices/{invoice}/payment', [\App\Http\Controllers\InvoiceController::class, 'recordPayment'])->name('invoices.payment');
    Route::post('invoices/{invoice}/cancel', [\App\Http\Controllers\InvoiceController::class, 'cancel'])->name('invoices.cancel');
    Route::get('sales/{sale}/create-invoice', [\App\Http\Controllers\InvoiceController::class, 'createFromSale'])->name('invoices.from-sale');


    Route::post('shifts', [ShiftController::class, 'store'])->name('shifts.store');
    Route::post('shifts/close', [ShiftController::class, 'close'])->name('shifts.close');
    Route::get('shifts/active', [ShiftController::class, 'getActive'])->name('shifts.active');
    Route::get('shifts/{shift}', [ShiftController::class, 'show'])->name('shifts.show');

    Route::resource('expense-categories', ExpenseCategoryController::class);
    Route::resource('expenses', ExpenseController::class);
    Route::patch('expenses/{expense}/approve', [ExpenseController::class, 'approve'])->name('expenses.approve');
    Route::patch('expenses/{expense}/reject', [ExpenseController::class, 'reject'])->name('expenses.reject');

    // Promotions
    Route::resource('promotions', PromotionController::class);

    // Loans
    Route::resource('loans', \App\Http\Controllers\LoanController::class);
    Route::post('loans/{loan}/payments', [\App\Http\Controllers\LoanController::class, 'recordPayment'])->name('loans.record-payment');

    // AI Inventory Insights & Decision Center
    Route::get('ai/dashboard', [\App\Http\Controllers\AIInventoryController::class, 'dashboard'])->name('ai.dashboard');
    Route::get('ai/product/{product}', [\App\Http\Controllers\AIInventoryController::class, 'productAnalysis'])->name('ai.product');
    Route::get('ai/pricing', [\App\Http\Controllers\AIInventoryController::class, 'pricingDashboard'])->name('ai.pricing');
    Route::get('ai/bundles', [\App\Http\Controllers\AIInventoryController::class, 'bundleSuggestions'])->name('ai.bundles');
    Route::get('ai/waste-management', [\App\Http\Controllers\AIInventoryController::class, 'wasteManagement'])->name('ai.waste');

    // AI API Endpoints
    Route::post('api/ai/execute-recommendation', [\App\Http\Controllers\AIInventoryController::class, 'executeRecommendation'])->name('ai.execute');

    Route::get('reports/sales', [\App\Http\Controllers\ReportController::class, 'sales'])->name('reports.sales');
    Route::get('reports/pnl', [\App\Http\Controllers\ReportController::class, 'profitLoss'])->name('reports.pnl');

    // Audit Logs
    Route::get('audit-logs', [AuditLogController::class, 'index'])->name('audit-logs.index');
    Route::get('audit-logs/{auditLog}', [AuditLogController::class, 'show'])->name('audit-logs.show');

    // System Control (Owner Only)
    Route::get('system/control', [SystemControlController::class, 'index'])->name('system.control');
    Route::post('system/toggle', [SystemControlController::class, 'toggle'])->name('system.toggle');
    Route::post('system/subscription', [SystemControlController::class, 'updateSubscription'])->name('system.subscription.update');
    Route::post('system/company', [SystemControlController::class, 'manageCompany'])->name('system.company.manage');

    // Users (Owner / Super Admin / Manager only)
    Route::resource('users', UserController::class)
        ->middleware('role:owner,super_admin,manager');

    // Profile (any authenticated user)
    Route::get('profile', [App\Http\Controllers\ProfileController::class, 'show'])->name('profile.show');
    Route::put('profile', [App\Http\Controllers\ProfileController::class, 'update'])->name('profile.update');
    Route::put('profile/password', [App\Http\Controllers\ProfileController::class, 'updatePassword'])->name('profile.password');

    // Logout
    Route::post('logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
    Route::post('/password/change', [App\Http\Controllers\UserController::class, 'changePassword'])->name('password.change');

    // Platform Owner Routes (admin emails only)
    Route::middleware('platform')->prefix('platform')->name('platform.')->group(function () {
        Route::get('/', [App\Http\Controllers\PlatformController::class, 'index'])->name('index');
        Route::post('companies/{company}/activate', [App\Http\Controllers\PlatformController::class, 'activateCompany'])->name('companies.activate');
        Route::post('companies/{company}/suspend', [App\Http\Controllers\PlatformController::class, 'suspendCompany'])->name('companies.suspend');
        Route::post('bots/purge', [App\Http\Controllers\PlatformController::class, 'purgeBots'])->name('bots.purge');
    });
});


// Email Verification Routes
Route::get('/email/verify/{id}/{hash}', function (\Illuminate\Http\Request $request, $id, $hash) {
    $user = \App\Models\User::findOrFail($id);

    if (!hash_equals((string) $hash, sha1($user->getEmailForVerification()))) {
        abort(403, 'Invalid verification link.');
    }

    if (!$user->hasVerifiedEmail()) {
        $user->markEmailAsVerified();

        // Start the 7-day trial only now that the email is confirmed real
        $company = \App\Models\Company::find($user->company_id);
        if ($company && $company->subscription_status === 'pending') {
            $company->update([
                'subscription_status' => 'trial',
                'trial_ends_at'       => now()->addDays(7),
            ]);
        }
    }

    $message = 'Email verified successfully! Your 7-day free trial has started. You can now log in to WingPOS.';

    return redirect()->route('login')->with('success', $message);
})->middleware('signed')->name('verification.verify');

Route::middleware('auth')->group(function () {
    Route::get('/email/verify', function () {
        return view('auth.verify-email');
    })->name('verification.notice');

    Route::post('/email/verification-notification', function (\Illuminate\Http\Request $request) {
        $request->user()->sendEmailVerificationNotification();
        return back()->with('success', 'Verification link sent!');
    })->middleware('throttle:6,1')->name('verification.send');
});

// Allow unauthenticated users to see verification notice after registration
Route::get('/verify-email-notice', function () {
    return view('auth.verify-email-notice');
})->name('verification.notice.guest');

// Subscription (auth required — also allowed when subscription is expired)
Route::middleware('auth')->prefix('subscribe')->name('subscribe.')->group(function () {
    Route::get('/',           [App\Http\Controllers\SubscriptionController::class, 'plans'])->name('plans');
    Route::post('/initiate',  [App\Http\Controllers\SubscriptionController::class, 'initiate'])->name('initiate');
    Route::get('/pending/{payment}', [App\Http\Controllers\SubscriptionController::class, 'pending'])->name('pending');
    Route::get('/status/{payment}',  [App\Http\Controllers\SubscriptionController::class, 'checkStatus'])->name('status');
});

// M-Pesa callback — no auth, no CSRF (Safaricom calls this directly)
Route::post('/mpesa/callback', [App\Http\Controllers\SubscriptionController::class, 'callback'])
    ->name('mpesa.callback');

// System Unavailable Page (Public if deactivated)
Route::get('system/unavailable', function () {
    return view('errors.system_unavailable');
})->name('system.unavailable');

// Subscription Expired Page
Route::get('system/subscription-expired', function () {
    return view('errors.subscription_expired');
})->name('subscription.expired');
