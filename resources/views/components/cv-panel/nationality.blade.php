@props(['nationality', 'nameless' => false])

@php
    $flag = $nationality?->photoUrl();
    $label = $nationality?->display_name ?? '—';
@endphp

{{-- merge() folds any passed class into this one; a second class attribute
     would be ignored by the browser and silently drop the styling. --}}
<span {{ $attributes->merge(['class' => 'cvp-nationality']) }}>
    @if ($flag)
        {{-- Dimensions come from CSS (4:3), so no width/height here. --}}
        <img src="{{ $flag }}" alt="" class="cvp-flag" loading="lazy">
    @elseif ($nationality?->code)
        {{-- No bundled SVG for this code; the code itself still identifies it. --}}
        <span class="cvp-flag cvp-flag-text">{{ $nationality->code }}</span>
    @endif

    @unless ($nameless)
        <span>{{ $label }}</span>
    @endunless
</span>
