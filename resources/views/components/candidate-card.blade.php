@props(['candidate'])

@php
    $status = $candidate->availability_status;
@endphp

<article class="candidate-card">
    <div class="candidate-card__media">
        <a href="{{ route('candidates.show', $candidate) }}" aria-label="عرض سيرة {{ $candidate->name }}">
            {{-- The card shows the CV itself: page one of the PDF is rendered
                 into the canvas in the browser. Until it resolves (or if it
                 fails) the placeholder underneath stays visible. --}}
            <span class="cv-thumb" data-cv-thumb="{{ $candidate->cv_file_url }}">
                <canvas class="cv-thumb__canvas" aria-hidden="true"></canvas>

                <span class="cv-thumb__placeholder" aria-hidden="true">
                    <svg width="34" height="34" viewBox="0 0 24 24" fill="none">
                        <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"
                              stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                        <path d="M14 3v5h5" stroke="currentColor" stroke-width="1.7" stroke-linejoin="round"/>
                    </svg>
                    <span class="cv-thumb__placeholder-text">السيرة الذاتية</span>
                </span>
            </span>
        </a>

        <span class="candidate-card__badge bg-{{ $status->badge() }}-subtle text-{{ $status->badge() }}-emphasis">
            {{ $status->label() }}
        </span>

        <span class="candidate-card__ref" dir="ltr">{{ $candidate->reference_number }}</span>

        @if ($candidate->cv_file_url)
            <span class="candidate-card__cv" aria-hidden="true">
                <svg width="13" height="13" viewBox="0 0 24 24" fill="none">
                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"
                          stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                    <path d="M14 3v5h5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                </svg>
                <span>PDF</span>
            </span>
        @endif
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
