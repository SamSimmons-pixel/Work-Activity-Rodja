<?php

namespace App\Providers;

use App\Models\Activity;
use App\Models\User;
use Illuminate\Support\Facades\Gate;
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
        // Superadmin bypass for Administrator role
        Gate::before(function (User $user, string $ability) {
            if ($user->hasRole('Administrator')) {
                return true;
            }
        });

        // Dynamic Gate check against Role Permissions
        Gate::after(function (User $user, string $ability, $result) {
            if ($result !== null) {
                return $result;
            }
            return $user->hasPermission($ability);
        });

        // Authorization Gate for Viewing an Activity (IDOR prevention)
        Gate::define('view-activity', function (User $user, Activity $activity) {
            if ($activity->user_id === $user->id) {
                return true;
            }

            if ($user->hasPermission('activity.read.all')) {
                return true;
            }

            if ($user->hasPermission('activity.read.division') && $user->division_id && $user->division_id === $activity->user?->division_id) {
                return true;
            }

            if ($user->hasPermission('activity.read.subordinate')) {
                return in_array($activity->user_id, $user->getSubordinateIds());
            }

            return false;
        });

        // Authorization Gate for Editing/Updating an Activity
        Gate::define('update-activity', function (User $user, Activity $activity) {
            return $activity->user_id === $user->id && $user->hasPermission('activity.update.own');
        });

        // Authorization Gate for Deleting an Activity
        Gate::define('delete-activity', function (User $user, Activity $activity) {
            return $activity->user_id === $user->id && $user->hasPermission('activity.delete.own');
        });
    }
}
