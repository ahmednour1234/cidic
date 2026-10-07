<?php

namespace App\Providers;

use App\Models\AdminNotification;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\View;
use Illuminate\Support\ServiceProvider;

class CvPanelServiceProvider extends ServiceProvider
{
    /**
     * The panel layout shows the notification bell on every page, so the data
     * is composed once here rather than repeated in each controller.
     */
    public function boot(): void
    {
        View::composer('cv-panel.layout', function ($view) {
            $user = Auth::user();

            if ($user === null) {
                return;
            }

            $scope = AdminNotification::query()
                ->where('admin_id', $user->id)
                ->panelTypes();

            $view->with([
                'unreadNotifications' => (clone $scope)->unread()->count(),
                'latestNotifications' => (clone $scope)->latest('id')->limit(6)->get(),
            ]);
        });
    }
}
