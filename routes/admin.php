<?php

use App\Modules\Admin\Http\Controllers\AdminCommissionController;
use App\Modules\Admin\Http\Controllers\AdminTenantController;
use Illuminate\Support\Facades\Route;

// FR-ADM-01 — Kelola tenant & skema komisi (UC-21)

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

// FR-ADM-02 — Kelola meja & QR Code (UC-22)

Route::get('/tables', function () {
    return 'Kelola meja (belum diimplementasikan)';
})->name('tables.index');