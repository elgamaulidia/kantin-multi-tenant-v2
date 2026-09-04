<?php

use Illuminate\Support\Facades\Route;

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
