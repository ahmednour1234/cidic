<?php

namespace App\Providers;

use App\Enums\Permission;
use App\Models\User;
use App\Services\ActivityLogger;
use App\Services\FileUploadService;
use App\Services\ReferenceNumberService;
use App\Services\SettingService;
use App\View\Composers\AdminNavComposer;
use Illuminate\Pagination\Paginator;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Vite;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        // Shared instances: settings memoise per request, the rest are stateless.
        $this->app->singleton(SettingService::class);
        $this->app->singleton(FileUploadService::class);
        $this->app->singleton(ReferenceNumberService::class);
        $this->app->singleton(ActivityLogger::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Bootstrap 5 markup matches the admin/public styling.
        Paginator::useBootstrapFive();

        // Emit built asset URLs relative to the document root instead of
        // resolving them against APP_URL. A deployment whose APP_URL does not
        // exactly match the served host would otherwise request every
        // stylesheet from the wrong domain and render unstyled.
        Vite::createAssetPathsUsing(static function (string $path, ?bool $secure = null): string {
            $base = rtrim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/');

            return $base . '/' . ltrim($path, '/');
        });

        $this->registerGates();
        $this->shareViewData();
    }

    /**
     * One gate per permission, backed by the user's role.
     */
    protected function registerGates(): void
    {
        foreach (Permission::cases() as $permission) {
            Gate::define($permission->value, fn (User $user) => $user->hasPermission($permission));
        }

        // Any authenticated, active staff member may open the dashboard shell.
        Gate::define('access-admin', fn (User $user) => $user->is_active);
    }

    /**
     * Expose settings to every view without each controller passing them.
     */
    protected function shareViewData(): void
    {
        View::composer('*', function ($view) {
            $view->with('siteSettings', app(SettingService::class));
        });

        // Sidebar badge counts, resolved only for the admin chrome.
        View::composer('admin.partials.sidebar', AdminNavComposer::class);
    }
}
