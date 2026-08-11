@php
    $phone = setting('phone');
    $logo = setting_image('logo');
@endphp

<header class="site-header">
    <nav class="navbar navbar-expand-xl py-1">
        <div class="container">
            <a class="site-logo" href="{{ route('home') }}">
                @if ($logo)
                    {{-- The artwork already contains both the mark and the wordmark. --}}
                    <img src="{{ $logo }}" alt="{{ setting('company_name_ar', 'سدك للإستقدام') }}"
                         class="site-logo__img">
                @else
                    <span class="site-logo__mark">CIDIC</span>
                    <span>
                        <span class="site-logo__title d-block">{{ setting('company_name_en', 'CIDIC RECRUITMENT') }}</span>
                        <span class="site-logo__sub">{{ setting('company_name_ar', 'سدك للإستقدام') }}</span>
                    </span>
                @endif
            </a>

            <button class="navbar-toggler border-0" type="button" data-bs-toggle="collapse"
                    data-bs-target="#mainNav" aria-controls="mainNav" aria-expanded="false"
                    aria-label="فتح القائمة">
                <span class="navbar-toggler-icon"></span>
            </button>

            <div class="collapse navbar-collapse" id="mainNav">
                <ul class="navbar-nav site-nav mx-auto mb-3 mb-xl-0">
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('home')) active @endif" href="{{ route('home') }}">الرئيسية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('services.*')) active @endif" href="{{ route('services.index') }}">خدماتنا</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('nationalities.*')) active @endif" href="{{ route('nationalities.index') }}">الجنسيات</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('candidates.*')) active @endif" href="{{ route('candidates.index') }}">السير الذاتية</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#how-it-works">كيف نعمل</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#why-us">لماذا نحن</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link" href="{{ route('home') }}#testimonials">آراء العملاء</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('faq')) active @endif" href="{{ route('faq') }}">الأسئلة الشائعة</a>
                    </li>
                    <li class="nav-item">
                        <a class="nav-link @if(request()->routeIs('contact.*')) active @endif" href="{{ route('contact.create') }}">تواصل معنا</a>
                    </li>
                </ul>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if ($phone)
                        <a href="{{ tel_url($phone) }}" class="header-phone">
                            <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor" aria-hidden="true">
                                <path d="M6.62 10.79a15.05 15.05 0 006.59 6.59l2.2-2.2a1 1 0 011.02-.24 11.36 11.36 0 003.57.57 1 1 0 011 1V20a1 1 0 01-1 1A17 17 0 013 4a1 1 0 011-1h3.5a1 1 0 011 1c0 1.25.2 2.45.57 3.57a1 1 0 01-.25 1.02l-2.2 2.2z"/>
                            </svg>
                            <span dir="ltr">{{ $phone }}</span>
                        </a>
                    @endif

                    <a href="{{ route('recruitment-requests.create') }}" class="btn btn-primary btn-pill">اطلب الآن</a>
                </div>
            </div>
        </div>
    </nav>
</header>
