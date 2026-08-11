<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="robots" content="noindex, nofollow">
    <title>@yield('title', 'لوحة التحكم') — {{ setting('company_name_ar', 'سدك للإستقدام') }}</title>

    @if ($favicon = setting_image('favicon'))
        <link rel="icon" href="{{ $favicon }}">
    @endif

    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/css/admin.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body class="admin-body">
    <div class="admin-layout">
        @include('admin.partials.sidebar')

        <div class="admin-sidebar__backdrop" data-admin-backdrop></div>

        <div class="admin-main">
            <header class="admin-topbar">
                <button class="btn btn-light d-lg-none" type="button" data-sidebar-toggle aria-label="فتح القائمة">
                    <span aria-hidden="true">&#9776;</span>
                </button>

                <h1 class="h5 mb-0 flex-grow-1">@yield('title', 'لوحة التحكم')</h1>

                <div class="d-flex align-items-center gap-2">
                    <a href="{{ route('home') }}" class="btn btn-outline-primary btn-sm" target="_blank" rel="noopener">
                        عرض الموقع
                    </a>

                    <div class="dropdown">
                        <button class="btn btn-light btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
                            {{ auth()->user()->name }}
                        </button>
                        <ul class="dropdown-menu dropdown-menu-end">
                            <li>
                                <span class="dropdown-item-text small text-muted-soft">
                                    {{ auth()->user()->role?->label() }}
                                </span>
                            </li>
                            <li><hr class="dropdown-divider"></li>
                            <li>
                                <form method="POST" action="{{ route('admin.logout') }}">
                                    @csrf
                                    <button type="submit" class="dropdown-item">تسجيل الخروج</button>
                                </form>
                            </li>
                        </ul>
                    </div>
                </div>
            </header>

            <main class="admin-content">
                @include('admin.partials.flash')
                @yield('content')
            </main>
        </div>
    </div>

    @stack('scripts')
</body>
</html>
