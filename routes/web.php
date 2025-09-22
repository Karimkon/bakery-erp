<?php

use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

use App\Http\Controllers\Admin\DashboardController as AdminDashboardController;
use App\Http\Controllers\Chef\DashboardController as ChefDashboardController;
use App\Http\Controllers\Sales\DashboardController as SalesDashboardController;
use App\Http\Controllers\Finance\DashboardController as FinanceDashboardController;

// Home
Route::get('/', fn () => view('welcome'));

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

    if (Auth::attempt($credentials, $request->boolean('remember')) && Auth::user()->role === 'admin') {
        $request->session()->regenerate();
        return redirect()->intended(route('admin.dashboard'));
    }

    Auth::logout();
    return redirect()->route('admin.login')->with('error','Only admins can login here.');
})->name('admin.login.submit');

Route::post('/chef/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember')) && Auth::user()->role === 'chef') {
        $request->session()->regenerate();
        return redirect()->intended(route('chef.dashboard'));
    }

    Auth::logout();
    return redirect()->route('chef.login')->with('error','Only chefs can login here.');
})->name('chef.login.submit');

Route::post('/sales/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember')) && Auth::user()->role === 'sales') {
        $request->session()->regenerate();
        return redirect()->intended(route('sales.dashboard'));
    }

    Auth::logout();
    return redirect()->route('sales.login')->with('error','Only sales staff can login here.');
})->name('sales.login.submit');

Route::post('/finance/login', function (Request $request) {
    $credentials = $request->validate([
        'email' => ['required','email'],
        'password' => ['required'],
    ]);

    if (Auth::attempt($credentials, $request->boolean('remember')) && Auth::user()->role === 'finance') {
        $request->session()->regenerate();
        return redirect()->intended(route('finance.dashboard'));
    }

    Auth::logout();
    return redirect()->route('finance.login')->with('error','Only finance staff can login here.');
})->name('finance.login.submit');

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
    Route::resource('ingredients', \App\Http\Controllers\Admin\IngredientController::class);
    Route::resource('dispatches', \App\Http\Controllers\Admin\DispatchController::class)->only(['index','create','store','show','edit','update']);
    Route::get('dispatches/openings/{driver}/{date}', [\App\Http\Controllers\Admin\DispatchController::class, 'openings'])
    ->name('admin.dispatches.openings');
    
});

Route::middleware(['auth','role:chef'])->prefix('chef')->name('chef.')->group(function () {
    Route::get('/dashboard', [ChefDashboardController::class,'index'])->name('dashboard');
    Route::resource('productions', \App\Http\Controllers\Chef\ProductionController::class);

});

Route::middleware(['auth','role:sales'])->prefix('sales')->name('sales.')->group(function () {
    Route::get('/dashboard', [SalesDashboardController::class,'index'])->name('dashboard');
});

Route::middleware(['auth','role:finance'])->prefix('finance')->name('finance.')->group(function () {
    Route::get('/dashboard', [FinanceDashboardController::class,'index'])->name('dashboard');
});

// ----------------------
// Override default login
// ----------------------
Route::get('/login', function () {
    return redirect()->route('chef.login');
})->name('login');
