<?php

namespace App\Providers;

use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;
use Livewire\Livewire;

class CatalogServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // view('catalog::tenant.menu-manager')
        View::addNamespace(
            'catalog',
            base_path('app/Modules/Catalog/resources/views')
        );

        // <livewire:catalog::menu-manager /> dan php artisan make:livewire catalog::...
        Livewire::addNamespace(
            'catalog',
            viewPath: base_path('app/Modules/Catalog/resources/views/livewire')
        );
    }
}
