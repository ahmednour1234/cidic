@extends('layouts.app')

@section('meta_title', $service->meta_title ?: $service->title . ' | ' . setting('company_name_ar'))
@section('meta_description', $service->meta_description ?: $service->short_description)

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>{{ $service->title }}</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('services.index') }}">خدماتنا</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $service->title }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-5">
                <div class="col-lg-8">
                    @if ($service->image_url)
                        <img src="{{ $service->image_url }}" alt="{{ $service->title }}"
                             class="w-100 mb-4" style="border-radius: var(--radius); object-fit: cover;">
                    @endif

                    @if ($service->short_description)
                        <p class="lead" style="line-height: 2;">{{ $service->short_description }}</p>
                    @endif

                    @if ($service->description)
                        <p style="line-height: 2.1; color: var(--muted);">{{ $service->description }}</p>
                    @endif

                    <a href="{{ route('recruitment-requests.create') }}" class="btn btn-primary btn-lg mt-3">
                        اطلب هذه الخدمة
                    </a>
                </div>

                <div class="col-lg-4">
                    @if ($services->isNotEmpty())
                        <div class="card">
                            <div class="card-body">
                                <h2 class="h6 mb-3">خدمات أخرى</h2>
                                <ul class="list-unstyled mb-0" style="line-height: 2.4;">
                                    @foreach ($services as $other)
                                        <li>
                                            <a href="{{ route('services.show', $other) }}">{{ $other->title }}</a>
                                        </li>
                                    @endforeach
                                </ul>
                            </div>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
