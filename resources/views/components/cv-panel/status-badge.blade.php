@props(['status', 'withdrawn' => false])

@php
    // Hex values must be applied inline: a hex colour inside class= renders
    // with no colour at all.
    $palette = [
        'available'  => ['#ecfdf5', '#047857'],
        'reserved'   => ['#e6f0f9', '#0060a8'],
        'assigned'   => ['#eef2ff', '#4338ca'],
        'in_housing' => ['#f0fdfa', '#0f766e'],
        'for_rent'   => ['#fef3c7', '#b45309'],
        'returned'   => ['#fef2f2', '#b91c1c'],
    ];

    [$bg, $fg] = $palette[$status] ?? ['#f1f5f9', '#475569'];

    $label = __('workers.status.'.$status);

    // Fall back to the raw key only if no label exists; never print the key
    // when a translation is available.
    if ($label === 'workers.status.'.$status) {
        $label = $status;
    }
@endphp

<span class="cvp-badge" style="background:{{ $bg }};color:{{ $fg }}">{{ $label }}</span>

@if ($withdrawn)
    <span class="cvp-badge ms-1" style="background:#f1f5f9;color:#475569">
        {{ __('cv-panel.list.withdrawn') }}
    </span>
@endif
