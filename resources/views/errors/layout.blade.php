@extends('layouts.app')

@section('meta_title', $title . ' | ' . setting('company_name_ar', 'سدك للإستقدام'))

@section('content')
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card text-center">
                        <div class="card-body p-5">
                            <div style="font-size: 4.5rem; font-weight: 800; color: var(--primary); line-height: 1;">
                                {{ $code }}
                            </div>

                            <h1 class="h4 mt-3 mb-3">{{ $title }}</h1>
                            <p class="text-muted-soft mb-4">{{ $message }}</p>

                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('home') }}" class="btn btn-primary">العودة للرئيسية</a>
                                <a href="{{ route('candidates.index') }}" class="btn btn-outline-primary">تصفح السير الذاتية</a>
                                <a href="{{ route('contact.create') }}" class="btn btn-outline-secondary">تواصل معنا</a>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
