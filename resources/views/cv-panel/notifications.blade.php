@extends('cv-panel.layout')

@section('title', __('cv-panel.notifications.title'))

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        @foreach ([
            'all'          => __('cv-panel.notifications.tab_all'),
            'reservations' => __('cv-panel.notifications.tab_reservations'),
            'uploads'      => __('cv-panel.notifications.tab_uploads'),
            'unassigned'   => __('cv-panel.notifications.tab_unassigned'),
        ] as $key => $label)
            <a href="{{ route('cv-panel.notifications.index', array_filter([
                   'tab' => $key,
                   'unread' => request('unread'),
               ])) }}"
               class="cvp-btn {{ $tab === $key ? 'cvp-btn-navy' : 'cvp-btn-ghost' }}">
                {{ $label }}
                @if (($counts[$key] ?? 0) > 0)
                    <span class="cvp-badge" style="background:#dc2626;color:#fff">{{ $counts[$key] }}</span>
                @endif
            </a>
        @endforeach

        <div class="ms-auto d-flex gap-2">
            <a href="{{ route('cv-panel.notifications.index', array_filter([
                   'tab' => $tab,
                   'unread' => request()->boolean('unread') ? null : 1,
               ])) }}"
               class="cvp-btn {{ request()->boolean('unread') ? 'cvp-btn-gold' : 'cvp-btn-ghost' }}">
                {{ __('cv-panel.notifications.unread_only') }}
            </a>

            <form method="POST" action="{{ route('cv-panel.notifications.read-all') }}">
                @csrf
                <button type="submit" class="cvp-btn cvp-btn-ghost">
                    {{ __('cv-panel.notifications.mark_all') }}
                </button>
            </form>
        </div>
    </div>

    <div class="cvp-card overflow-hidden">
        @forelse ($notifications as $notification)
            <a href="{{ route('cv-panel.notifications.read', $notification) }}"
               class="cvp-notif {{ $notification->isUnread() ? 'is-unread' : '' }}">
                {{-- Hex colours inline; a hex in class= renders colourless. --}}
                <span class="cvp-notif-icon"
                      style="background:{{ $notification->icon_bg }};color:{{ $notification->icon_color }}">
                    {!! $notification->icon_svg !!}
                </span>
                <span class="flex-grow-1">
                    <span class="d-block small fw-bold">{{ $notification->title }}</span>
                    <span class="d-block small text-muted">{{ $notification->body }}</span>
                    <span class="d-block text-muted" style="font-size:.7rem">
                        {{ $notification->created_at->diffForHumans() }}
                    </span>
                </span>
            </a>
        @empty
            <p class="text-muted small text-center py-5 mb-0">
                {{ __('cv-panel.notifications.empty') }}
            </p>
        @endforelse
    </div>

    <div class="mt-3">{{ $notifications->links() }}</div>

@endsection
