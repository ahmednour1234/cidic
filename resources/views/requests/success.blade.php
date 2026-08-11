@extends('layouts.app')

@section('meta_title', 'تم استلام طلبك | ' . setting('company_name_ar', 'سدك للإستقدام'))

@section('content')
    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-7">
                    <div class="card text-center">
                        <div class="card-body p-5">
                            <div style="width: 84px; height: 84px; border-radius: 50%; background: var(--primary-light); color: var(--success); display: grid; place-items: center; font-size: 2.5rem; margin: 0 auto 1.5rem;">
                                &#10003;
                            </div>

                            <h1 class="h4 mb-3">تم استلام طلبك بنجاح وسيتم التواصل معك قريباً.</h1>

                            <p class="text-muted-soft mb-4">
                                رقم الطلب الخاص بك، يرجى الاحتفاظ به للمراجعة:
                            </p>

                            <p class="mb-4">
                                <span class="badge bg-primary-subtle text-primary-emphasis fs-5 px-4 py-2" dir="ltr">
                                    {{ $requestNumber }}
                                </span>
                            </p>

                            <div class="d-flex flex-wrap justify-content-center gap-2">
                                <a href="{{ route('candidates.index') }}" class="btn btn-primary">تصفح السير الذاتية</a>
                                <a href="{{ route('home') }}" class="btn btn-outline-primary">العودة للرئيسية</a>
                                @if (setting('whatsapp'))
                                    <a href="{{ whatsapp_url('السلام عليكم، بخصوص الطلب رقم: ' . $requestNumber) }}"
                                       target="_blank" rel="noopener" class="btn btn-outline-secondary">
                                        متابعة عبر واتساب
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
