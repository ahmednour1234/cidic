@props(['nationality'])

@php
    // photoUrl() already falls back to the bundled flag named after the ISO
    // code, so the lookup lives in one place.
    $flag = $nationality->photoUrl();
@endphp

{{-- Points at the CV catalogue, which is what the count above measures. --}}
<a href="{{ route('cvs.nationality', $nationality->public_key) }}" class="nationality-card">
    @if ($flag)
        <img src="{{ $flag }}" alt="علم {{ $nationality->name_ar }}"
             class="nationality-card__flag" loading="lazy">
    @else
        <span class="nationality-card__flag d-grid"
              style="place-items: center; font-weight: 800; color: var(--primary);">
            {{ mb_substr($nationality->name_ar, 0, 2) }}
        </span>
    @endif

    <span class="nationality-card__name d-block">{{ $nationality->name_ar }}</span>

    @isset($nationality->candidates_count)
        <span class="nationality-card__count">{{ $nationality->candidates_count }} سيرة متاحة</span>
    @endisset
</a>
