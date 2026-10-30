<?php

use App\Models\Tenant;
use Illuminate\Support\Facades\Route;

// Dashboard tenant — UC-19
Route::get('/dashboard', function () {
    return view('dashboard');
})->name('dashboard');

// FR-TEN-02 — Kelola menu & stok (UC-13)

Route::get('/menu', function () {
    return 'Kelola menu tenant (belum diimplementasikan)';
})->name('menu.index');

// FR-TEN-04 — Kitchen Display System (UC-15)

Route::get('/kds', function () {
    return 'KDS tenant (belum diimplementasikan)';
})->name('kds');

// FR-TEN-05 — Laporan penjualan (UC-16)

Route::get('/reports', function () {
    return 'Laporan tenant (belum diimplementasikan)';
})->name('reports');

// UC-13 / UC-14 - Kelola menu & stok (komponen Livewire catalog::menu-manager, Modul 7)
Route::get('/menu-manager', fn (Tenant $tenant) => view('catalog::tenant.menu-manager', ['tenant' => $tenant]))
    ->name('menu-manager');
