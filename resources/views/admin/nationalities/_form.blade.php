<div class="admin-card">
    <div class="admin-card__body">
        <div class="row g-3">
            <div class="col-md-6">
                <label for="name_ar" class="form-label">الاسم بالعربية <span class="required-mark">*</span></label>
                <input type="text" id="name_ar" name="name_ar"
                       class="form-control @error('name_ar') is-invalid @enderror"
                       value="{{ old('name_ar', $nationality->name_ar) }}" required maxlength="150">
                @error('name_ar')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="name_en" class="form-label">الاسم بالإنجليزية</label>
                <input type="text" id="name_en" name="name_en" dir="ltr" class="form-control"
                       value="{{ old('name_en', $nationality->name_en) }}" maxlength="150">
            </div>

            <div class="col-md-4">
                <label for="slug" class="form-label">الرابط (slug)</label>
                <input type="text" id="slug" name="slug" dir="ltr"
                       class="form-control @error('slug') is-invalid @enderror"
                       value="{{ old('slug', $nationality->slug) }}" maxlength="150">
                <div class="form-text">يُنشأ تلقائياً إذا تُرك فارغاً.</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="country_code" class="form-label">رمز الدولة</label>
                <input type="text" id="country_code" name="country_code" dir="ltr" class="form-control"
                       value="{{ old('country_code', $nationality->country_code) }}" maxlength="8">
            </div>

            <div class="col-md-4">
                <label for="sort_order" class="form-label">ترتيب العرض</label>
                <input type="number" id="sort_order" name="sort_order" min="0" class="form-control"
                       value="{{ old('sort_order', $nationality->sort_order ?? 0) }}">
            </div>

            <div class="col-md-6">
                <label for="flag" class="form-label">العلم</label>
                <input type="file" id="flag" name="flag" class="form-control @error('flag') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.webp,.svg">
                @error('flag')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($nationality->flag_url)
                    <img src="{{ $nationality->flag_url }}" alt="" class="file-preview">
                @endif
            </div>

            <div class="col-12">
                <label for="description" class="form-label">الوصف</label>
                <textarea id="description" name="description" rows="3" class="form-control"
                          maxlength="2000">{{ old('description', $nationality->description) }}</textarea>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $nationality->exists ? $nationality->is_active : true))>
                    <label class="form-check-label" for="is_active">مفعّلة</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
        حفظ
        <span class="btn-spinner" aria-hidden="true"></span>
    </button>
    <a href="{{ route('admin.nationalities.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
