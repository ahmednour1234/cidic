@props(['testimonial'])

@php
    $rating = max(0, min(5, (int) $testimonial->rating));
@endphp

<article class="testimonial-card">
    <div class="testimonial-card__head">
        @if ($testimonial->avatar_url)
            <img src="{{ $testimonial->avatar_url }}" alt="" class="testimonial-card__avatar" loading="lazy">
        @else
            <span class="testimonial-card__avatar d-grid" style="place-items: center; font-weight: 700; color: var(--primary);">
                {{ mb_substr($testimonial->name, 0, 1) }}
            </span>
        @endif

        <span>
            <span class="testimonial-card__name d-block">{{ $testimonial->name }}</span>
            @if ($testimonial->city)
                <span class="testimonial-card__city">{{ $testimonial->city }}</span>
            @endif
        </span>

        <span class="testimonial-card__stars" aria-label="التقييم {{ $rating }} من 5">
            <span aria-hidden="true">{{ str_repeat('★', $rating) }}</span>
        </span>
    </div>

    <p class="testimonial-card__review">{{ $testimonial->review }}</p>
</article>
