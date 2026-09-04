<?php

use Illuminate\Support\Facades\Route;

// FR-ADM-01 — Kelola tenant & skema komisi (UC-21)
Route::get('/tenants', function () {
    return 'Kelola tenant (belum diimplementasikan)';
})->name('tenants.index');

// FR-ADM-02 — Kelola meja & QR Code (UC-22)
Route::get('/tables', function () {
    return 'Kelola meja (belum diimplementasikan)';
})->name('tables.index');
