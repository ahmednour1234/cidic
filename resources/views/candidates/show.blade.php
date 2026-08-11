@extends('layouts.app')

@section('meta_title', $candidate->meta_title ?: "{$candidate->name} - {$candidate->reference_number} | " . setting('company_name_ar'))
@section('meta_description', $candidate->meta_description ?: \Illuminate\Support\Str::limit(strip_tags((string) $candidate->description), 155))
@section('og_image', $candidate->profile_image_url)

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>{{ $candidate->name }}</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('candidates.index') }}">السير الذاتية</a></li>
                    <li class="breadcrumb-item active" aria-current="page" dir="ltr">{{ $candidate->reference_number }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4 g-lg-5">
                {{-- ---------------- Photo + actions ---------------- --}}
                <div class="col-lg-4">
                    <img src="{{ $candidate->profile_image_url }}"
                         alt="صورة {{ $candidate->name }}"
                         class="candidate-profile__image mb-3">

                    @php $status = $candidate->availability_status; @endphp
                    <div class="text-center mb-3">
                        <span class="badge-soft bg-{{ $status->badge() }}-subtle text-{{ $status->badge() }}-emphasis fs-6">
                            {{ $status->label() }}
                        </span>
                    </div>

                    <div class="d-grid gap-2">
                        @if ($candidate->isAvailable())
                            <a href="{{ route('candidate-requests.create', $candidate) }}" class="btn btn-primary btn-lg">
                                طلب هذه العاملة
                            </a>
                        @else
                            <button type="button" class="btn btn-secondary btn-lg" disabled>
                                غير متاحة حالياً
                            </button>
                            <p class="text-center text-muted-soft small mb-0">
                                هذه العاملة غير متاحة حالياً.
                            </p>
                        @endif

                        @if (setting('whatsapp'))
                            <a href="{{ whatsapp_url($whatsappMessage) }}" target="_blank" rel="noopener"
                               class="btn btn-outline-primary">
                                استفسار عبر واتساب
                            </a>
                        @endif

                        @if ($candidate->cv_file_url)
                            <a href="{{ $candidate->cv_file_url }}" target="_blank" rel="noopener"
                               class="btn btn-outline-secondary">
                                تحميل السيرة الذاتية (PDF)
                            </a>
                        @endif
                    </div>
                </div>

                {{-- ---------------- Details ---------------- --}}
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-4">
                        <h2 class="mb-0">{{ $candidate->name }}</h2>
                        <span class="badge bg-primary-subtle text-primary-emphasis" dir="ltr">
                            {{ $candidate->reference_number }}
                        </span>
                    </div>

                    <ul class="spec-list mb-4">
                        <li class="spec-list__item">
                            <span class="spec-list__label">الجنسية</span>
                            <span class="spec-list__value">{{ $candidate->nationality?->name_ar ?? '—' }}</span>
                        </li>
                        @if ($candidate->display_age)
                            <li class="spec-list__item">
                                <span class="spec-list__label">العمر</span>
                                <span class="spec-list__value">{{ $candidate->display_age }} سنة</span>
                            </li>
                        @endif
                        <li class="spec-list__item">
                            <span class="spec-list__label">المهنة</span>
                            <span class="spec-list__value">{{ $candidate->profession }}</span>
                        </li>
                        <li class="spec-list__item">
                            <span class="spec-list__label">سنوات الخبرة</span>
                            <span class="spec-list__value">{{ $candidate->years_of_experience }} سنوات</span>
                        </li>
                        <li class="spec-list__item">
                            <span class="spec-list__label">اللغات</span>
                            <span class="spec-list__value">{{ $candidate->languages_label }}</span>
                        </li>
                        <li class="spec-list__item">
                            <span class="spec-list__label">التصنيف</span>
                            <span class="spec-list__value">{{ $candidate->category?->name_ar ?? '—' }}</span>
                        </li>
                        @if ($candidate->religion)
                            <li class="spec-list__item">
                                <span class="spec-list__label">الديانة</span>
                                <span class="spec-list__value">{{ $candidate->religion }}</span>
                            </li>
                        @endif
                        @if ($candidate->marital_status)
                            <li class="spec-list__item">
                                <span class="spec-list__label">الحالة الاجتماعية</span>
                                <span class="spec-list__value">
                                    {{ ['single' => 'عزباء', 'married' => 'متزوجة', 'divorced' => 'مطلقة', 'widowed' => 'أرملة'][$candidate->marital_status] ?? $candidate->marital_status }}
                                </span>
                            </li>
                        @endif
                        @if ($candidate->education)
                            <li class="spec-list__item">
                                <span class="spec-list__label">المؤهل</span>
                                <span class="spec-list__value">{{ $candidate->education }}</span>
                            </li>
                        @endif
                        @if (! is_null($candidate->children_count))
                            <li class="spec-list__item">
                                <span class="spec-list__label">عدد الأبناء</span>
                                <span class="spec-list__value">{{ $candidate->children_count }}</span>
                            </li>
                        @endif
                        @if ($candidate->salary)
                            <li class="spec-list__item">
                                <span class="spec-list__label">الراتب</span>
                                <span class="spec-list__value">{{ number_format((float) $candidate->salary) }} ريال</span>
                            </li>
                        @endif
                    </ul>

                    @if (filled($candidate->skills))
                        <h3 class="h5 mb-3">المهارات</h3>
                        <div class="mb-4">
                            @foreach ((array) $candidate->skills as $skill)
                                <span class="skill-chip">{{ $skill }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if (filled($candidate->previous_countries))
                        <h3 class="h5 mb-3">دول الخبرة السابقة</h3>
                        <div class="mb-4">
                            @foreach ((array) $candidate->previous_countries as $country)
                                <span class="skill-chip">{{ $country }}</span>
                            @endforeach
                        </div>
                    @endif

                    @if ($candidate->description)
                        <h3 class="h5 mb-3">نبذة</h3>
                        <p style="line-height: 2; color: var(--muted);">{{ $candidate->description }}</p>
                    @endif

                    @if ($candidate->intro_video_url)
                        <h3 class="h5 mb-3 mt-4">الفيديو التعريفي</h3>
                        <video controls preload="metadata" class="w-100" style="border-radius: var(--radius); max-height: 420px;">
                            <source src="{{ $candidate->intro_video_url }}">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
                    @endif

                    @if ($candidate->cv_file_url)
                        <h3 class="h5 mb-3 mt-4">معاينة السيرة الذاتية</h3>
                        <object data="{{ $candidate->cv_file_url }}" type="application/pdf"
                                width="100%" height="520" style="border-radius: var(--radius); border: 1px solid var(--border);">
                            <p class="mb-0 p-3">
                                لا يمكن عرض الملف داخل المتصفح.
                                <a href="{{ $candidate->cv_file_url }}" target="_blank" rel="noopener">اضغط هنا للتحميل</a>.
                            </p>
                        </object>
                    @endif
                </div>
            </div>
        </div>
    </section>

    @if ($related->isNotEmpty())
        <section class="section section--surface">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-title">سير ذاتية مشابهة</h2>
                </div>

                <div class="row g-4">
                    @foreach ($related as $item)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-candidate-card :candidate="$item" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
