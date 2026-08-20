@extends('layouts.app')

@section('meta_title', 'خدماتنا | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'تعرف على خدمات الاستقدام والإيجار الشهري ونقل الخدمات التي نقدمها.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>خدماتنا</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">خدماتنا</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($services->isNotEmpty())
                <div class="row g-4">
                    @foreach ($services as $service)
                        <div class="col-md-6 col-lg-4" data-reveal data-reveal-delay="{{ $loop->index * 90 }}">
                            <x-service-card :service="$service" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state__icon" aria-hidden="true">&#9881;</div>
                    <p class="mb-0">لا توجد خدمات متاحة حالياً.</p>
                </div>
            @endif
        </div>
    </section>

    {{-- ==================== HOW WE DELIVER (split) ==================== --}}
    <section class="section section--surface">
        <div class="container">
            <x-split-feature
                image="{{ storage_url('site/service-recruitment.jpg') }}"
                alt="إجراءات الاستقدام"
                eyebrow="آلية العمل"
                title="إجراءات واضحة ومتابعة دقيقة حتى الوصول"
                text="نبدأ بفهم احتياجك ثم نرشح الكفاءة المناسبة، ونتولى التوثيق والتأشيرات والسفر مع إطلاعك على كل مرحلة أولاً بأول."
                :items="[
                    'ترشيح سير ذاتية مطابقة لمتطلباتك',
                    'إنهاء العقود والتأشيرات نظامياً',
                    'تنسيق السفر والاستقبال حتى منزلك',
                ]"
                cta-label="اطلب الخدمة"
                cta-url="{{ route('recruitment-requests.create') }}" />
        </div>
    </section>

    {{-- ==================== QUALITY (split, reversed) ==================== --}}
    <section class="section">
        <div class="container">
            <x-split-feature
                reverse
                image="{{ storage_url('site/service-rental.jpg') }}"
                alt="عمالة منزلية مدربة"
                eyebrow="الجودة"
                title="كفاءات مدربة تُختار بعناية قبل ترشيحها لك"
                text="نتحقق من الخبرة والسجل الصحي والسلوكي لكل مرشحة، مع تدريب على مهارات العناية بالمنزل والتعامل الأسري."
                :items="[
                    'فحص طبي وسجل جنائي معتمد',
                    'تدريب على مهام المنزل ورعاية الأطفال',
                    'ضمان الاستبدال خلال فترة التجربة',
                ]"
                badge-num="٪٩٨"
                badge-label="رضا العملاء"
                cta-label="تصفح السير الذاتية"
                cta-url="{{ route('candidates.index') }}" />
        </div>
    </section>
@endsection
