@extends('layouts.app')

@section('meta_title', 'اطلب الآن | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'قدم طلب استقدام العمالة المنزلية وسيتم التواصل معك في أقرب وقت.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>طلب استقدام</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">اطلب الآن</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-1">بيانات الطلب</h2>
                            <p class="text-muted-soft small mb-4">
                                أدخل بياناتك وسيقوم فريقنا بالتواصل معك لاستكمال التفاصيل.
                            </p>

                            @include('partials.quick-request-form', ['services' => $services])
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
