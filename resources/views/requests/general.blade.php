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

                            <form method="POST" action="{{ route('recruitment-requests.store') }}" data-submit-guard>
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="name" class="form-label">
                                            الاسم <span class="required-mark">*</span>
                                        </label>
                                        <input type="text" id="name" name="name"
                                               class="form-control @error('name') is-invalid @enderror"
                                               value="{{ old('name') }}" required maxlength="150">
                                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="mobile" class="form-label">
                                            رقم الجوال <span class="required-mark">*</span>
                                        </label>
                                        <input type="tel" id="mobile" name="mobile" dir="ltr"
                                               class="form-control @error('mobile') is-invalid @enderror"
                                               value="{{ old('mobile') }}" required placeholder="05xxxxxxxx">
                                        @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="whatsapp" class="form-label">رقم الواتساب</label>
                                        <input type="tel" id="whatsapp" name="whatsapp" dir="ltr"
                                               class="form-control @error('whatsapp') is-invalid @enderror"
                                               value="{{ old('whatsapp') }}" placeholder="05xxxxxxxx">
                                        @error('whatsapp')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="email" class="form-label">البريد الإلكتروني</label>
                                        <input type="email" id="email" name="email" dir="ltr"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="city" class="form-label">المدينة</label>
                                        <input type="text" id="city" name="city"
                                               class="form-control @error('city') is-invalid @enderror"
                                               value="{{ old('city') }}" maxlength="128">
                                        @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="service_id" class="form-label">الخدمة المطلوبة</label>
                                        <select id="service_id" name="service_id" class="form-select">
                                            <option value="">اختر الخدمة</option>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                                                    {{ $service->title }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="nationality_id" class="form-label">الجنسية المطلوبة</label>
                                        <select id="nationality_id" name="nationality_id" class="form-select">
                                            <option value="">اختر الجنسية</option>
                                            @foreach ($nationalities as $nationality)
                                                <option value="{{ $nationality->id }}" @selected(old('nationality_id') == $nationality->id)>
                                                    {{ $nationality->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label for="candidate_category_id" class="form-label">تصنيف العمالة</label>
                                        <select id="candidate_category_id" name="candidate_category_id" class="form-select">
                                            <option value="">اختر التصنيف</option>
                                            @foreach ($categories as $category)
                                                <option value="{{ $category->id }}" @selected(old('candidate_category_id') == $category->id)>
                                                    {{ $category->name_ar }}
                                                </option>
                                            @endforeach
                                        </select>
                                    </div>

                                    <div class="col-12">
                                        <label for="notes" class="form-label">ملاحظات</label>
                                        <textarea id="notes" name="notes" rows="4"
                                                  class="form-control @error('notes') is-invalid @enderror"
                                                  maxlength="2000">{{ old('notes') }}</textarea>
                                        @error('notes')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="d-grid mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        إرسال الطلب
                                        <span class="btn-spinner" aria-hidden="true"></span>
                                    </button>
                                </div>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
@endsection
