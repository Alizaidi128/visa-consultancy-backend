<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Inertia\Inertia;
use Illuminate\Support\Facades\URL;

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
        if (app()->environment('production')) {
            URL::forceScheme('https');
        }
        Inertia::share([
            'auth' => fn () => auth()->check() ? [
                'user' => auth()->user(),
                'roles' => auth()->user()->getRoleNames(),

                /*
                |--------------------------------------------------------------------------
                | Policy helpers for frontend
                |--------------------------------------------------------------------------
                | Allows Vue to check permissions safely:
                | $page.props.auth.can.submitVisa
                */
                'can' => [
                    'createVisa' => auth()->user()->can('create', \App\Models\VisaApplication::class),
                ],
            ] : null,
        ]);

    }
}
