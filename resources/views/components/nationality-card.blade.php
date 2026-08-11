@props(['nationality'])

@php
    // Fall back to a bundled flag by country code when no image is uploaded.
    $code = strtolower((string) $nationality->country_code);
    $bundled = $code !== '' && file_exists(public_path("images/flags/{$code}.svg"))
        ? asset("images/flags/{$code}.svg")
        : null;

    $flag = $nationality->flag_url ?: $bundled;
@endphp

<a href="{{ route('candidates.index', ['nationality' => $nationality->slug]) }}" class="nationality-card">
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
