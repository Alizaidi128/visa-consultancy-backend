<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/* MODELS */
use App\Models\VisaApplication;
use App\Models\Document;
use App\Models\User;

/* POLICIES */
use App\Policies\VisaApplicationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\UserPolicy;
use App\Models\Client;
use App\Policies\ClientPolicy;
use App\Models\Deadline;
use App\Policies\DeadlinePolicy;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * The policy mappings for the application.
     */
    protected $policies = [
        VisaApplication::class => VisaApplicationPolicy::class,
        Document::class => DocumentPolicy::class,
        User::class => UserPolicy::class,
        Client::class => ClientPolicy::class,
        Deadline::class => DeadlinePolicy::class,
    ];

    /**
     * Register any authentication / authorization services.
     */
    public function boot(): void
    {
        $this->registerPolicies();

        /*
        |--------------------------------------------------------------------------
        | GLOBAL ROLE SHORTCUTS (OPTIONAL BUT USEFUL)
        |--------------------------------------------------------------------------
        */

        Gate::define('is-admin', fn (User $user) => $user->hasRole('admin'));
        Gate::define('is-consultant', fn (User $user) => $user->hasRole('consultant'));
        Gate::define('is-client', fn (User $user) => $user->hasRole('client'));
    }
}
