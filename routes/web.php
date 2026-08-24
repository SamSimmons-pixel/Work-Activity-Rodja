<?php

use App\Http\Controllers\ExportController;
use App\Livewire\Admin\Organization\Index as OrganizationIndex;
use App\Livewire\Admin\Roles\Index as RolesIndex;
use App\Livewire\Admin\Users\Index as UsersIndex;
use App\Livewire\Auth\Login;
use App\Livewire\Dashboard\Index as DashboardIndex;
use App\Livewire\Organization\Chart as OrganizationChart;
use App\Livewire\Profile\Index as ProfileIndex;
use App\Livewire\Reports\MonthlyReport;
use App\Livewire\Reviews\Index as ReviewsIndex;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Route;

// Redirect root to dashboard
Route::get('/', function () {
    return redirect()->route('dashboard');
});

// Guest Authentication Routes
Route::middleware('guest')->group(function () {
    Route::get('/login', Login::class)->name('login');
});

// Authenticated Routes
Route::middleware('auth')->group(function () {
    Route::get('/dashboard', DashboardIndex::class)->name('dashboard');
    Route::get('/profile', ProfileIndex::class)->name('profile');
    Route::get('/reports/monthly', MonthlyReport::class)->name('reports.monthly');
    Route::get('/activities/export-excel', [ExportController::class, 'exportMonthlyActivities'])->name('activities.export-excel');
    Route::get('/organization-chart', OrganizationChart::class)->name('organization.chart');
    Route::get('/performance-reviews', ReviewsIndex::class)->name('reviews.index');

    // Admin Routes protected by RBAC permissions
    Route::prefix('admin')->name('admin.')->group(function () {
        Route::get('/users', UsersIndex::class)->name('users')->middleware('can:user.manage');
        Route::get('/organization', OrganizationIndex::class)->name('organization')->middleware('can:division.manage');
        Route::get('/roles', RolesIndex::class)->name('roles')->middleware('can:role.manage');
    });

    Route::post('/logout', function (Request $request) {
        Auth::logout();
        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    })->name('logout');
});
