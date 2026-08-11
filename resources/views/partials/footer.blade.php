@php
    $socials = array_filter([
        'فيسبوك' => setting('facebook'),
        'انستقرام' => setting('instagram'),
        'إكس' => setting('twitter'),
        'تيك توك' => setting('tiktok'),
        'سناب شات' => setting('snapchat'),
    ]);

    $logo = setting_image('logo');
@endphp

<footer class="site-footer">
    <div class="container">
        <div class="row g-4">
            {{-- About --}}
            <div class="col-lg-4">
                <span class="site-footer__logo">
                    @if ($logo)
                        <img src="{{ $logo }}" alt="{{ setting('company_name_ar', 'سدك للإستقدام') }}"
                             class="site-footer__logo-img">
                    @else
                        <span class="site-logo__mark" style="width: 34px; height: 34px; font-size: 0.6rem;">CIDIC</span>
                        <span>
                            <span class="site-logo__title d-block" style="font-size: 0.85rem;">
                                {{ setting('company_name_en', 'CIDIC RECRUITMENT') }}
                            </span>
                            <span class="site-logo__sub">{{ setting('company_name_ar', 'سدك للإستقدام') }}</span>
                        </span>
                    @endif
                </span>

                <h5>عن سدك للإستقدام</h5>
                <p style="line-height: 1.95;">
                    {{ setting('company_description', 'نوفر خدمات الاستقدام، الإيجار الشهري، ونقل الخدمات باحترافية وسرعة وفق الأنظمة المعتمدة في المملكة العربية السعودية.') }}
                </p>

                @if ($license = setting('license_number'))
                    <span class="license-badge">
                        <span aria-hidden="true">&#128737;</span>
                        رقم الترخيص: <span dir="ltr">{{ $license }}</span>
                    </span>
                @endif
            </div>

            {{-- Quick links --}}
            <div class="col-6 col-lg-2">
                <h5>روابط سريعة</h5>
                <ul class="site-footer__list">
                    <li><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li><a href="{{ route('services.index') }}">خدماتنا</a></li>
                    <li><a href="{{ route('nationalities.index') }}">الجنسيات</a></li>
                    <li><a href="{{ route('candidates.index') }}">السير الذاتية</a></li>
                    <li><a href="{{ route('about') }}">من نحن</a></li>
                </ul>
            </div>

            {{-- Support --}}
            <div class="col-6 col-lg-2">
                <h5>الدعم</h5>
                <ul class="site-footer__list">
                    <li><a href="{{ route('faq') }}">الأسئلة الشائعة</a></li>
                    <li><a href="{{ route('contact.create') }}">تواصل معنا</a></li>
                    <li><a href="{{ route('recruitment-requests.create') }}">اطلب الآن</a></li>
                    <li><a href="{{ route('privacy-policy') }}">سياسة الخصوصية</a></li>
                    <li><a href="{{ route('terms') }}">الشروط والأحكام</a></li>
                </ul>
            </div>

            {{-- Contact --}}
            <div class="col-lg-4">
                <h5>تواصل معنا</h5>
                <ul class="site-footer__list">
                    @if ($phone = setting('phone'))
                        <li>
                            <span aria-hidden="true">&#9742;</span>
                            <a href="{{ tel_url($phone) }}" dir="ltr">{{ $phone }}</a>
                        </li>
                    @endif
                    @if ($wa = setting('whatsapp'))
                        <li>
                            <span aria-hidden="true">&#128241;</span>
                            <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" dir="ltr">{{ $wa }}</a>
                        </li>
                    @endif
                    @if ($email = setting('email'))
                        <li>
                            <span aria-hidden="true">&#9993;</span>
                            <a href="mailto:{{ $email }}" dir="ltr">{{ $email }}</a>
                        </li>
                    @endif
                    @if ($address = setting('address'))
                        <li>
                            <span aria-hidden="true">&#128205;</span>
                            {{ $address }}
                        </li>
                    @endif
                </ul>

                @if ($socials)
                    <div class="d-flex gap-2 mt-3">
                        @foreach ($socials as $label => $url)
                            <a href="{{ $url }}" class="social-link" target="_blank" rel="noopener"
                               title="{{ $label }}" aria-label="{{ $label }}">
                                {{ mb_substr($label, 0, 1) }}
                            </a>
                        @endforeach
                    </div>
                @endif
            </div>
        </div>

        <div class="site-footer__bottom d-flex flex-wrap justify-content-between gap-2">
            <span>{{ setting('footer_text', '© ' . date('Y') . ' ' . setting('company_name_ar', 'سدك للإستقدام') . '. جميع الحقوق محفوظة.') }}</span>
            <span>
                <a href="{{ route('privacy-policy') }}">سياسة الخصوصية</a>
                <span class="mx-2">|</span>
                <a href="{{ route('terms') }}">الشروط والأحكام</a>
            </span>
        </div>
    </div>
</footer>
