<!DOCTYPE html>
<html lang="ar" dir="rtl">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    @php
        $metaTitle = trim($__env->yieldContent('meta_title')) ?: setting('meta_title', setting('company_name_ar', 'سدك للإستقدام'));
        $metaDescription = trim($__env->yieldContent('meta_description')) ?: setting('meta_description', 'خدمات استقدام العمالة المنزلية في السعودية.');
        // Social crawlers require absolute URLs.
        $ogImage = trim($__env->yieldContent('og_image')) ?: setting_image('og_image', null, true);

        if ($ogImage && ! str_starts_with($ogImage, 'http')) {
            $ogImage = url($ogImage);
        }
    @endphp

    <title>{{ $metaTitle }}</title>
    <meta name="description" content="{{ $metaDescription }}">
    <link rel="canonical" href="{{ url()->current() }}">

    {{-- OpenGraph --}}
    <meta property="og:type" content="website">
    <meta property="og:locale" content="ar_SA">
    <meta property="og:site_name" content="{{ setting('company_name_ar', 'سدك للإستقدام') }}">
    <meta property="og:title" content="{{ $metaTitle }}">
    <meta property="og:description" content="{{ $metaDescription }}">
    <meta property="og:url" content="{{ url()->current() }}">
    @if ($ogImage)
        <meta property="og:image" content="{{ $ogImage }}">
    @endif

    {{-- Twitter --}}
    <meta name="twitter:card" content="summary_large_image">
    <meta name="twitter:title" content="{{ $metaTitle }}">
    <meta name="twitter:description" content="{{ $metaDescription }}">
    @if ($ogImage)
        <meta name="twitter:image" content="{{ $ogImage }}">
    @endif

    @if ($favicon = setting_image('favicon'))
        <link rel="icon" href="{{ $favicon }}">
    @endif

    @vite(['resources/css/app.css', 'resources/css/site.css', 'resources/js/app.js'])
    @stack('styles')
</head>
<body>
    @include('partials.header')

    <main id="main-content">
        @include('partials.flash')
        @yield('content')
    </main>

    @include('partials.footer')
    @include('partials.whatsapp')
    @include('partials.intro-modal')

    @stack('scripts')
</body>
</html>
