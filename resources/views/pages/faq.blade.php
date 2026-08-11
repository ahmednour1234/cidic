@extends('layouts.app')

@section('meta_title', 'الأسئلة الشائعة | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'إجابات على أكثر الأسئلة تكراراً حول خدمات الاستقدام والإيجار الشهري ونقل الخدمات.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>الأسئلة الشائعة</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">الأسئلة الشائعة</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    @if ($faqs->isNotEmpty())
                        <div class="accordion" id="faqPageAccordion">
                            @foreach ($faqs as $index => $faq)
                                <x-faq-item :faq="$faq" :index="$index" parent="faqPageAccordion" />
                            @endforeach
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state__icon" aria-hidden="true">&#63;</div>
                            <p class="mb-0">لا توجد أسئلة متاحة حالياً.</p>
                        </div>
                    @endif

                    <div class="text-center mt-5">
                        <p class="text-muted-soft">لم تجد إجابة لسؤالك؟</p>
                        <a href="{{ route('contact.create') }}" class="btn btn-primary">تواصل معنا</a>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
