<?php

namespace App\Providers;

use App\Models\Payment;
use App\Models\Setting;
use App\Observers\PaymentObserver;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Schema\Builder;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        require_once app_path('Support/helpers.php');
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Suppress PHP 8.5 deprecation warnings that break Livewire AJAX responses
        error_reporting(E_ALL & ~E_DEPRECATED);

        if ($this->app->runningUnitTests()) {
            Model::unguard();
        }

        Builder::defaultStringLength(191);

        // Skip database-dependent logic if not yet installed
        if (! file_exists(storage_path('installed'))) {
            return;
        }

        try {
            Payment::observe(PaymentObserver::class);

            // Share global settings with all views
            if (Schema::hasTable('settings')) {
                View::composer('*', function ($view) {
                    $view->with('site_name', velocrm_company_name());
                    $view->with('site_title', velocrm_app_name());
                    $view->with('site_logo', Setting::get('logo'));
                    $view->with('site_favicon', Setting::get('favicon'));
                    $view->with('primary_color', Setting::get('primary_color', '#4f46e5'));
                    $view->with('currencySymbol', velocrm_currency_symbol());
                    $view->with('dateFormat', velocrm_date_format());
                });

                config([
                    'app.currency_symbol' => velocrm_currency_symbol(),
                    'app.date_format' => velocrm_date_format(),
                ]);
            }
        } catch (\Exception) {
            // Silently ignore — database may not exist during installation
        }
    }
}
