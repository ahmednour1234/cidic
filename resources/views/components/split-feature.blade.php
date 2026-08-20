@props([
    'image',
    'alt' => '',
    'eyebrow' => null,
    'title',
    'text' => null,
    'items' => [],
    'reverse' => false,
    'badgeNum' => null,
    'badgeLabel' => null,
    'ctaLabel' => null,
    'ctaUrl' => null,
])

<div class="split {{ $reverse ? 'split--reverse' : '' }}">
    <div class="split__media" data-reveal="{{ $reverse ? 'left' : 'right' }}">
        <img src="{{ $image }}" alt="{{ $alt }}" class="split__img" loading="lazy" decoding="async">

        @if ($badgeNum)
            <span class="split__badge">
                <span class="split__badge-num">{{ $badgeNum }}</span>
                <span class="split__badge-label">{{ $badgeLabel }}</span>
            </span>
        @endif
    </div>

    <div data-reveal="{{ $reverse ? 'right' : 'left' }}" data-reveal-delay="120">
        @if ($eyebrow)
            <span class="split__eyebrow">{{ $eyebrow }}</span>
        @endif

        <h2 class="split__title">{{ $title }}</h2>

        @if ($text)
            <p class="split__text">{{ $text }}</p>
        @endif

        @if (! empty($items))
            <ul class="split__list">
                @foreach ($items as $item)
                    <li>
                        <svg width="18" height="18" viewBox="0 0 20 20" fill="none" aria-hidden="true">
                            <path d="M4 10.5l4 4 8-9" stroke="currentColor" stroke-width="2.4"
                                  stroke-linecap="round" stroke-linejoin="round"/>
                        </svg>
                        <span>{{ $item }}</span>
                    </li>
                @endforeach
            </ul>
        @endif

        @if ($ctaLabel && $ctaUrl)
            <a href="{{ $ctaUrl }}" class="btn btn-primary btn-pill">{{ $ctaLabel }}</a>
        @endif
    </div>
</div>
