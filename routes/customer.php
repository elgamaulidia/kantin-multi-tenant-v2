<?php

use App\Http\Controllers\Modules\Ordering\Controllers\CustomerQrScanController;
use Illuminate\Support\Facades\Route;

// FR-CUS-01 — Identifikasi meja lewat QR Code (UC-02)
Route::get('/order/{token_meja}', CustomerQrScanController::class)
    ->middleware('throttle:qr-scan')
    ->name('scan');

// FR-CUS-02 — Telusuri menu & tenant (UC-01)
Route::view('/{canteen:slug}', 'customer-preview')->name('home');
