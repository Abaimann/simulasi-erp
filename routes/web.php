<?php

declare(strict_types=1);

use App\Http\Controllers\ActiveCompanyController;
use App\Http\Controllers\Admin\AuditLogController;
use App\Http\Controllers\Admin\CompanyController;
use App\Http\Controllers\Admin\MembershipController;
use App\Http\Controllers\Admin\UserController;
use App\Http\Controllers\Auth\AuthenticatedSessionController;
use App\Http\Controllers\DashboardController;
use App\Http\Controllers\Group\SummaryController;
use App\Http\Controllers\Inventory\LotController;
use App\Http\Controllers\Inventory\StockBalanceController;
use App\Http\Controllers\Inventory\StockMovementController;
use App\Http\Controllers\Master\CustomerPricingController;
use App\Http\Controllers\Master\ProductController;
use App\Http\Controllers\Master\WarehouseController;
use App\Http\Controllers\Master\WarehouseRackController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

Route::get('/', function () {
    return Auth::check()
        ? redirect()->route('dashboard')
        : redirect()->route('login');
})->name('home');

Route::middleware('guest')->group(function (): void {
    Route::get('/login', [AuthenticatedSessionController::class, 'create'])->name('login');
    Route::post('/login', [AuthenticatedSessionController::class, 'store'])->name('login.store');
});

Route::middleware('auth')->group(function (): void {
    Route::post('/logout', [AuthenticatedSessionController::class, 'destroy'])->name('logout');
});

Route::middleware(['auth', 'active.company'])->group(function (): void {
    Route::get('/beranda', [DashboardController::class, 'index'])->name('dashboard');
    Route::post('/konteks-pt', [ActiveCompanyController::class, 'switch'])->name('active-company.switch');

    Route::prefix('master')->name('master.')->group(function (): void {
        Route::get('/produk', [ProductController::class, 'index'])->name('products.index');
        Route::get('/produk/tambah', [ProductController::class, 'create'])->name('products.create');
        Route::post('/produk', [ProductController::class, 'store'])->name('products.store');
        Route::get('/produk/{product}', [ProductController::class, 'show'])->name('products.show');

        Route::get('/gudang', [WarehouseController::class, 'index'])->name('warehouses.index');
        Route::get('/gudang/{warehouse}', [WarehouseController::class, 'show'])->name('warehouses.show');
        Route::post('/gudang/{warehouse}/rak', [WarehouseRackController::class, 'store'])->name('warehouses.racks.store');
        Route::patch('/gudang/{warehouse}/rak/{rack}/toggle', [WarehouseRackController::class, 'toggleActive'])
            ->name('warehouses.racks.toggle');
    });

    Route::get('/harga-pelanggan', [CustomerPricingController::class, 'index'])->name('master.pricing.index');

    Route::prefix('stok')->name('inventory.')->group(function (): void {
        Route::get('/saldo', [StockBalanceController::class, 'index'])->name('balances.index');
        Route::get('/lot', [LotController::class, 'index'])->name('lots.index');
        Route::get('/lot/{lot}', [LotController::class, 'show'])->name('lots.show');
        Route::get('/pergerakan', [StockMovementController::class, 'index'])->name('movements.index');
        Route::post('/pergerakan/pindah-rak', [StockMovementController::class, 'storeRackTransfer'])
            ->name('movements.rack-transfer');
    });

    Route::prefix('admin')->name('admin.')->group(function (): void {
        Route::get('/perusahaan', [CompanyController::class, 'index'])->name('companies.index');
        Route::get('/pengguna', [UserController::class, 'index'])->name('users.index');
        Route::post('/pengguna', [UserController::class, 'store'])->name('users.store');
        Route::post('/keanggotaan', [MembershipController::class, 'store'])->name('memberships.store');
        Route::patch('/keanggotaan/{membership}/toggle', [MembershipController::class, 'toggleActive'])
            ->name('memberships.toggle');
        Route::get('/audit-log', [AuditLogController::class, 'index'])->name('audit-logs.index');
    });

    Route::get('/grup/ringkasan', [SummaryController::class, 'index'])->name('group.summary.index');
});
