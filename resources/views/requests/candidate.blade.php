@extends('layouts.app')

@section('meta_title', 'طلب العاملة ' . $candidate->reference_number . ' | ' . setting('company_name_ar'))

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>تقديم طلب</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('candidates.index') }}">السير الذاتية</a></li>
                    <li class="breadcrumb-item">
                        <a href="{{ route('candidates.show', $candidate) }}">{{ $candidate->name }}</a>
                    </li>
                    <li class="breadcrumb-item active" aria-current="page">تقديم طلب</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4 justify-content-center">
                {{-- Selected candidate summary --}}
                <div class="col-lg-4 order-lg-2">
                    <div class="card">
                        <div class="card-body">
                            <h2 class="h6 mb-3">العاملة المطلوبة</h2>

                            <div class="d-flex gap-3 align-items-center mb-3">
                                <img src="{{ $candidate->profile_image_url }}" alt=""
                                     style="width: 72px; height: 96px; object-fit: cover; border-radius: var(--radius-sm);">
                                <div>
                                    <strong class="d-block">{{ $candidate->name }}</strong>
                                    <span class="d-block small text-muted-soft" dir="ltr">{{ $candidate->reference_number }}</span>
                                    <span class="badge-soft bg-{{ $candidate->availability_status->badge() }}-subtle text-{{ $candidate->availability_status->badge() }}-emphasis mt-1 d-inline-block">
                                        {{ $candidate->availability_status->label() }}
                                    </span>
                                </div>
                            </div>

                            <ul class="list-unstyled small mb-0" style="line-height: 2;">
                                <li>الجنسية: <strong>{{ $candidate->nationality?->name_ar ?? '—' }}</strong></li>
                                @if ($candidate->display_age)
                                    <li>العمر: <strong>{{ $candidate->display_age }} سنة</strong></li>
                                @endif
                                <li>المهنة: <strong>{{ $candidate->profession }}</strong></li>
                                <li>الخبرة: <strong>{{ $candidate->years_of_experience }} سنوات</strong></li>
                                <li>اللغات: <strong>{{ $candidate->languages_label }}</strong></li>
                            </ul>
                        </div>
                    </div>
                </div>

                {{-- Request form --}}
                <div class="col-lg-7 order-lg-1">
                    <div class="card">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-1">بيانات التواصل</h2>
                            <p class="text-muted-soft small mb-4">
                                لا حاجة لإنشاء حساب. أدخل بياناتك وسيتم التواصل معك في أقرب وقت.
                            </p>

                            <form method="POST" action="{{ route('candidate-requests.store', $candidate) }}" data-submit-guard>
                                @csrf

                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="customer_name" class="form-label">
                                            الاسم <span class="required-mark">*</span>
                                        </label>
                                        <input type="text" id="customer_name" name="customer_name"
                                               class="form-control @error('customer_name') is-invalid @enderror"
                                               value="{{ old('customer_name') }}" required maxlength="150">
                                        @error('customer_name')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
                                        <label for="service_type" class="form-label">نوع الخدمة</label>
                                        <select id="service_type" name="service_type" class="form-select">
                                            <option value="">اختر الخدمة</option>
                                            @foreach ($services as $service)
                                                <option value="{{ $service->title }}" @selected(old('service_type') === $service->title)>
                                                    {{ $service->title }}
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
