<?php

use App\Modules\Ordering\Http\Controllers\CustomerWelcomeController;
use Illuminate\Support\Facades\Route;

// FR-CUS-02 — Telusuri menu & tenant (UC-01)
Route::view('/{canteen:slug}', 'customer.home')->name('home');
Route::get('/{canteen:slug}/meja', CustomerWelcomeController::class)->name('welcome');
