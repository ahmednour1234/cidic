<?php

namespace App\Http\Controllers\Admin;

use App\Enums\AvailabilityStatus;
use App\Http\Controllers\Controller;
use App\Models\Candidate;
use App\Models\CandidateRequest;
use App\Models\ContactMessage;
use App\Models\Nationality;
use App\Models\RecruitmentRequest;
use App\Models\Service;
use Illuminate\View\View;

class DashboardController extends Controller
{
    public function index(): View
    {
        $stats = [
            'candidates_total' => Candidate::count(),
            'candidates_available' => Candidate::query()
                ->where('availability_status', AvailabilityStatus::Available->value)->count(),
            'candidates_reserved' => Candidate::query()
                ->where('availability_status', AvailabilityStatus::Reserved->value)->count(),
            'requests_new' => CandidateRequest::query()->new()->count()
                + RecruitmentRequest::query()->new()->count(),
            'requests_month' => CandidateRequest::query()
                ->where('created_at', '>=', now()->startOfMonth())->count()
                + RecruitmentRequest::query()
                    ->where('created_at', '>=', now()->startOfMonth())->count(),
            'messages' => ContactMessage::count(),
            'services' => Service::count(),
            'nationalities' => Nationality::count(),
        ];

        $recentRequests = CandidateRequest::query()
            ->with(['candidate:id,name,reference_number,profile_image'])
            ->latest()
            ->limit(8)
            ->get();

        $recentCandidates = Candidate::query()
            ->with(['nationality:id,name_ar,slug', 'category:id,name_ar,slug'])
            ->latest()
            ->limit(8)
            ->get();

        return view('admin.dashboard', [
            'stats' => $stats,
            'recentRequests' => $recentRequests,
            'recentCandidates' => $recentCandidates,
            'monthlyChart' => $this->monthlyRequestChart(),
            'nationalityChart' => $this->nationalityChart(),
        ]);
    }

    /**
     * Requests per month for the last six months, rendered as a CSS bar chart.
     *
     * @return list<array{label: string, value: int, height: int}>
     */
    protected function monthlyRequestChart(): array
    {
        $months = collect(range(5, 0))->map(fn (int $back) => now()->subMonths($back)->startOfMonth());

        $rows = $months->map(function ($month) {
            $end = (clone $month)->endOfMonth();

            $count = CandidateRequest::query()->whereBetween('created_at', [$month, $end])->count()
                + RecruitmentRequest::query()->whereBetween('created_at', [$month, $end])->count();

            return [
                'label' => $month->translatedFormat('M'),
                'value' => $count,
            ];
        })->all();

        return $this->withHeights($rows);
    }

    /**
     * Candidate distribution by nationality.
     *
     * @return list<array{label: string, value: int, height: int}>
     */
    protected function nationalityChart(): array
    {
        $rows = Nationality::query()
            ->withCount('candidates')
            ->orderByDesc('candidates_count')
            ->limit(6)
            ->get()
            ->map(fn (Nationality $n) => [
                'label' => $n->name_ar,
                'value' => (int) $n->candidates_count,
            ])
            ->all();

        return $this->withHeights($rows);
    }

    /**
     * Scale values to a 0-100 height so the view needs no chart library.
     *
     * @param  list<array{label: string, value: int}>  $rows
     * @return list<array{label: string, value: int, height: int}>
     */
    protected function withHeights(array $rows): array
    {
        $max = max(array_column($rows, 'value') ?: [0]);

        return array_map(function (array $row) use ($max) {
            $row['height'] = $max > 0 ? (int) round(($row['value'] / $max) * 100) : 0;

            return $row;
        }, $rows);
    }
}
