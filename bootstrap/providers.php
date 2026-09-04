<?php

use App\Providers\AdminServiceProvider;
use App\Providers\AppServiceProvider;
use App\Providers\CatalogServiceProvider;
use App\Providers\FortifyServiceProvider;
use App\Providers\KitchenServiceProvider;
use App\Providers\OrderingServiceProvider;
use App\Providers\PaymentsServiceProvider;
use App\Providers\ReportingServiceProvider;

return [
    AppServiceProvider::class,
    FortifyServiceProvider::class,
    // Modular monolith — satu provider per bounded context (Modul 2).
    AdminServiceProvider::class,
    CatalogServiceProvider::class,
    OrderingServiceProvider::class,
    PaymentsServiceProvider::class,
    KitchenServiceProvider::class,
    ReportingServiceProvider::class,
];
