<?php

namespace App\Providers;

use App\Models\Enquiry;
use App\Models\JobApplication;
use App\Models\Service;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\URL;
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
        // Canonical URLs, the sitemap and asset links must be https when the site is served over https
        // (behind a TLS-terminating proxy Laravel would otherwise build http:// links).
        if (str_starts_with((string) config('app.url'), 'https://')) {
            URL::forceScheme('https');
        }

        // The site has no Tailwind, so use our own pagination markup.
        Paginator::defaultView('vendor.pagination.site');
        Paginator::defaultSimpleView('vendor.pagination.site');

        // The enquiry form (home + contact) offers the live list of services.
        View::composer('components.enquiry-form', function ($view) {
            $view->with('serviceOptions', Service::active()->ordered()->pluck('title'));
        });

        // Unread counters in the admin sidebar.
        View::composer('layouts.admin', function ($view) {
            $view->with([
                'newApplications' => JobApplication::where('status', 'new')->count(),
                'unreadEnquiries' => Enquiry::unread()->count(),
            ]);
        });
    }
}
