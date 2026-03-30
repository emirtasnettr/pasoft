<?php

namespace App\Providers;

use App\Models\Claim;
use App\Models\Customer;
use App\Models\InsurancePolicy;
use App\Models\Lead;
use App\Models\Task;
use App\Models\User;
use App\Observers\TaskObserver;
use Illuminate\Database\Eloquent\Relations\Relation;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\Facades\Vite;
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
        if ($this->app->environment('production') && config('app.force_https')) {
            URL::forceScheme('https');
        }

        Vite::prefetch(concurrency: 3);

        Task::observe(TaskObserver::class);

        Relation::enforceMorphMap([
            'customer' => Customer::class,
            'lead' => Lead::class,
            'policy' => InsurancePolicy::class,
            'claim' => Claim::class,
            'user' => User::class,
        ]);
    }
}
