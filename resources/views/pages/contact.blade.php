@extends('layouts.app')

@section('meta_title', 'تواصل معنا | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'تواصل مع فريق سدك للإستقدام للاستفسار عن خدمات الاستقدام.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>تواصل معنا</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">تواصل معنا</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4">
                <div class="col-lg-4">
                    <div class="card h-100">
                        <div class="card-body p-4">
                            <h2 class="h6 mb-3">معلومات التواصل</h2>

                            <ul class="list-unstyled mb-4" style="line-height: 2.2;">
                                @if ($phone = setting('phone'))
                                    <li>الهاتف: <a href="{{ tel_url($phone) }}" dir="ltr">{{ $phone }}</a></li>
                                @endif
                                @if ($wa = setting('whatsapp'))
                                    <li>واتساب: <a href="{{ whatsapp_url() }}" target="_blank" rel="noopener" dir="ltr">{{ $wa }}</a></li>
                                @endif
                                @if ($email = setting('email'))
                                    <li>البريد: <a href="mailto:{{ $email }}" dir="ltr">{{ $email }}</a></li>
                                @endif
                                @if ($address = setting('address'))
                                    <li>العنوان: {{ $address }}</li>
                                @endif
                                @if ($license = setting('license_number'))
                                    <li>رقم الترخيص: <span dir="ltr">{{ $license }}</span></li>
                                @endif
                            </ul>

                            @if ($map = setting('google_map_url'))
                                <a href="{{ $map }}" target="_blank" rel="noopener" class="btn btn-outline-primary w-100">
                                    عرض الموقع على الخريطة
                                </a>
                            @endif
                        </div>
                    </div>
                </div>

                <div class="col-lg-8">
                    <div class="card">
                        <div class="card-body p-4">
                            <h2 class="h5 mb-4">أرسل لنا رسالة</h2>

                            <form method="POST" action="{{ route('contact.store') }}" data-submit-guard>
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
                                        <label for="email" class="form-label">البريد الإلكتروني</label>
                                        <input type="email" id="email" name="email" dir="ltr"
                                               class="form-control @error('email') is-invalid @enderror"
                                               value="{{ old('email') }}">
                                        @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-md-6">
                                        <label for="subject" class="form-label">الموضوع</label>
                                        <input type="text" id="subject" name="subject"
                                               class="form-control @error('subject') is-invalid @enderror"
                                               value="{{ old('subject') }}" maxlength="200">
                                        @error('subject')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>

                                    <div class="col-12">
                                        <label for="message" class="form-label">
                                            الرسالة <span class="required-mark">*</span>
                                        </label>
                                        <textarea id="message" name="message" rows="5"
                                                  class="form-control @error('message') is-invalid @enderror"
                                                  required maxlength="3000">{{ old('message') }}</textarea>
                                        @error('message')<div class="invalid-feedback">{{ $message }}</div>@enderror
                                    </div>
                                </div>

                                <div class="mt-4">
                                    <button type="submit" class="btn btn-primary btn-lg">
                                        إرسال الرسالة
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
