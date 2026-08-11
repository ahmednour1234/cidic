@props(['candidate'])

@php
    $status = $candidate->availability_status;
@endphp

<article class="candidate-card">
    <div class="candidate-card__media">
        <a href="{{ route('candidates.show', $candidate) }}" aria-label="عرض سيرة {{ $candidate->name }}">
            <img src="{{ $candidate->profile_image_url }}"
                 alt="صورة {{ $candidate->name }}"
                 class="candidate-card__image"
                 loading="lazy">
        </a>

        <span class="candidate-card__badge bg-{{ $status->badge() }}-subtle text-{{ $status->badge() }}-emphasis">
            {{ $status->label() }}
        </span>

        <span class="candidate-card__ref" dir="ltr">{{ $candidate->reference_number }}</span>
    </div>

    <div class="candidate-card__body">
        <h3 class="candidate-card__name">
            <a href="{{ route('candidates.show', $candidate) }}" class="text-reset stretched-link-none">
                {{ $candidate->name }}
            </a>
        </h3>

        <ul class="candidate-card__meta">
            <li>
                @if ($candidate->nationality?->flag_url)
                    <img src="{{ $candidate->nationality->flag_url }}" alt="" class="candidate-card__flag" loading="lazy">
                @endif
                <span>الجنسية: <strong>{{ $candidate->nationality?->name_ar ?? '—' }}</strong></span>
            </li>
            @if ($candidate->display_age)
                <li><span>العمر: <strong>{{ $candidate->display_age }} سنة</strong></span></li>
            @endif
            <li><span>الخبرة: <strong>{{ $candidate->years_of_experience }} سنوات</strong></span></li>
            <li><span>المهنة: <strong>{{ $candidate->profession }}</strong></span></li>
            <li><span>اللغة: <strong>{{ $candidate->languages_label }}</strong></span></li>
        </ul>

        <div class="candidate-card__actions">
            <a href="{{ route('candidates.show', $candidate) }}" class="btn btn-outline-primary btn-sm">
                عرض السيرة
            </a>

            @if ($candidate->isAvailable())
                <a href="{{ route('candidate-requests.create', $candidate) }}" class="btn btn-primary btn-sm">
                    تقديم طلب
                </a>
            @else
                <button type="button" class="btn btn-secondary btn-sm" disabled
                        title="هذه العاملة غير متاحة حالياً">
                    غير متاحة
                </button>
            @endif
        </div>
    </div>
</article>
