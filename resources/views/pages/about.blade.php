@extends('layouts.app')

@section('meta_title', $page->meta_title ?: 'من نحن | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', $page->meta_description ?: setting('company_description'))

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    <div class="cms-content" style="line-height: 2.1; color: var(--muted);">
                        {{-- Admin-authored HTML from the CMS. --}}
                        {!! $page->content !!}
                    </div>
                </div>
            </div>
        </div>
    </section>

    @if ($whyChooseUs->isNotEmpty())
        <section class="section section--surface">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-title">لماذا نحن</h2>
                </div>

                <div class="row g-4">
                    @foreach ($whyChooseUs as $item)
                        <div class="col-sm-6 col-lg-3">
                            <div class="feature-card">
                                <div class="feature-card__icon" aria-hidden="true">
                                    {!! $item->icon ? e($item->icon) : '&#10003;' !!}
                                </div>
                                <h3 class="feature-card__title">{{ $item->title }}</h3>
                                <p class="feature-card__text">{{ $item->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif

    @if ($howItWorks->isNotEmpty())
        <section class="section">
            <div class="container">
                <div class="text-center mb-5">
                    <h2 class="section-title">كيف نعمل</h2>
                </div>

                <div class="row g-4">
                    @foreach ($howItWorks as $index => $step)
                        <div class="col-sm-6 col-lg-3">
                            <div class="step-card">
                                <div class="step-card__number">{{ $index + 1 }}</div>
                                <h3 class="feature-card__title">{{ $step->title }}</h3>
                                <p class="feature-card__text">{{ $step->description }}</p>
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </section>
    @endif
@endsection
