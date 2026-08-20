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

                {{-- ---------------- CV document ---------------- --}}
                <div class="col-lg-8">
                    <div class="d-flex flex-wrap align-items-center gap-3 mb-3">
                        <h2 class="mb-0">{{ $candidate->name }}</h2>
                        <span class="badge bg-primary-subtle text-primary-emphasis" dir="ltr">
                            {{ $candidate->reference_number }}
                        </span>
                    </div>

                    {{-- The PDF is the profile: every detail lives inside it, so
                         nothing here repeats what the document already states. --}}
                    <div class="cv-viewer">
                        <div class="cv-viewer__bar">
                            <span class="cv-viewer__label">
                                <svg width="16" height="16" viewBox="0 0 24 24" fill="none" aria-hidden="true">
                                    <path d="M14 3H7a2 2 0 0 0-2 2v14a2 2 0 0 0 2 2h10a2 2 0 0 0 2-2V8l-5-5Z"
                                          stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                    <path d="M14 3v5h5" stroke="currentColor" stroke-width="2" stroke-linejoin="round"/>
                                </svg>
                                السيرة الذاتية
                            </span>

                            <a href="{{ $candidate->cv_file_url }}" target="_blank" rel="noopener"
                               class="cv-viewer__open">فتح في نافذة جديدة</a>
                        </div>

                        <object data="{{ $candidate->cv_file_url }}#view=FitH"
                                type="application/pdf" class="cv-viewer__frame"
                                aria-label="السيرة الذاتية للمرشحة {{ $candidate->name }}">
                            <div class="cv-viewer__fallback">
                                <p class="mb-3">لا يمكن عرض الملف داخل المتصفح.</p>
                                <a href="{{ $candidate->cv_file_url }}" target="_blank" rel="noopener"
                                   class="btn btn-primary btn-pill">تحميل السيرة الذاتية (PDF)</a>
                            </div>
                        </object>
                    </div>

                    @if ($candidate->intro_video_url)
                        <h3 class="h5 mb-3 mt-4">الفيديو التعريفي</h3>
                        <video controls preload="metadata" class="w-100" style="border-radius: var(--radius); max-height: 420px;">
                            <source src="{{ $candidate->intro_video_url }}">
                            متصفحك لا يدعم تشغيل الفيديو.
                        </video>
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
