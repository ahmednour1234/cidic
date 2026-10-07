<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تسجيل الدخول — {{ __('cv-panel.title') }}</title>
    @vite(['resources/css/app.css', 'resources/css/cv-panel.css', 'resources/js/app.js'])
</head>
<body>

<div class="cvp-auth">

    {{-- Brand side. Hidden under lg, where it would push the form off-screen.
         The photograph is the site's own hero image, washed with brand blue. --}}
    @php
        $logo = setting_image('logo');
        // Portrait crop (1500x2250), which suits this tall column; the
        // landscape hero would be cropped to a sliver of itself.
        $heroImage = asset('storage/site/hero-worker-scene.webp');
    @endphp

    <aside class="cvp-auth-brand" style="background-image:url('{{ $heroImage }}')">
        @if ($logo)
            <div class="cvp-auth-logo">
                <img src="{{ $logo }}" alt="{{ setting('company_name_ar', 'سدك للإستقدام') }}">
            </div>
        @else
            <div class="cvp-auth-mark">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                     stroke-linecap="round" stroke-linejoin="round" width="26" height="26">
                    <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                    <path d="M14 2v6h6"/><path d="M8 13h8"/><path d="M8 17h5"/>
                </svg>
            </div>
        @endif

        <div class="cvp-auth-copy">
            <h1 class="cvp-auth-title">{{ __('cv-panel.title') }}</h1>
            <p class="cvp-auth-sub">
                إدارة السير الذاتية للعاملات المنزليات — من الرفع حتى إنشاء العقد،
                في مكان واحد.
            </p>

            <ul class="cvp-auth-points">
                @foreach ([
                    'رفع السير الذاتية وتنظيمها حسب الجنسية',
                    'حجز السير للعملاء ومتابعتها حتى التعيين',
                    'صفحات عامة لكل جنسية تشاركها مع عملائك',
                ] as $point)
                    <li>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="3"
                             stroke-linecap="round" stroke-linejoin="round">
                            <path d="m5 13 4 4L19 7"/>
                        </svg>
                        {{ $point }}
                    </li>
                @endforeach
            </ul>
        </div>

        <p class="cvp-auth-foot mb-0">
            {{ setting('company_name_ar', 'سدك للإستقدام') }} © {{ date('Y') }}
        </p>
    </aside>

    {{-- Form side --}}
    <main class="cvp-auth-form">
        <div class="cvp-auth-box">

            <div class="cvp-auth-chip">
                @if ($logo)
                    <img src="{{ $logo }}" alt="" style="height:2rem;width:auto">
                @else
                    <span>
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                             stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                        </svg>
                    </span>
                @endif
                {{ __('cv-panel.title') }}
            </div>

            <h2>تسجيل الدخول</h2>
            <p class="lead-sm">أدخل بياناتك للوصول إلى لوحة السير الذاتية.</p>

            @if ($errors->any())
                <div class="alert alert-danger border-0 d-flex gap-2 align-items-start"
                     style="border-radius:.6rem;background:#fef2f2;color:#991b1b;font-size:.86rem">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"
                         stroke-linecap="round" width="18" height="18" style="flex:0 0 auto;margin-top:1px">
                        <circle cx="12" cy="12" r="10"/><path d="M12 8v4"/><path d="M12 16h.01"/>
                    </svg>
                    <div>
                        @foreach ($errors->all() as $error)
                            <div>{{ $error }}</div>
                        @endforeach
                    </div>
                </div>
            @endif

            <form method="POST" action="{{ route('cv-panel.login.store') }}"
                  x-data="{ show: false, sending: false }" @submit="sending = true">
                @csrf

                <div class="mb-3">
                    <label for="email" class="cvp-label">البريد الإلكتروني</label>
                    <div class="cvp-field">
                        <svg class="cvp-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="2" y="4" width="20" height="16" rx="2"/>
                            <path d="m22 7-10 6L2 7"/>
                        </svg>
                        <input type="email" id="email" name="email" value="{{ old('email') }}"
                               class="cvp-input cvp-ltr" dir="ltr" required autofocus
                               autocomplete="username" placeholder="name@example.com">
                    </div>
                </div>

                <div class="mb-3">
                    <label for="password" class="cvp-label">كلمة المرور</label>
                    <div class="cvp-field">
                        <svg class="cvp-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                             stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                            <rect x="3" y="11" width="18" height="11" rx="2"/>
                            <path d="M7 11V7a5 5 0 0 1 10 0v4"/>
                        </svg>

                        <input :type="show ? 'text' : 'password'" id="password" name="password"
                               class="cvp-input cvp-ltr" dir="ltr" required
                               autocomplete="current-password" placeholder="••••••••">

                        <button type="button" class="cvp-field-action" @click="show = !show"
                                :aria-label="show ? 'إخفاء كلمة المرور' : 'إظهار كلمة المرور'">
                            <svg x-show="! show" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                 width="17" height="17">
                                <path d="M2 12s3.5-7 10-7 10 7 10 7-3.5 7-10 7-10-7-10-7"/>
                                <circle cx="12" cy="12" r="3"/>
                            </svg>
                            <svg x-show="show" x-cloak viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round"
                                 width="17" height="17">
                                <path d="M9.9 4.24A9.1 9.1 0 0 1 12 4c6.5 0 10 7 10 7a18.5 18.5 0 0 1-2.16 3.19"/>
                                <path d="M6.61 6.61A18.5 18.5 0 0 0 2 11s3.5 7 10 7a9 9 0 0 0 5.39-1.61"/>
                                <path d="m2 2 20 20"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div class="form-check mb-4">
                    <input type="checkbox" id="remember" name="remember" value="1" class="form-check-input">
                    <label for="remember" class="form-check-label" style="font-size:.86rem">تذكّرني</label>
                </div>

                <button type="submit" class="cvp-btn cvp-btn-gold cvp-btn-lg" :disabled="sending">
                    <span x-show="sending" x-cloak class="spinner-border spinner-border-sm"></span>
                    <span x-text="sending ? 'جارٍ الدخول…' : 'تسجيل الدخول'">تسجيل الدخول</span>
                </button>
            </form>
        </div>
    </main>
</div>

</body>
</html>
