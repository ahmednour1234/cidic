@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.home'))

@php
    // [key, label, tint, ink, icon path]
    // Tints drawn from the CIDIC palette: brand blue for the primary figures,
    // green/amber only where the state itself carries that meaning.
    $icons = [
        'available' => ['#e8f6ef', '#1a9d63', '<path d="M20 6 9 17l-5-5"/>'],
        'reserved'  => ['#e6f0f9', '#0060a8', '<path d="M6 4h12v16l-6-4-6 4z"/>'],
        'assigned'  => ['#e9eef4', '#003f74', '<path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/><path d="m17 11 2 2 4-4"/>'],
        'today'     => ['#fdf3e3', '#d38b1a', '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
        'my_reservations' => ['#e6f0f9', '#0060a8', '<path d="M6 4h12v16l-6-4-6 4z"/>'],
        'my_completed'    => ['#e8f6ef', '#1a9d63', '<path d="M20 6 9 17l-5-5"/>'],
        'my_today'        => ['#fdf3e3', '#d38b1a', '<rect x="3" y="4" width="18" height="18" rx="2"/><path d="M16 2v4M8 2v4M3 10h18"/>'],
    ];

    $cards = $isAgent
        ? [
            ['my_reservations', __('cv-panel.dashboard.my_reservations')],
            ['my_completed',    __('cv-panel.dashboard.my_completed')],
            ['my_today',        __('cv-panel.dashboard.my_today')],
            ['available',       __('cv-panel.dashboard.available')],
        ]
        : [
            ['available', __('cv-panel.dashboard.available')],
            ['reserved',  __('cv-panel.dashboard.reserved')],
            ['assigned',  __('cv-panel.dashboard.assigned')],
            ['today',     __('cv-panel.dashboard.today')],
        ];
@endphp

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <div>
            <h1 class="cvp-page-title">مرحبًا، {{ auth()->user()->name }}</h1>
            <p class="cvp-page-sub">
                {{ $isAgent ? 'نظرة على حجوزاتك والسير المتاحة.' : 'نظرة عامة على السير الذاتية في نطاقك.' }}
            </p>
        </div>

        @if (\App\Support\CvPanel\CvPanelPermissions::canUpload(auth()->user()))
            <a href="{{ route('cv-panel.upload') }}" class="cvp-btn cvp-btn-gold ms-auto">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" width="15" height="15">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                {{ __('cv-panel.nav.upload') }}
            </a>
        @endif
    </div>

    {{-- Stats --}}
    <div class="row g-3 mb-4">
        @foreach ($cards as [$key, $label])
            @php [$tint, $ink, $path] = $icons[$key] ?? ['#f1f5f9', '#475569', '<circle cx="12" cy="12" r="9"/>']; @endphp

            <div class="col-6 col-lg-3">
                <div class="cvp-card cvp-stat h-100">
                    {{-- Hex values inline: a hex inside class= renders colourless. --}}
                    <span class="cvp-stat-icon" style="background:{{ $tint }};color:{{ $ink }}">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                             stroke-linecap="round" stroke-linejoin="round" width="20" height="20">
                            {!! $path !!}
                        </svg>
                    </span>
                    <span>
                        <span class="cvp-stat-value d-block">{{ number_format($stats[$key] ?? 0) }}</span>
                        <span class="cvp-stat-label d-block">{{ $label }}</span>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    {{-- Nationalities --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <h2 class="cvp-section-title">{{ __('cv-panel.dashboard.nationalities') }}</h2>
        @if ($nationalities->isNotEmpty())
            <span class="cvp-badge" style="background:#f1f5f9;color:#475569">{{ $nationalities->count() }}</span>
        @endif
    </div>

    @if ($nationalities->isEmpty())
        <div class="cvp-card cvp-empty mb-4">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                <circle cx="12" cy="12" r="10"/><path d="M2 12h20"/>
                <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10"/>
            </svg>
            <p class="mb-0" style="font-size:.9rem">{{ __('cv-panel.dashboard.no_nationalities') }}</p>
        </div>
    @else
        <div class="row g-3 mb-4">
            @foreach ($nationalities as $nationality)
                @php $publicUrl = route('cvs.nationality', $nationality->public_key); @endphp

                <div class="col-12 col-md-6 col-xl-4">
                    <div class="cvp-card cvp-nat" x-data="{ copied: false }">

                        <div class="cvp-nat-head">
                            @if ($photo = $nationality->photoUrl())
                                <img src="{{ $photo }}" alt="" class="cvp-nat-flag" width="42" height="42">
                            @else
                                <span class="cvp-nat-flag-fallback">
                                    {{ $nationality->code ?: mb_substr($nationality->display_name, 0, 2) }}
                                </span>
                            @endif

                            <div class="flex-grow-1 min-w-0">
                                <a href="{{ route('cv-panel.cvs.index', ['nationality_id' => $nationality->id]) }}"
                                   class="cvp-nat-name">{{ $nationality->display_name }}</a>
                                <span class="cvp-nat-count">
                                    {{ __('cv-panel.dashboard.count_available', ['count' => $nationality->available_count]) }}
                                </span>
                            </div>

                            <span class="cvp-badge"
                                  style="background:{{ $nationality->available_count > 0 ? '#ecfdf5' : '#f1f5f9' }};
                                         color:{{ $nationality->available_count > 0 ? '#047857' : '#94a3b8' }}">
                                {{ $nationality->available_count }}
                            </span>
                        </div>

                        <div class="cvp-nat-body">
                            <label class="cvp-label" style="font-size:.75rem;color:#64748b">
                                {{ __('cv-panel.dashboard.public_link') }}
                            </label>

                            <div class="cvp-linkbox mb-2">
                                <input type="text" readonly dir="ltr" value="{{ $publicUrl }}"
                                       x-ref="link" @focus="$event.target.select()">

                                <button type="button" class="cvp-btn cvp-btn-ghost"
                                        :class="copied && 'cvp-copy-ok'"
                                        style="padding:.3rem .55rem"
                                        :title="copied ? @js(__('cv-panel.dashboard.copied')) : @js(__('cv-panel.dashboard.copy'))"
                                        @click="
                                            const value = $refs.link.value;
                                            const done = () => { copied = true; setTimeout(() => copied = false, 1500); };
                                            if (navigator.clipboard) {
                                                navigator.clipboard.writeText(value).then(done).catch(() => {
                                                    $refs.link.select(); document.execCommand('copy'); done();
                                                });
                                            } else {
                                                $refs.link.select(); document.execCommand('copy'); done();
                                            }
                                        ">
                                    <svg x-show="! copied" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.9" stroke-linecap="round" stroke-linejoin="round"
                                         width="14" height="14">
                                        <rect x="9" y="9" width="13" height="13" rx="2"/>
                                        <path d="M5 15H4a2 2 0 0 1-2-2V4a2 2 0 0 1 2-2h9a2 2 0 0 1 2 2v1"/>
                                    </svg>
                                    <svg x-show="copied" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="2.4" stroke-linecap="round" stroke-linejoin="round"
                                         width="14" height="14">
                                        <path d="m5 13 4 4L19 7"/>
                                    </svg>
                                </button>
                            </div>

                            <a href="{{ $publicUrl }}" target="_blank" rel="noopener"
                               class="d-inline-flex align-items-center gap-1 text-decoration-none fw-bold"
                               style="color:#0060a8;font-size:.8rem">
                                {{ __('cv-panel.dashboard.open_public') }}
                                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                                     stroke-linecap="round" stroke-linejoin="round" width="13" height="13">
                                    <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                    <path d="M15 3h6v6"/><path d="M10 14 21 3"/>
                                </svg>
                            </a>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    {{-- Recent --}}
    <div class="d-flex align-items-center gap-2 mb-3">
        <h2 class="cvp-section-title">
            {{ $isAgent ? __('cv-panel.dashboard.recent_reservations') : __('cv-panel.dashboard.recent_uploads') }}
        </h2>
        <a href="{{ route('cv-panel.cvs.index') }}" class="ms-auto text-decoration-none fw-bold"
           style="color:#0060a8;font-size:.8rem">عرض الكل ←</a>
    </div>

    <div class="cvp-card overflow-hidden">
        @if ($recent->isEmpty())
            <div class="cvp-empty">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                     stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6"/>
                </svg>
                <p class="mb-0" style="font-size:.9rem">{{ __('cv-panel.list.empty') }}</p>
            </div>
        @else
            <div class="table-responsive">
                <table class="table cvp-table align-middle">
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الجنسية</th>
                            <th>الحالة</th>
                            <th>العميل</th>
                            <th>التاريخ</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($recent as $worker)
                            <tr>
                                <td style="color:#94a3b8">{{ $worker->id }}</td>
                                <td class="fw-bold">{{ $worker->name ?? '—' }}</td>
                                <td><x-cv-panel.nationality :nationality="$worker->nationality" /></td>
                                <td>
                                    <x-cv-panel.status-badge
                                        :status="$worker->status"
                                        :withdrawn="$worker->status === 'available' && $worker->isWithdrawn()" />
                                </td>
                                <td>{{ $worker->client?->name ?? '—' }}</td>
                                <td style="color:#64748b;font-size:.82rem">
                                    {{ ($isAgent ? $worker->assigned_at : $worker->created_at)?->format('Y-m-d') ?? '—' }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>

@endsection
