<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use App\Models\Nationality;
use App\Models\Worker;
use App\Support\CvPanel\CvPanelPermissions;
use Illuminate\Http\Request;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(Request $request): View
    {
        $user = $request->user();
        $scope = CvPanelPermissions::visibleNationalityIds($user);
        $isAgent = $user->isCustomerService() && ! $user->isSuperAdmin();

        $nationalities = Nationality::query()
            ->when($scope !== null, fn ($q) => $q->whereIn('id', $scope))
            ->ordered()
            ->get()
            ->map(function (Nationality $nationality) {
                // Counts exactly what the public site shows, so a withdrawn CV
                // is never advertised as available.
                $nationality->available_count = Worker::query()
                    ->publiclyVisible()
                    ->where('nationality_id', $nationality->id)
                    ->count();

                return $nationality;
            });

        return view('cv-panel.dashboard', [
            'isAgent' => $isAgent,
            'stats' => $isAgent ? $this->agentStats($user, $scope) : $this->staffStats($scope),
            'nationalities' => $nationalities,
            'recent' => $this->recent($user, $scope, $isAgent),
        ]);
    }

    private function staffStats(?array $scope): array
    {
        $base = fn () => Worker::query()->forNationalities($scope);

        return [
            'available' => Worker::query()->publiclyVisible()->forNationalities($scope)->count(),
            'reserved' => $base()->where('status', Worker::STATUS_RESERVED)->count(),
            'assigned' => $base()->where('status', Worker::STATUS_ASSIGNED)->count(),
            'today' => $base()->whereDate('created_at', today())->count(),
        ];
    }

    private function agentStats($user, ?array $scope): array
    {
        $mine = fn () => Worker::query()
            ->forNationalities($scope)
            ->where('assigned_by_admin_id', $user->id);

        return [
            'my_reservations' => $mine()->where('status', Worker::STATUS_RESERVED)->count(),
            'my_completed' => $mine()->where('status', Worker::STATUS_ASSIGNED)->count(),
            'my_today' => $mine()->whereDate('assigned_at', today())->count(),
            'available' => Worker::query()->publiclyVisible()->forNationalities($scope)->count(),
        ];
    }

    private function recent($user, ?array $scope, bool $isAgent)
    {
        return Worker::query()
            ->with(['nationality', 'client'])
            ->forNationalities($scope)
            ->when($isAgent,
                fn ($q) => $q->where('assigned_by_admin_id', $user->id)->latest('assigned_at'),
                fn ($q) => $q->whereNotNull('cv_path')->latest('id'))
            ->limit(10)
            ->get();
    }
}
