<?php

use Illuminate\Support\Facades\Route;

// FR-CUS-01 — Identifikasi meja lewat QR Code (UC-02)
Route::get('/order/{token_meja}', function (string $token_meja) {
    return "Scan meja: {$token_meja} (belum diimplementasikan)";
})->name('scan');

// FR-CUS-02 — Telusuri menu & tenant (UC-01)
Route::view('/{canteen:slug}', 'customer-preview')->name('home');
