<?php

namespace App\Providers;

use App\Policies\OrangTua\ParentDashboardPolicy;
use App\Repositories\Contracts\ParentPortalRepositoryInterface;
use App\Repositories\Eloquent\ParentPortalRepository;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->bind(ParentPortalRepositoryInterface::class, ParentPortalRepository::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Gate::define('parent.dashboard.view', [ParentDashboardPolicy::class, 'view']);
        Gate::define('parent.dashboard.notes', [ParentDashboardPolicy::class, 'manageNotes']);
        Gate::define('parent.dashboard.schedules', [ParentDashboardPolicy::class, 'viewSchedules']);
    }
}
