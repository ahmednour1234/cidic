@extends('layouts.app')

@section('meta_title', setting('meta_title', 'سدك للإستقدام | حلول موثوقة لاستقدام العمالة المنزلية'))
@section('meta_description', setting('meta_description'))

@section('content')

    {{-- ============================ HERO ============================ --}}
    @php
        // Headline is stored as one string; the first line is plain and every
        // line after it is highlighted, matching the reference design.
        $heroTitle = setting('hero_title');
        $heroLines = array_values(array_filter(array_map(
            'trim',
            preg_split('/\r\n|\r|\n/', (string) $heroTitle)
        ), 'strlen'));

        if (empty($heroLines)) {
            $heroLines = ['حلول موثوقة', 'لاستقدام العمالة المنزلية', 'في السعودية'];
        }

        // Prefer a hand-dropped site/hero-main.* over the configured settings,
        // so replacing the hero art needs no admin step: drop the file in
        // storage/app/public/site/ and it is picked up on the next request.
        $heroPhoto = null;

        foreach (['webp', 'jpg', 'jpeg', 'png'] as $ext) {
            // Ask the disk rather than is_file(): shared hosts with open_basedir
            // report false for a file that exists and serves fine.
            if (Illuminate\Support\Facades\Storage::disk('public')->exists("site/hero-main.{$ext}")) {
                // Root-relative, matching setting_image(), so the URL is not
                // pinned to whichever host rendered the page.
                $heroPhoto = storage_url("site/hero-main.{$ext}");
                break;
            }
        }

        $heroPhoto = $heroPhoto
            ?: setting_image('hero_background_image')
            ?: setting_image('hero_image');

        // Decorative backdrop behind the whole hero band. Same drop-in rule as
        // the photo: site/hero-bg-art.* wins, otherwise the CSS-drawn shapes.
        $heroArt = null;

        foreach (['webp', 'png', 'jpg', 'jpeg', 'svg'] as $ext) {
            if (Illuminate\Support\Facades\Storage::disk('public')->exists("site/hero-bg-art.{$ext}")) {
                $heroArt = storage_url("site/hero-bg-art.{$ext}");
                break;
            }
        }
    @endphp

    <section class="hero-v2 {{ $heroArt ? 'hero-v2--art' : '' }}">
        @if ($heroArt)
            {{-- Supplied artwork replaces the CSS-drawn blob, dots and arc. --}}
            <img src="{{ $heroArt }}" alt="" aria-hidden="true" decoding="async"
                 class="hero-v2__art">
        @else
            <span class="hero-v2__blob" aria-hidden="true"></span>
            <span class="hero-v2__dots" aria-hidden="true"></span>
        @endif

        <div class="container">
            <div class="row align-items-center g-4 g-lg-5">
                <div class="{{ $heroArt ? 'col-lg-6 offset-lg-1 hero-v2__copy' : 'col-lg-6' }} order-lg-2">
                    <h1 class="hero-v2__title">
                        {{ array_shift($heroLines) }}
                        @foreach ($heroLines as $line)
                            <span class="d-block">{{ $line }}</span>
                        @endforeach
                    </h1>

                    <p class="hero-v2__text">
                        {{ setting('hero_subtitle', 'نوفر خدمات الاستقدام، الإيجار الشهري، ونقل الخدمات باحترافية وسرعة وفق الأنظمة المعتمدة.') }}
                    </p>

                    <div class="hero-v2__actions">
                        <a href="{{ route('recruitment-requests.create') }}" class="btn btn-primary btn-lg btn-pill hero-v2__cta">
                            <span>اطلب الآن</span>
                            <span class="hero-v2__cta-icon" aria-hidden="true">&#8592;</span>
                        </a>
                        <a href="{{ route('contact.create') }}" class="btn btn-lg btn-pill hero-v2__ghost">
                            <span>تواصل معنا</span>
                            <span class="hero-v2__ghost-icon" aria-hidden="true">&#9993;</span>
                        </a>
                    </div>
                </div>

                @unless ($heroArt)
                    {{-- Without supplied artwork the hero draws its own arc-framed
                         photo; the artwork already contains the subject. --}}
                    <div class="col-lg-6 order-lg-1">
                        <div class="hero-v2__media">
                            <span class="hero-v2__arc" aria-hidden="true"></span>
                            @if ($heroPhoto)
                                <img src="{{ $heroPhoto }}"
                                     alt="عاملة منزلية مدربة أثناء العمل"
                                     class="hero-v2__photo">
                            @else
                                <div class="hero-v2__photo hero-v2__photo--fallback">
                                    <span>CIDIC</span>
                                </div>
                            @endif
                        </div>
                    </div>
                @endunless
            </div>
        </div>
    </section>

    {{-- ---- Feature cards overlapping the hero ---- --}}
    <div class="container">
        <div class="hero-features">
            @php
                $heroFeatures = [
                    ['icon' => '&#128737;', 'title' => 'التزام بالأنظمة', 'text' => 'وفق أنظمة وزارة الموارد البشرية والتنمية الاجتماعية'],
                    ['icon' => '&#128101;', 'title' => 'عمالة مدربة', 'text' => 'نختار أفضل الكفاءات بعد تدريب وتأهيل'],
                    ['icon' => '&#9201;', 'title' => 'سرعة في الإنجاز', 'text' => 'إجراءات سريعة ومتابعة دقيقة حتى الوصول'],
                    ['icon' => '&#127911;', 'title' => 'دعم متواصل', 'text' => 'فريق دعم جاهز للإجابة على استفساراتك'],
                ];
            @endphp

            @foreach ($heroFeatures as $feature)
                <div class="hero-feature" data-reveal data-reveal-delay="{{ $loop->index * 90 }}">
                    <span class="hero-feature__icon" aria-hidden="true">{!! $feature['icon'] !!}</span>
                    <span class="hero-feature__body">
                        <span class="hero-feature__title">{{ $feature['title'] }}</span>
                        <span class="hero-feature__text">{{ $feature['text'] }}</span>
                    </span>
                </div>
            @endforeach
        </div>
    </div>

    {{-- ============================ SERVICES ============================ --}}
    @if ($services->isNotEmpty())
        <section class="section" id="services">
            <div class="container">
                <div class="section-heading" data-reveal>
                    <h2 class="section-title">خدماتنا</h2>
                    <a href="{{ route('services.index') }}" class="btn btn-link px-0">عرض الكل</a>
                </div>

                <div class="row g-3">
                    @foreach ($services as $service)
                        <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="{{ $loop->index * 90 }}">
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
                <div class="section-heading" data-reveal>
                    <h2 class="section-title">الجنسيات المتاحة</h2>
                </div>

                <div class="row g-3">
                    @foreach ($nationalities as $nationality)
                        <div class="col-6 col-md-4 col-lg-3 col-xl" data-reveal="zoom" data-reveal-delay="{{ $loop->index * 60 }}">
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
            <div class="section-heading" data-reveal>
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
                        <div class="col-12 col-sm-6 col-lg-3" data-reveal data-reveal-delay="{{ $loop->index * 80 }}">
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
                <div class="section-heading" data-reveal>
                    <h2 class="section-title">كيف نعمل</h2>
                </div>

                <div class="row g-3">
                    @foreach ($howItWorks as $index => $step)
                        <div class="col-sm-6 col-lg-3" data-reveal data-reveal-delay="{{ $index * 100 }}">
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
    {{-- ==================== PARTNERSHIP (split) ==================== --}}
    <section class="section" id="partnership">
        <div class="container">
            <x-split-feature
                image="{{ setting_image('about_image') ?: storage_url('site/about.webp') }}"
                alt="فريق سدك للإستقدام أثناء العمل"
                eyebrow="لماذا سدك"
                title="شريك موثوق في الاستقدام من أول خطوة حتى الوصول"
                text="نتولى عنك كل التفاصيل: اختيار الكفاءات، إنهاء الإجراءات النظامية، والمتابعة حتى استلام العمالة في منزلك بكل يسر."
                :items="[
                    'ترخيص رسمي ومزاولة وفق أنظمة وزارة الموارد البشرية',
                    'عقود واضحة بلا رسوم مفاجئة',
                    'متابعة ما بعد الوصول وضمان الاستبدال',
                ]"
                badge-num="+٥٠٠٠"
                badge-label="عملية استقدام ناجحة"
                cta-label="تعرف علينا"
                cta-url="{{ route('about') }}" />
        </div>
    </section>

        <section class="section" id="why-us">
            <div class="container">
                <div class="section-heading" data-reveal>
                    <h2 class="section-title">لماذا نحن</h2>
                </div>

                <div class="row g-3">
                    @foreach ($whyChooseUs as $item)
                        <div class="col-sm-6 col-lg-3" data-reveal data-reveal-delay="{{ $loop->index * 100 }}">
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
                <div class="section-heading" data-reveal>
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
                <div class="section-heading" data-reveal>
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

@push('scripts')
    @vite('resources/js/cv-thumbs.js')
@endpush
