@props(['service'])

<article class="feature-card">
    <span class="feature-card__icon" aria-hidden="true">
        {!! $service->icon ? e($service->icon) : '&#9881;' !!}
    </span>

    <span>
        <span class="feature-card__title d-block">
            <a href="{{ route('services.show', $service) }}" class="text-reset">{{ $service->title }}</a>
        </span>
        <p class="feature-card__text">{{ $service->short_description }}</p>
    </span>
</article>
