<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\View;
use App\Models\Inventory;
use Illuminate\Pagination\Paginator;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {

        Paginator::useBootstrap();
        // Share low stock items globally
        View::composer('*', function ($view) {
            $lowStockItems = \App\Models\Inventory::where('quantity', '<=', 5)->orderBy('quantity')->get();
            $view->with('lowStockItems', $lowStockItems);
        });
    }
}
