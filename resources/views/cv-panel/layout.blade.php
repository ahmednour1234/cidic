@php
    use App\Support\CvPanel\CvPanelPermissions;

    $me = auth()->user();
    $unread = $unreadNotifications ?? 0;
    $latest = $latestNotifications ?? collect();
@endphp
<!DOCTYPE html>
<html lang="{{ app()->getLocale() }}" dir="{{ in_array(app()->getLocale(), ['ar']) ? 'rtl' : 'ltr' }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">

    <title>@yield('title', __('cv-panel.title')) — {{ __('cv-panel.title') }}</title>

    @vite(['resources/css/app.css', 'resources/css/cv-panel.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="cvp">

    <header class="cvp-header">
        <div class="container-fluid px-3 px-lg-4 py-2 d-flex align-items-center gap-3">

            <a href="{{ route('cv-panel.dashboard') }}" class="cvp-brand">
                @if ($panelLogo = setting_image('logo'))
                    <span class="cvp-brand-logo"><img src="{{ $panelLogo }}" alt=""></span>
                @else
                    <span class="cvp-brand-mark">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>
                        </svg>
                    </span>
                @endif
                {{ __('cv-panel.title') }}
            </a>

            <div class="ms-auto d-flex align-items-center gap-2 gap-md-3">

                <span class="cvp-whoami d-none d-md-flex">
                    <span class="cvp-avatar">{{ mb_substr($me->name, 0, 1) }}</span>
                    <span class="fw-bold">{{ $me->name }}</span>
                </span>

                {{-- Bell. Alpine v3 API throughout; v2's __x.$data does not exist here. --}}
                <div class="cvp-bell" x-data="{ open: false }" @keydown.escape.window="open = false">
                    <button type="button" class="cvp-iconbtn position-relative"
                            @click="open = !open" :aria-expanded="open"
                            aria-label="{{ __('cv-panel.notifications.title') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" width="18" height="18">
                            <path d="M18 8a6 6 0 1 0-12 0c0 7-3 9-3 9h18s-3-2-3-9"/>
                            <path d="M13.73 21a2 2 0 0 1-3.46 0"/>
                        </svg>
                        @if ($unread > 0)
                            <span class="cvp-bell-count">{{ $unread > 99 ? '99+' : $unread }}</span>
                        @endif
                    </button>

                    <div class="cvp-bell-panel" x-show="open" x-cloak
                         @click.outside="open = false" x-transition.opacity>

                        <div class="d-flex align-items-center justify-content-between px-3 py-2 border-bottom">
                            <strong class="small">{{ __('cv-panel.notifications.title') }}</strong>
                            @if ($unread > 0)
                                <span class="small fw-bold" style="color:#0060a8">{{ $unread }}</span>
                            @endif
                        </div>

                        <div style="max-height:22rem; overflow-y:auto">
                            @forelse ($latest as $notification)
                                <a href="{{ route('cv-panel.notifications.read', $notification) }}"
                                   class="cvp-notif {{ $notification->isUnread() ? 'is-unread' : '' }}">
                                    {{-- Hex colours must be inline: a hex in class= renders colourless. --}}
                                    <span class="cvp-notif-icon"
                                          style="background:{{ $notification->icon_bg }};color:{{ $notification->icon_color }}">
                                        {!! $notification->icon_svg !!}
                                    </span>
                                    <span class="flex-grow-1">
                                        <span class="d-block small fw-bold">{{ $notification->title }}</span>
                                        <span class="d-block small text-muted">
                                            {{ \Illuminate\Support\Str::limit($notification->body, 90) }}
                                        </span>
                                        <span class="d-block text-muted" style="font-size:.7rem">
                                            {{ $notification->created_at->diffForHumans() }}
                                        </span>
                                    </span>
                                </a>
                            @empty
                                <p class="text-muted small text-center py-4 mb-0">
                                    {{ __('cv-panel.notifications.empty') }}
                                </p>
                            @endforelse
                        </div>

                        <a href="{{ route('cv-panel.notifications.index') }}"
                           class="d-block text-center py-2 small fw-bold text-decoration-none"
                           style="color:#003f74">
                            {{ __('cv-panel.notifications.view_all') }}
                        </a>
                    </div>
                </div>

                <form method="POST" action="{{ route('cv-panel.logout') }}" class="m-0">
                    @csrf
                    <button type="submit" class="cvp-iconbtn"
                            title="{{ __('cv-panel.nav.logout') }}"
                            aria-label="{{ __('cv-panel.nav.logout') }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" width="17" height="17">
                            <path d="M9 21H5a2 2 0 0 1-2-2V5a2 2 0 0 1 2-2h4"/>
                            <path d="m16 17 5-5-5-5"/><path d="M21 12H9"/>
                        </svg>
                    </button>
                </form>
            </div>
        </div>
    </header>

    <nav class="cvp-tabs px-3 px-lg-4">
        @php
            $tabs = [
                ['route' => 'cv-panel.dashboard',      'label' => __('cv-panel.nav.home'),  'show' => true],
                ['route' => 'cv-panel.cvs.index',      'label' => __('cv-panel.nav.cvs'),   'show' => true],
                ['route' => 'cv-panel.upload',         'label' => __('cv-panel.nav.upload'),
                 'show' => CvPanelPermissions::canUpload($me)],
                ['route' => 'cv-panel.cvs.reserved',   'label' => __('cv-panel.nav.reserved'),
                 'show' => CvPanelPermissions::canFollowUpReserved($me)],
                ['route' => 'cv-panel.guide',          'label' => __('cv-panel.nav.guide'), 'show' => true],
                ['route' => 'cv-panel.coordinators',   'label' => __('cv-panel.nav.coordinators'),
                 'show' => CvPanelPermissions::canManageUsers($me)],
                ['route' => 'cv-panel.users.index',    'label' => __('cv-panel.nav.users'),
                 'show' => CvPanelPermissions::canManageUsers($me)],
            ];
        @endphp

        @foreach ($tabs as $tab)
            @continue (! $tab['show'])
            <a href="{{ route($tab['route']) }}"
               class="cvp-tab {{ request()->routeIs($tab['route']) ? 'is-active' : '' }}">
                {{ $tab['label'] }}
            </a>
        @endforeach
    </nav>

    <main class="container-fluid px-3 px-lg-4 py-4">

        @foreach ([
            'success' => ['#ecfdf5', '#047857', '<path d="m5 13 4 4L19 7"/>'],
            'error'   => ['#fef2f2', '#991b1b', '<circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>'],
        ] as $key => [$bg, $fg, $icon])
            @if (session($key))
                {{-- Hex colours inline; a hex in class= renders colourless. --}}
                <div class="d-flex align-items-start gap-2 p-3 mb-3"
                     style="background:{{ $bg }};color:{{ $fg }};border-radius:.9rem;font-size:.88rem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"
                         stroke-linecap="round" stroke-linejoin="round" width="18" height="18"
                         style="flex:0 0 auto;margin-top:1px">{!! $icon !!}</svg>
                    <div class="fw-bold">{{ session($key) }}</div>
                </div>
            @endif
        @endforeach

        @if ($errors->any())
            <div class="d-flex align-items-start gap-2 p-3 mb-3"
                 style="background:#fef2f2;color:#991b1b;border-radius:.9rem;font-size:.88rem">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.1"
                     stroke-linecap="round" stroke-linejoin="round" width="18" height="18"
                     style="flex:0 0 auto;margin-top:1px">
                    <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                </svg>
                <ul class="mb-0 ps-3 fw-bold">
                    @foreach ($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        @yield('content')
    </main>

    @stack('scripts')
</body>
</html>
