@extends('layouts.app')

@section('meta_title', 'الجنسيات المتاحة | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'تصفح الجنسيات المتاحة للاستقدام واختر السير الذاتية المناسبة.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>الجنسيات المتاحة</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الجنسيات</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            @if ($nationalities->isNotEmpty())
                <p class="section-subtitle text-center mb-5">
                    اضغط على الجنسية لعرض السير الذاتية المتاحة منها.
                </p>

                <div class="row g-3 g-md-4">
                    @foreach ($nationalities as $nationality)
                        <div class="col-6 col-md-4 col-lg-3" data-reveal="zoom" data-reveal-delay="{{ $loop->index * 60 }}">
                            <x-nationality-card :nationality="$nationality" />
                        </div>
                    @endforeach
                </div>
            @else
                <div class="empty-state">
                    <div class="empty-state__icon" aria-hidden="true">&#9873;</div>
                    <p class="mb-0">لا توجد جنسيات متاحة حالياً.</p>
                </div>
            @endif
        </div>
    </section>
@endsection
