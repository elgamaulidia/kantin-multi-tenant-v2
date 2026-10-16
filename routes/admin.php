<?php

use App\Modules\Admin\Http\Controllers\AdminCommissionController;
use App\Modules\Admin\Http\Controllers\AdminDiningTableController;
use App\Modules\Admin\Http\Controllers\AdminTenantController;
use Illuminate\Support\Facades\Route;

// Dashboard admin â€” UC-19
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// FR-ADM-01 â€” Kelola tenant & skema komisi (UC-21)

Route::get('/tenants', [AdminTenantController::class, 'index'])
    ->name('tenants.index');

Route::get('/tenants/create', [AdminTenantController::class, 'create'])
    ->name('tenants.create');

Route::post('/tenants', [AdminTenantController::class, 'store'])
    ->name('tenants.store');

Route::post('/tenants/{tenant}/commission', [
    AdminCommissionController::class,
    'store',
])->name('tenants.commission.store');

Route::get('/tenants/{tenant}', [AdminTenantController::class, 'edit'])
    ->name('tenants.edit');

// FR-ADM-02 â€” Kelola meja & QR Code (UC-22)

Route::get('/tables', [AdminDiningTableController::class, 'index'])
    ->name('tables.index');

Route::post('/tables', [AdminDiningTableController::class, 'store'])
    ->name('tables.store');

Route::put('/tables/{table}', [AdminDiningTableController::class, 'update'])
    ->name('tables.update');

Route::post('/tables/{table}/qr', [AdminDiningTableController::class, 'issueQr'])
    ->name('tables.qr.issue');

Route::delete('/tables/{table}/qr/{qr}', [AdminDiningTableController::class, 'revokeQr'])
    ->name('tables.qr.revoke');
