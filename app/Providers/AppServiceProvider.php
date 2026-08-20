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

        // Built assets and uploaded media both resolve against the document
        // root rather than APP_URL, so a deployment whose APP_URL does not
        // match the served host still finds them. $secure is ignored on
        // purpose: a root-relative URL inherits the requesting page's scheme.
        Vite::createAssetPathsUsing(static function (string $path, ?bool $secure = null): string {
            return static::documentRootPath($path);
        });

        // config/filesystems.php cannot do this itself: config is resolved (and
        // cached) before a request exists, so the prefix has to be applied here.
        config(['filesystems.disks.public.url' => static::documentRootPath('storage')]);

        $this->registerGates();
        $this->shareViewData();
    }

    /**
     * Prefix a path with the subdirectory the app is served from.
     *
     * The prefix comes from the live request, so an install served from a
     * subdirectory (.../public/) resolves correctly even when APP_URL does not
     * match the host. Under `artisan` there is no served request, so APP_URL's
     * path stands in instead.
     */
    protected static function documentRootPath(string $path): string
    {
        // A served request is authoritative even when its base path is empty:
        // that means a document-root install. Only fall back to APP_URL when
        // running under `artisan`, where the container still holds a Request
        // synthesised from CLI globals whose base path is meaningless.
        $base = app()->runningInConsole()
            ? trim((string) parse_url((string) config('app.url'), PHP_URL_PATH), '/')
            : trim((string) request()->getBasePath(), '/');

        return ($base === '' ? '' : '/' . $base) . '/' . ltrim($path, '/');
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
