<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use App\Models\AdminNotification;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\View\View;

class NotificationController extends Controller
{
    /** Tab => the types it covers. */
    private const TABS = [
        'all' => AdminNotification::PANEL_TYPES,
        'reservations' => [AdminNotification::TYPE_CV_RESERVED],
        'uploads' => [AdminNotification::TYPE_CV_UPLOADED],
        'unassigned' => [AdminNotification::TYPE_UNASSIGNED],
    ];

    public function index(Request $request): View
    {
        $tab = array_key_exists($request->input('tab'), self::TABS)
            ? $request->input('tab')
            : 'all';

        $notifications = $this->scope($request)
            ->whereIn('type', self::TABS[$tab])
            ->when($request->boolean('unread'), fn ($q) => $q->unread())
            ->latest('id')
            ->paginate(30)
            ->withQueryString();

        // Only the page in front of the user is marked read.
        $unreadOnPage = $notifications->getCollection()
            ->filter(fn (AdminNotification $n) => $n->isUnread())
            ->pluck('id');

        if ($unreadOnPage->isNotEmpty()) {
            AdminNotification::whereIn('id', $unreadOnPage)->update(['read_at' => now()]);
        }

        return view('cv-panel.notifications', [
            'notifications' => $notifications,
            'tab' => $tab,
            'counts' => $this->unreadCounts($request),
        ]);
    }

    /** Marks one notification read and forwards to its target. */
    public function read(Request $request, AdminNotification $notification): RedirectResponse
    {
        abort_unless($notification->admin_id === $request->user()->id, 403);

        if ($notification->isUnread()) {
            $notification->update(['read_at' => now()]);
        }

        $url = $notification->url ?? route('cv-panel.notifications.index');

        // A link into the main admin area would 403 for panel-only staff, so
        // rewrite it to the equivalent panel view.
        if (preg_match('#/workers/(\d+)#', $url, $matches) === 1) {
            return redirect()->route('cv-panel.cvs.index', ['search' => $matches[1]]);
        }

        return redirect()->to($url);
    }

    public function readAll(Request $request): JsonResponse|RedirectResponse
    {
        $this->scope($request)->unread()->update(['read_at' => now()]);

        if ($request->expectsJson()) {
            return response()->json(['ok' => true]);
        }

        return back();
    }

    /** @return array<string, int> */
    private function unreadCounts(Request $request): array
    {
        $counts = [];

        foreach (self::TABS as $tab => $types) {
            $counts[$tab] = $this->scope($request)->unread()->whereIn('type', $types)->count();
        }

        return $counts;
    }

    private function scope(Request $request): Builder
    {
        return AdminNotification::query()
            ->where('admin_id', $request->user()->id)
            ->panelTypes();
    }
}
