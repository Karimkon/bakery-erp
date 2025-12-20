<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Chef\DashboardController as ChefDashboardController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Finance\DashboardController as FinanceDashboardController;

use App\Http\Controllers\Sales\SaleController;
use App\Http\Controllers\Sales\BankingController;
use App\Http\Controllers\Sales\ShopStockController;
use App\Http\Controllers\Admin\ReportsController;
use App\Http\Controllers\Manager\ManagerDashboardController;
use App\Http\Controllers\Manager\ManagerReportsController;
use App\Http\Controllers\Admin\AdminDamageController;
use App\Http\Controllers\Admin\AdminExpenseController;
use App\Http\Controllers\Admin\ProductionApprovalController;



// Home
Route::get('/', fn () => view('welcome'));
Route::get('/manual', fn () => view('manual'));

// ----------------------
// Login views per role
// ----------------------
Route::get('/admin/login', fn () => view('admin.auth.login'))->name('admin.login');
Route::get('/chef/login', fn () => view('chef.auth.login'))->name('chef.login');
Route::get('/sales/login', fn () => view('sales.auth.login'))->name('sales.login');
Route::get('/finance/login', fn () => view('finance.auth.login'))->name('finance.login');

// ----------------------
// Login submit per role
// ----------------------
Route::post('/admin/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'admin',       // force admin here
    ], $request->boolean('remember'))) {

    $request->session()->regenerate();
    return redirect()->intended(route('admin.dashboard'));
}

return back()->with('error', 'Only admins can login here.');


    Auth::logout();
    return redirect()->route('admin.login')->with('error','Only admins can login here.');
})->name('admin.login.submit');

Route::post('/chef/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'chef',       // force chef here
    ], $request->boolean('remember'))) {

    $request->session()->regenerate();
    return redirect()->intended(route('chef.dashboard'));
}

return back()->with('error', 'Only admins can login here.');


    Auth::logout();
    return redirect()->route('chef.login')->with('error','Only chefs can login here.');
})->name('chef.login.submit');

Route::post('/sales/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'sales',       // force sales here
    ], $request->boolean('remember'))) {

    $request->session()->regenerate();
    return redirect()->intended(route('sales.dashboard'));
}

return back()->with('error', 'Only admins can login here.');


    Auth::logout();
    return redirect()->route('sales.login')->with('error','Only sales staff can login here.');
})->name('sales.login.submit');

Route::post('/finance/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'finance',       // force finance here
    ], $request->boolean('remember'))) {

    $request->session()->regenerate();
    return redirect()->intended(route('finance.dashboard'));
}

return back()->with('error', 'Only admins can login here.');


    Auth::logout();
    return redirect()->route('finance.login')->with('error','Only finance staff can login here.');
})->name('finance.login.submit');

// ----------------------
// Manager Login
// ----------------------
Route::get('/manager/login', fn () => view('manager.auth.login'))->name('manager.login');

Route::post('/manager/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'manager',   // force manager here
    ], $request->boolean('remember'))) {

        $request->session()->regenerate();
        return redirect()->intended(route('manager.dashboard'));
    }

    return back()->with('error', 'Only managers can login here.');
})->name('manager.login.submit');



// ----------------------
// Kampala Shop Login
// ----------------------
Route::get('/kampala/login', fn () => view('kampala.auth.login'))->name('kampala.login');

Route::post('/kampala/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt([
        'email' => $request->email,
        'password' => $request->password,
        'role' => 'kampala_shop',   // force kampala_shop here
    ], $request->boolean('remember'))) {

        $request->session()->regenerate();
        return redirect()->intended(route('kampala.dashboard'));
    }

    return back()->with('error', 'Only Kampala shop staff can login here.');
})->name('kampala.login.submit');


// ----------------------
// Shared logout
// ----------------------
Route::post('/logout', function (Request $request) {
    Auth::logout();
    $request->session()->invalidate();
    $request->session()->regenerateToken();
    return redirect('/');
})->name('logout');

// ----------------------
// Dashboards per role
// ----------------------
Route::middleware(['auth','role:admin'])->prefix('admin')->name('admin.')->group(function () {
    Route::get('/dashboard', [AdminDashboardController::class,'index'])->name('dashboard');
    Route::resource('users', \App\Http\Controllers\Admin\AdminUserController::class);
    Route::resource('productions', \App\Http\Controllers\Admin\ProductionController::class);

       // Bakery Sales
    Route::get('/sales/bakery', [\App\Http\Controllers\Admin\SalesReportController::class, 'bakerySales'])->name('sales.bakery');
    
    // Kampala Sales
    Route::get('/sales/kampala', [\App\Http\Controllers\Admin\SalesReportController::class, 'kampalaSales'])->name('sales.kampala');
    Route::get('/sales/kampala/{shopId}', [\App\Http\Controllers\Admin\SalesReportController::class, 'kampalaShopDetails'])->name('sales.kampala-details');
    Route::get('/kampala-sales/export', [\App\Http\Controllers\Admin\SalesReportController::class, 'exportKampalaSales'])->name('kampala-sales.export');

    // 🟢 FINANCIAL REPORTS - CORRECT STRUCTURE
    Route::prefix('reports/financial')->name('reports.financial.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Admin\FinancialReportsController::class, 'index'])->name('index');
        Route::get('/export', [\App\Http\Controllers\Admin\FinancialReportsController::class, 'exportExcel'])->name('export');
    });

    Route::get('dispatches/financial-report', [\App\Http\Controllers\Admin\DispatchController::class, 'financialReport'])
    ->name('dispatches.financial-report');
Route::get('dispatches/financial-details/{driverId}', [\App\Http\Controllers\Admin\DispatchController::class, 'financialDetails'])
    ->name('dispatches.financial-details');
Route::get('dispatches/history/{driver}', [\App\Http\Controllers\Admin\DispatchController::class, 'history'])
    ->name('dispatches.history');

    Route::get('dispatches/back-debt-history/{driverId}', 
  [\App\Http\Controllers\Admin\DispatchController::class, 'backDebtHistory']
)->name('dispatches.back-debt-history');
   
    Route::resource('dispatches', \App\Http\Controllers\Admin\DispatchController::class)->only(['index','create','store','show','edit','update']);
    Route::get('dispatches/openings/{driver}/{date}', [\App\Http\Controllers\Admin\DispatchController::class, 'openings'])
    ->name('dispatches.openings');

    // 🟦 NEW: Shop Dispatch (Bakery Main Shop)
    Route::resource('shop-dispatch', \App\Http\Controllers\Admin\ShopDispatchController::class)
        ->except(['show']); 

    // 🟦 NEW: Reporting route for shop sales/stock
    Route::get('shop-report', [\App\Http\Controllers\Admin\ShopReportController::class,'index'])->name('shop.report');

    Route::get('bankings', [\App\Http\Controllers\Admin\BankingController::class, 'index'])
        ->name('bankings.index');

    Route::post('/dispatches/{dispatch}/mark-received', 
    [\App\Http\Controllers\Admin\DispatchController::class, 'markReceived'])->name('dispatches.markReceived');

    Route::get('reports', [ReportsController::class,'index'])->name('reports.index');
    Route::get('reports/export-pdf', [ReportsController::class,'exportPdf'])->name('reports.exportPdf');
    Route::get('reports/export-excel', [ReportsController::class,'exportExcel'])->name('reports.exportExcel');


    Route::get('damages', [AdminDamageController::class, 'index'])->name('damages.index');
    Route::get('damages/{damage}', [AdminDamageController::class, 'show'])->name('damages.show');
    Route::post('damages/{damage}/approve', [AdminDamageController::class, 'approve'])->name('damages.approve');
    Route::post('damages/{damage}/reject', [AdminDamageController::class, 'reject'])->name('damages.reject');
    Route::post('damages/{damage}/sold', [AdminDamageController::class, 'markAsSold'])->name('damages.sold');
    Route::delete('damages/{damage}', [\App\Http\Controllers\Admin\AdminDamageController::class, 'destroy'])
    ->name('damages.destroy');


     // 💰 NEW: Admin Expense Analytics Routes
    Route::get('expenses/dashboard', [AdminExpenseController::class, 'dashboard'])
        ->name('expenses.dashboard');
    Route::get('expenses/daily-report', [AdminExpenseController::class, 'dailyReport'])
        ->name('expenses.daily');
    Route::get('expenses/driver-analysis', [AdminExpenseController::class, 'driverAnalysis'])
        ->name('expenses.driver-analysis');
    Route::get('expenses/export', [AdminExpenseController::class, 'export'])
        ->name('expenses.export');


    Route::get('ingredients-overview', [App\Http\Controllers\Admin\IngredientController::class, 'overview'])
        ->name('ingredients.overview');

    Route::get('ingredients/stock_history', [App\Http\Controllers\Admin\StockHistoryController::class, 'index'])
        ->name('ingredients.stock_history');

    Route::get('ingredients/stock_history/{id}', [App\Http\Controllers\Admin\StockHistoryController::class, 'show'])
        ->name('ingredients.stock_history.show');


     Route::resource('ingredients', \App\Http\Controllers\Admin\IngredientController::class);

     Route::get('/deposits', [\App\Http\Controllers\Admin\AdminDepositController::class, 'index'])->name('deposits.index');

     Route::get('reports/productprofit', [App\Http\Controllers\Admin\ProductProfitReportController::class, 'index'])
        ->name('reports.productprofit');

    Route::get('staff-breakfast', [\App\Http\Controllers\Admin\AdminStaffBreakfastController::class, 'index'])
        ->name('staff_breakfast.index');
    Route::post('staff-breakfast/{breakfast}/approve', [\App\Http\Controllers\Admin\AdminStaffBreakfastController::class, 'approve'])
        ->name('staff_breakfast.approve');
    Route::post('staff-breakfast/{breakfast}/reject', [\App\Http\Controllers\Admin\AdminStaffBreakfastController::class, 'reject'])
        ->name('staff_breakfast.reject');

    Route::resource('chef_targets', \App\Http\Controllers\Admin\ChefTargetController::class);

    Route::get('/managers/{manager}/progress-history', [\App\Http\Controllers\Admin\ChefTargetController::class, 'managerProgressHistory'])
        ->name('manager.progress-history');

    // Daily Cash Reports
Route::get('/reports/daily-cash', [\App\Http\Controllers\Admin\DailyCashReportController::class, 'index'])->name('reports.daily-cash');
Route::get('/reports/daily-cash/range', [\App\Http\Controllers\Admin\DailyCashReportController::class, 'dateRange'])->name('reports.daily-cash.range');
Route::get('/reports/daily-cash/api/summary', [\App\Http\Controllers\Admin\DailyCashReportController::class, 'getDailySummary'])->name('reports.daily-cash.api.summary');
Route::get('/api/cash-balance', [\App\Http\Controllers\Admin\DailyCashReportController::class, 'getCashBalance'])->name('api.cash-balance');
Route::get('/reports/financial', [\App\Http\Controllers\Admin\FinancialReportsController::class, 'index'])->name('reports.financial');
// Production Approvals - Alternative structure
    Route::get('production-approvals', [ProductionApprovalController::class, 'index'])->name('productions.approval-index');
    Route::get('production-approvals/{production}', [ProductionApprovalController::class, 'show'])->name('productions.approval-show');
    Route::post('production-approvals/{production}/approve', [ProductionApprovalController::class, 'approve'])->name('productions.approve');
    Route::post('production-approvals/{production}/reject', [ProductionApprovalController::class, 'reject'])->name('productions.reject');

    // Add to admin routes group
Route::prefix('reports')->name('reports.')->group(function () {
    Route::get('daily-production', [\App\Http\Controllers\Admin\DailyProductionReportController::class, 'index'])->name('daily-production');
    Route::get('daily-production/export-pdf', [\App\Http\Controllers\Admin\DailyProductionReportController::class, 'exportPdf'])->name('daily-production.export-pdf');
    Route::get('/daily-production/export-html', [\App\Http\Controllers\Admin\DailyProductionReportController::class, 'exportHtml'])
    ->name('daily-production.export-html');
    Route::post('daily-production/send-whatsapp', [\App\Http\Controllers\Admin\DailyProductionReportController::class, 'sendWhatsAppReport'])->name('daily-production.send-whatsapp');
    Route::post('daily-production/send-email', [\App\Http\Controllers\Admin\DailyProductionReportController::class, 'sendEmailReport'])->name('daily-production.send-email');
    Route::post('daily-production/auto-send', [\App\Http\Controllers\Admin\DailyProductionReportController::class, 'autoSendDailyReport'])->name('daily-production.auto-send');
});

 // Kampala Admin Dashboard
    Route::prefix('kampala')->name('kampala.')->group(function () {
        Route::get('/dashboard', [\App\Http\Controllers\Admin\KampalaAdminController::class, 'dashboard'])
            ->name('dashboard');
        Route::get('/shop/{shop}/activities', [\App\Http\Controllers\Admin\KampalaAdminController::class, 'shopActivities'])
            ->name('shop-activities');
        Route::get('/shop/{shop}/activities/export', [\App\Http\Controllers\Admin\KampalaAdminController::class, 'exportActivities'])
            ->name('shop-activities.export');
    });
    


});

Route::middleware(['auth','role:chef'])->prefix('chef')->name('chef.')->group(function () {
    Route::get('/dashboard', [ChefDashboardController::class,'index'])->name('dashboard');
    Route::resource('productions', \App\Http\Controllers\Chef\ProductionController::class);
    Route::get('/progress-history', [ChefDashboardController::class, 'progressHistory'])->name('progress-history');
    Route::get('/ingredients', [ChefDashboardController::class, 'ingredients'])->name('ingredients.index');
});

Route::middleware(['auth','role:sales'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class,'index'])->name('dashboard');
    Route::get('/cash-balance', [SalesDashboardController::class, 'getCashBalance'])->name('cash-balance');
    Route::resource('sales', SaleController::class)->only(['index','create','store','edit','update','destroy','show']);
    Route::resource('bankings', BankingController::class)->only(['index','create','store','edit','update','destroy','show']);
    Route::get('stock', [ShopStockController::class,'index'])->name('stock.index');
     Route::get('receipt/{id}', [SaleController::class, 'receipt'])->name('sales.receipt');
    Route::get('summary/daily', [SaleController::class, 'dailySummary'])->name('sales.summary.daily');
});

Route::middleware(['auth','role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [FinanceDashboardController::class,'index'])->name('dashboard');
    Route::get('payrolls/{payroll}/payslip', [App\Http\Controllers\Finance\PayrollController::class, 'payslip'])
     ->name('payrolls.payslip');

    Route::resource('expenses', App\Http\Controllers\Finance\ExpenseController::class);
    Route::resource('deposits', App\Http\Controllers\Finance\BankDepositController::class);
    Route::resource('payrolls', App\Http\Controllers\Finance\PayrollController::class);
    Route::get('/overview', [\App\Http\Controllers\Finance\OverviewController::class, 'index'])->name('overview');

});

Route::middleware(['auth','role:manager'])->prefix('manager')->name('manager.')->group(function () {
    Route::get('/dashboard', [ManagerDashboardController::class,'index'])->name('dashboard');
    Route::resource('kampala-dispatches', \App\Http\Controllers\Manager\KampalaDispatchController::class);
    // ✅ give manager access to dispatches
    // ✅ Custom route FIRST
    Route::get('dispatches/openings/{driver}/{date}', [App\Http\Controllers\Manager\ManagerDispatchController::class, 'openings'])
        ->name('dispatches.openings');
    Route::get('dispatches/financial-report', [\App\Http\Controllers\Manager\ManagerDispatchController::class, 'financialReport'])
        ->name('dispatches.financial-report');
    Route::get('dispatches/financial-details/{driverId}', [\App\Http\Controllers\Manager\ManagerDispatchController::class, 'financialDetails'])
    ->name('dispatches.financial-details');
     Route::get('dispatches/back-debt-history/{driverId}', [App\Http\Controllers\Manager\ManagerDispatchController::class, 'backDebtHistory'])
        ->name('dispatches.back-debt-history');

    // ✅ Resource route AFTER
    Route::resource('dispatches', App\Http\Controllers\Manager\ManagerDispatchController::class);

    Route::get('dispatch/export-pdf', [ManagerReportsController::class, 'exportPdf'])
    ->name('dispatch.exportPdf')
    ->defaults('reportType', 'dispatch');

Route::get('dispatch/export-excel', [ManagerReportsController::class, 'exportExcel'])
    ->name('dispatch.exportExcel')
    ->defaults('reportType', 'dispatch');


    // Production Reports
    Route::get('production', [ManagerReportsController::class, 'index'])->name('production.index');
    Route::get('production/export-pdf', [ManagerReportsController::class, 'exportPdf'])
    ->name('production.exportPdf')
    ->defaults('reportType', 'production');

Route::get('production/export-excel', [ManagerReportsController::class, 'exportExcel'])
    ->name('production.exportExcel')
    ->defaults('reportType', 'production');

    // Ingredients Management
    Route::resource('ingredients', App\Http\Controllers\Manager\ManagerIngredientController::class);

    Route::get('damages', [\App\Http\Controllers\Manager\ManagerDamageController::class, 'index'])
        ->name('damages.index');

    Route::get('damages/create', [\App\Http\Controllers\Manager\ManagerDamageController::class, 'create'])
        ->name('damages.create');

    Route::post('damages', [\App\Http\Controllers\Manager\ManagerDamageController::class, 'store'])
        ->name('damages.store');

    Route::get('damages/{damage}', [\App\Http\Controllers\Manager\ManagerDamageController::class, 'show'])
    ->name('damages.show');

    // web.php
Route::post('damages/{damage}/sold', [\App\Http\Controllers\Manager\ManagerDamageController::class, 'markAsSold'])->name('damages.sold');

    // 🟩 Manager Shop Dispatch (for managing shop deliveries)
    Route::resource('shop-dispatch', \App\Http\Controllers\Manager\ManagerShopDispatchController::class)
        ->except(['show']);

    // 🟩 Manager Shop Reports
    Route::get('shop-report', [\App\Http\Controllers\Manager\ManagerShopReportController::class, 'index'])
        ->name('shop.report');

    Route::get('/dispatches/history/{driver}', [App\Http\Controllers\Manager\ManagerDispatchController::class, 'history'])
    ->name('dispatches.history');

    Route::get('ingredients/chef/{chefId}', [App\Http\Controllers\Manager\ManagerIngredientController::class, 'byChef'])
        ->name('ingredients.byChef');
    Route::get('ingredients-overview', [App\Http\Controllers\Manager\ManagerIngredientController::class, 'overview'])
    ->name('ingredients.overview');

    Route::get('staff-breakfast', [\App\Http\Controllers\Manager\ManagerStaffBreakfastController::class, 'index'])
        ->name('staff_breakfast.index');
    Route::get('staff-breakfast/create', [\App\Http\Controllers\Manager\ManagerStaffBreakfastController::class, 'create'])
        ->name('staff_breakfast.create');
    Route::post('staff-breakfast', [\App\Http\Controllers\Manager\ManagerStaffBreakfastController::class, 'store'])
        ->name('staff_breakfast.store');

    Route::get('/progress-history', [ManagerDashboardController::class, 'progressHistory'])
        ->name('progress-history');

        Route::post('/ingredients/{ingredient}/quick-add-stock', [\App\Http\Controllers\Manager\ManagerIngredientController::class, 'quickAddStock'])
    ->name('ingredients.quick-add-stock');

    // 🟩 Manager Production Approval Routes
    Route::prefix('productions/approval')->name('productions.approval.')->group(function () {
        Route::get('/', [\App\Http\Controllers\Manager\ProductionApprovalController::class, 'index'])->name('index');
        Route::get('/{production}', [\App\Http\Controllers\Manager\ProductionApprovalController::class, 'show'])->name('show');
        Route::post('/{production}/approve', [\App\Http\Controllers\Manager\ProductionApprovalController::class, 'approve'])->name('approve');
        Route::post('/{production}/reject', [\App\Http\Controllers\Manager\ProductionApprovalController::class, 'reject'])->name('reject');

         // ✅ NEW: Edit routes
    Route::get('/approval/{production}/edit', [ProductionApprovalController::class, 'edit'])
        ->name('/approval.edit');
    Route::put('/approval/{production}/update', [ProductionApprovalController::class, 'update'])
        ->name('/approval.update');

    });
    
    });


    // Kampala shop routes (for Aria & Nakato)
Route::middleware(['auth','role:kampala_shop'])->prefix('kampala')->name('kampala.')->group(function () {
    Route::get('/dashboard', [\App\Http\Controllers\Kampala\KampalaDashboardController::class, 'index'])->name('dashboard');
    Route::resource('dispatches', \App\Http\Controllers\Kampala\KampalaDispatchController::class)->only(['index', 'show', 'destroy']);
    Route::post('dispatches/{kampalaDispatch}/receive', [\App\Http\Controllers\Kampala\KampalaDispatchController::class, 'receive'])->name('dispatches.receive');
    Route::resource('sales', \App\Http\Controllers\Kampala\KampalaSaleController::class);
    Route::resource('bankings', \App\Http\Controllers\Kampala\KampalaBankingController::class);
    Route::get('stock', [\App\Http\Controllers\Kampala\KampalaStockController::class, 'index'])->name('stock.index');
     Route::resource('expenses', \App\Http\Controllers\Kampala\KampalaExpenseController::class)
        ->only(['index', 'create', 'store', 'show']);
});

// ----------------------
// Override default login
// ----------------------
Route::get('/login', function () {
    return redirect()->route('chef.login');
})->name('login');
