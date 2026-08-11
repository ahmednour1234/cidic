<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="robots" content="noindex, nofollow">
    <title>تسجيل الدخول — {{ setting('company_name_ar', 'سدك للإستقدام') }}</title>
    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/app.js'])
</head>
<body style="background: var(--surface);">
    <div class="container" style="min-height: 100vh; display: grid; place-items: center; padding: 2rem 1rem;">
        <div style="width: 100%; max-width: 420px;">
            <div class="text-center mb-4">
                <a href="{{ route('home') }}" class="site-logo justify-content-center flex-column gap-2">
                    @if ($loginLogo = setting_image('logo'))
                        <img src="{{ $loginLogo }}" alt="{{ setting('company_name_ar', 'سدك للإستقدام') }}"
                             style="height: 74px; width: auto; object-fit: contain;">
                    @else
                        <span class="site-logo__mark">CIDIC</span>
                        <span class="site-logo__title d-block">{{ setting('company_name_ar', 'سدك للإستقدام') }}</span>
                    @endif
                    <span class="site-logo__sub">لوحة التحكم</span>
                </a>
            </div>

            <div class="card">
                <div class="card-body p-4">
                    <h1 class="h5 mb-1">تسجيل الدخول</h1>
                    <p class="text-muted-soft small mb-4">أدخل بياناتك للوصول إلى لوحة التحكم.</p>

                    @if ($errors->any())
                        <div class="alert alert-danger" role="alert">
                            <ul class="mb-0 ps-3">
                                @foreach ($errors->all() as $error)
                                    <li>{{ $error }}</li>
                                @endforeach
                            </ul>
                        </div>
                    @endif

                    <form method="POST" action="{{ route('admin.login.store') }}" data-submit-guard>
                        @csrf

                        <div class="mb-3">
                            <label for="email" class="form-label">البريد الإلكتروني</label>
                            <input type="email" id="email" name="email" dir="ltr"
                                   class="form-control @error('email') is-invalid @enderror"
                                   value="{{ old('email') }}" required autofocus autocomplete="username">
                            @error('email')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="mb-3">
                            <label for="password" class="form-label">كلمة المرور</label>
                            <input type="password" id="password" name="password" dir="ltr"
                                   class="form-control @error('password') is-invalid @enderror"
                                   required autocomplete="current-password">
                            @error('password')
                                <div class="invalid-feedback">{{ $message }}</div>
                            @enderror
                        </div>

                        <div class="form-check mb-4">
                            <input type="checkbox" class="form-check-input" id="remember" name="remember" value="1">
                            <label class="form-check-label" for="remember">تذكرني</label>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">
                            دخول
                            <span class="btn-spinner" aria-hidden="true"></span>
                        </button>
                    </form>
                </div>
            </div>

            <p class="text-center mt-3">
                <a href="{{ route('home') }}" class="text-muted-soft">العودة إلى الموقع</a>
            </p>
        </div>
    </div>
</body>
</html>
