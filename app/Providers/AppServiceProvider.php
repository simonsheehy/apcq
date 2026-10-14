<?php

namespace App\Providers;

use App\Models\MenuItem;
use App\Models\SiteSetting;
use App\Models\Training;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

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
        View::composer(['layouts.app', 'contact'], function ($view): void {
            $view->with('siteSettings', SiteSetting::instance());
            $view->with('headerMenuItems', MenuItem::forHeader());
            $view->with('footerMenuItems', MenuItem::forFooter());
        });

        Route::bind('publishedTraining', fn (string $slug): Training => Training::published()
            ->where('slug', $slug)
            ->firstOrFail());
    }
}
