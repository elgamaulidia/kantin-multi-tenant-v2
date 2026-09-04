<?php

use App\Http\Middleware\EnsureTeamMembership;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome')->name('home');

Route::prefix('{current_team}')
    ->middleware(['auth', 'verified', EnsureTeamMembership::class])
    ->group(function () {
        Route::view('dashboard', 'dashboard')->name('dashboard');
    });

// === Modul 2: Route domain Kantin Multi-Tenant ===

// Customer — publik, tanpa auth (SRS: pelanggan pakai sesi anonim)
Route::name('customer.')
    ->group(base_path('routes/customer.php'));

// Tenant — internal, wajib login (FR-TEN-01/UC-19)
Route::middleware(['auth', 'verified', 'role:tenant'])
    ->prefix('tenant/{tenant:slug}')
    ->scopeBindings()
    ->name('tenant.')
    ->group(base_path('routes/tenant.php'));

// Admin — internal, wajib login (FR-TEN-01/UC-19)
Route::middleware(['auth', 'verified', 'role:admin'])
    ->prefix('admin')
    ->name('admin.')
    ->group(base_path('routes/admin.php'));

require __DIR__.'/settings.php';
