@extends('layouts.app')

@section('meta_title', setting('meta_title', 'سدك للإستقدام | حلول موثوقة لاستقدام العمالة المنزلية'))
@section('meta_description', setting('meta_description'))

@section('content')

    {{-- ============================ HERO ============================ --}}
    <section class="hero">
        <div class="container">
            <div class="row align-items-center g-4 g-lg-5 hero__inner">
                <div class="col-lg-6">
                    @php
                        // The headline is stored as one string; the design highlights the
                        // second line, so split on the newline the admin enters (if any).
                        $heroTitle = setting('hero_title', "حلول موثوقة لاستقدام\nالعمالة المنزلية في السعودية");
                        $heroLines = preg_split('/\r\n|\r|\n/', trim($heroTitle), 2);
                    @endphp

                    <h1 class="hero__title">
                        {{ $heroLines[0] }}
                        @if (! empty($heroLines[1]))
                            <span class="d-block">{{ $heroLines[1] }}</span>
                        @endif
                    </h1>

                    <p class="hero__text">
                        {{ setting('hero_subtitle', 'نوفر خدمات الاستقدام، الإيجار الشهري، ونقل الخدمات باحترافية وسرعة وفق الأنظمة المعتمدة.') }}
                    </p>

                    <div class="hero__actions">
                        <a href="{{ route('recruitment-requests.create') }}" class="btn btn-primary btn-lg btn-pill">
                            اطلب الآن
                        </a>
                        <a href="{{ route('contact.create') }}" class="btn btn-outline-light btn-lg btn-pill">
                            تواصل معنا
                        </a>
                    </div>
                </div>

                <div class="col-lg-6">
                    @php
                        // Composite hero: a background scene plus a cut-out subject.
                        // Falls back to a single image, then to the brand panel.
                        $heroScene = setting_image('hero_scene_image');
                        $heroSubject = setting_image('hero_subject_image');
                        $heroImage = setting_image('hero_image');
                    @endphp

                    <div class="hero__media">
                        @if ($heroScene || $heroSubject)
                            @if ($heroScene)
                                <img src="{{ $heroScene }}" alt="" aria-hidden="true" class="hero__media-scene">
                            @endif

                            @if ($heroSubject)
                                <img src="{{ $heroSubject }}"
                                     alt="عاملة منزلية مدربة"
                                     class="hero__media-subject">
                            @endif
                        @elseif ($heroImage)
                            <img src="{{ $heroImage }}" alt="خدمات استقدام العمالة المنزلية" class="hero__image">
                        @else
                            <div class="hero__media-fallback">
                                <div>
                                    <div style="font-size: 2.6rem; font-weight: 800; letter-spacing: 0.04em;">CIDIC</div>
                                    <p class="mb-0 mt-2 opacity-75">{{ setting('company_name_ar', 'سدك للإستقدام') }}</p>
                                </div>
                            </div>
                        @endif

                        <span class="hero__badge">
                            @if ($badgeLogo = setting_image('logo'))
                                <img src="{{ $badgeLogo }}"
                                     alt="{{ setting('company_name_ar', 'سدك للإستقدام') }}"
                                     class="hero__badge-img">
                            @else
                                <span class="hero__badge-mark">CIDIC</span>
                                <span class="hero__badge-text">
                                    {{ setting('company_name_en', 'CIDIC RECRUITMENT') }}
                                    <span class="d-block" style="font-weight: 600; color: var(--muted);">
                                        {{ setting('company_name_ar', 'سدك للإستقدام') }}
                                    </span>
                                </span>
                            @endif
                        </span>
                    </div>
                </div>
            </div>
        </div>
    </section>

    {{-- ---- Trust bar overlapping the hero ---- --}}
    <div class="container">
        <div class="trust-bar">
            @foreach (['الالتزام بالأنظمة', 'عمالة مدربة', 'سرعة في الإنجاز'] as $point)
                <span class="trust-point">
                    <span class="trust-point__icon" aria-hidden="true">&#10003;</span>
                    {{ $point }}
                </span>
            @endforeach
        </div>
    </div>

    {{-- ============================ SERVICES ============================ --}}
    @if ($services->isNotEmpty())
        <section class="section" id="services">
            <div class="container">
                <div class="section-heading">
                    <h2 class="section-title">خدماتنا</h2>
                    <a href="{{ route('services.index') }}" class="btn btn-link px-0">عرض الكل</a>
                </div>

                <div class="row g-3">
                    @foreach ($services as $service)
                        <div class="col-md-6 col-lg-4">
                            <x-service-card :service="$service" />
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ NATIONALITIES ============================ --}}
    @if ($nationalities->isNotEmpty())
        <section class="section section--surface" id="nationalities">
            <div class="container">
                <div class="section-heading">
                    <h2 class="section-title">الجنسيات المتاحة</h2>
                </div>

                <div class="row g-3">
                    @foreach ($nationalities as $nationality)
                        <div class="col-6 col-md-4 col-lg-3 col-xl">
                            <x-nationality-card :nationality="$nationality" />
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('nationalities.index') }}" class="btn btn-outline-primary btn-pill">
                        عرض جميع الجنسيات
                    </a>
                </div>
            </div>
        </section>
    @endif

    {{-- ==================== FEATURED CVs ==================== --}}
    <section class="section" id="candidates">
        <div class="container">
            <div class="section-heading">
                <div>
                    <h2 class="section-title">السير الذاتية المتاحة</h2>
                    <p class="section-subtitle">
                        تصفح السير الذاتية المتوفرة واختر العاملة المناسبة لاحتياجاتك وقدم طلبك مباشرة.
                    </p>
                </div>
            </div>

            @if ($candidates->isNotEmpty())
                <div class="row g-3">
                    @foreach ($candidates as $candidate)
                        <div class="col-12 col-sm-6 col-lg-3">
                            <x-candidate-card :candidate="$candidate" />
                        </div>
                    @endforeach
                </div>

                <div class="text-center mt-4">
                    <a href="{{ route('candidates.index') }}" class="btn btn-primary btn-pill">
                        عرض جميع السير الذاتية
                    </a>
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state__icon" aria-hidden="true">&#9776;</div>
                    <p class="mb-0">لا توجد سير ذاتية متاحة حالياً. يرجى التواصل معنا لمعرفة التوفر.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ============================ HOW IT WORKS ============================ --}}
    @if ($howItWorks->isNotEmpty())
        <section class="section section--surface" id="how-it-works">
            <div class="container">
                <div class="section-heading">
                    <h2 class="section-title">كيف نعمل</h2>
                </div>

                <div class="row g-3">
                    @foreach ($howItWorks as $index => $step)
                        <div class="col-sm-6 col-lg-3">
                            <div class="feature-card">
                                <span class="feature-card__icon-wrap">
                                    <span class="feature-card__icon" aria-hidden="true">
                                        {!! $step->icon ? e($step->icon) : '&#9679;' !!}
                                    </span>
                                    <span class="feature-card__step">{{ $index + 1 }}</span>
                                </span>
                                <span>
                                    <span class="feature-card__title d-block">{{ $step->title }}</span>
                                    <p class="feature-card__text">{{ $step->description }}</p>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ WHY CHOOSE US ============================ --}}
    @if ($whyChooseUs->isNotEmpty())
        <section class="section" id="why-us">
            <div class="container">
                <div class="section-heading">
                    <h2 class="section-title">لماذا نحن</h2>
                </div>

                <div class="row g-3">
                    @foreach ($whyChooseUs as $item)
                        <div class="col-sm-6 col-lg-3">
                            <div class="feature-card">
                                <span class="feature-card__icon" aria-hidden="true">
                                    {!! $item->icon ? e($item->icon) : '&#10003;' !!}
                                </span>
                                <span>
                                    <span class="feature-card__title d-block">{{ $item->title }}</span>
                                    <p class="feature-card__text">{{ $item->description }}</p>
                                </span>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ TESTIMONIALS ============================ --}}
    @if ($testimonials->isNotEmpty())
        <section class="section section--surface" id="testimonials">
            <div class="container">
                <div class="section-heading">
                    <h2 class="section-title">آراء العملاء</h2>

                    @if ($testimonials->count() > 3)
                        <div class="carousel-nav">
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide="prev"
                                    aria-label="السابق">&#8250;</button>
                            <button type="button" data-bs-target="#testimonialCarousel" data-bs-slide="next"
                                    aria-label="التالي">&#8249;</button>
                        </div>
                    @endif
                </div>

                <div id="testimonialCarousel" class="carousel slide" data-bs-ride="carousel">
                    <div class="carousel-inner pb-4">
                        @foreach ($testimonials->chunk(3) as $chunkIndex => $chunk)
                            <div class="carousel-item @if($chunkIndex === 0) active @endif">
                                <div class="row g-3">
                                    @foreach ($chunk as $testimonial)
                                        <div class="col-md-4">
                                            <x-testimonial-card :testimonial="$testimonial" />
                                        </div>
                                    @endforeach
                                </div>
                            </div>
                        @endforeach
                    </div>

                    @if ($testimonials->chunk(3)->count() > 1)
                        <div class="carousel-indicators position-static mt-2">
                            @foreach ($testimonials->chunk(3) as $chunkIndex => $chunk)
                                <button type="button" data-bs-target="#testimonialCarousel"
                                        data-bs-slide-to="{{ $chunkIndex }}"
                                        class="@if($chunkIndex === 0) active @endif"
                                        style="background-color: var(--primary); border-radius: 50%; width: 8px; height: 8px;"
                                        aria-label="الشريحة {{ $chunkIndex + 1 }}"></button>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </section>
    @endif

    {{-- ============================ FAQ + SUPPORT CTA ============================ --}}
    @if ($faqs->isNotEmpty())
        <section class="section" id="faq">
            <div class="container">
                <div class="section-heading">
                    <h2 class="section-title">الأسئلة الشائعة</h2>
                </div>

                <div class="row g-4 align-items-stretch">
                    <div class="col-lg-4">
                        <div class="faq-cta">
                            <span class="faq-cta__icon" aria-hidden="true">&#9990;</span>
                            <h3>لديك سؤال آخر؟</h3>
                            <p>
                                فريقنا جاهز لمساعدتك والرد على جميع استفساراتك حول خدمات الاستقدام.
                            </p>
                            <a href="{{ route('contact.create') }}" class="btn btn-light btn-pill align-self-start">
                                تواصل معنا
                            </a>
                        </div>
                    </div>

                    <div class="col-lg-8">
                        <div class="accordion" id="faqAccordion">
                            @foreach ($faqs as $index => $faq)
                                <x-faq-item :faq="$faq" :index="$index" parent="faqAccordion" />
                            @endforeach
                        </div>

                        <div class="mt-3">
                            <a href="{{ route('faq') }}" class="btn btn-link px-0">عرض جميع الأسئلة</a>
                        </div>
                    </div>
                </div>
            </div>
        </section>
    @endif
@endsection
