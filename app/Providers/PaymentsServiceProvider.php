<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;

class PaymentsServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Interface & implementasi gateway pembayaran akan dibind di sini
        // pada modul integrasi Payment Gateway (FR-PAY-01, NFR-CON-05).
        // Contoh nanti:
        // $this->app->bind(PaymentGateway::class, SandboxQrisGateway::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        //
    }
}
