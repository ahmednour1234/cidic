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
                        <div class="col-md-6 col-lg-4">
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
@endsection
