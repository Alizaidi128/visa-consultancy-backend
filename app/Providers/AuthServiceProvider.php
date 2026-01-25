<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;
use Illuminate\Support\Facades\Gate;

/* MODELS */
use App\Models\VisaApplication;
use App\Models\Document;
use App\Models\User;
use App\Models\Client;
use App\Models\Deadline;
use App\Models\Appointment;

/* POLICIES */
use App\Policies\VisaApplicationPolicy;
use App\Policies\DocumentPolicy;
use App\Policies\UserPolicy;
use App\Policies\ClientPolicy;
use App\Policies\DeadlinePolicy;
use App\Policies\AppointmentPolicy;

class AuthServiceProvider extends ServiceProvider
{
    protected $policies = [
        VisaApplication::class => VisaApplicationPolicy::class,
        Document::class        => DocumentPolicy::class,
        User::class            => UserPolicy::class,
        Client::class          => ClientPolicy::class,
        Deadline::class        => DeadlinePolicy::class,
        Appointment::class     => AppointmentPolicy::class,
    ];

    public function boot(): void
    {
        $this->registerPolicies();

        Gate::define('is-admin', fn (User $user) => $user->hasRole('admin'));
        Gate::define('is-consultant', fn (User $user) => $user->hasRole('consultant'));
        Gate::define('is-client', fn (User $user) => $user->hasRole('client'));
    }
}
