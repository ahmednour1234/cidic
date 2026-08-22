{{--
    Shortened request form: name, mobile, city, service.

    Shared by the request page and the intro modal so the two never drift.
    The fields left out (whatsapp, email, nationality, category, notes) are
    all nullable in StoreRecruitmentRequest and still exist on the table, so
    omitting them here changes nothing server-side and older requests keep
    their values.

    $idPrefix namespaces the ids, since the modal renders a second copy of
    this form on the same page and duplicate ids would break every label.
--}}
@php
    $idPrefix = $idPrefix ?? '';
    $p = $idPrefix ? $idPrefix . '-' : '';
@endphp

<form method="POST" action="{{ route('recruitment-requests.store') }}" data-submit-guard>
    @csrf
    <div class="row g-3">
        <div class="col-md-6">
            <label for="{{ $p }}name" class="form-label">
                الاسم <span class="required-mark">*</span>
            </label>
            <input type="text" id="{{ $p }}name" name="name"
                   class="form-control @error('name') is-invalid @enderror"
                   value="{{ old('name') }}" required maxlength="150">
            @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="{{ $p }}mobile" class="form-label">
                رقم الجوال <span class="required-mark">*</span>
            </label>
            <input type="tel" id="{{ $p }}mobile" name="mobile" dir="ltr"
                   class="form-control @error('mobile') is-invalid @enderror"
                   value="{{ old('mobile') }}" required placeholder="05xxxxxxxx">
            @error('mobile')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="{{ $p }}city" class="form-label">المدينة</label>
            <input type="text" id="{{ $p }}city" name="city"
                   class="form-control @error('city') is-invalid @enderror"
                   value="{{ old('city') }}" maxlength="128">
            @error('city')<div class="invalid-feedback">{{ $message }}</div>@enderror
        </div>

        <div class="col-md-6">
            <label for="{{ $p }}service_id" class="form-label">الخدمة المطلوبة</label>
            <select id="{{ $p }}service_id" name="service_id" class="form-select">
                <option value="">اختر الخدمة</option>
                @foreach ($services as $service)
                    <option value="{{ $service->id }}" @selected(old('service_id') == $service->id)>
                        {{ $service->title }}
                    </option>
                @endforeach
            </select>
        </div>
    </div>

    <div class="d-grid mt-4">
        <button type="submit" class="btn btn-primary btn-lg">
            إرسال الطلب
            <span class="btn-spinner" aria-hidden="true"></span>
        </button>
    </div>
</form>
