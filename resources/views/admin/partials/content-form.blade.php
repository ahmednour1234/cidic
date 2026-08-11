@php
    /** Shared form body for the simple title/description/icon content blocks. */
    $indexRoute = $indexRoute ?? 'admin.dashboard';
@endphp

<div class="admin-card">
    <div class="admin-card__body">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="title" class="form-label">العنوان <span class="required-mark">*</span></label>
                <input type="text" id="title" name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $record->title) }}" required maxlength="150">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="sort_order" class="form-label">ترتيب العرض</label>
                <input type="number" id="sort_order" name="sort_order" min="0" class="form-control"
                       value="{{ old('sort_order', $record->sort_order ?? 0) }}">
            </div>

            <div class="col-md-4">
                <label for="icon" class="form-label">الأيقونة (رمز)</label>
                <input type="text" id="icon" name="icon" class="form-control"
                       value="{{ old('icon', $record->icon) }}" maxlength="100">
            </div>

            <div class="col-12">
                <label for="description" class="form-label">الوصف</label>
                <textarea id="description" name="description" rows="4" class="form-control"
                          maxlength="2000">{{ old('description', $record->description) }}</textarea>
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $record->exists ? $record->is_active : true))>
                    <label class="form-check-label" for="is_active">مفعّل</label>
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
    <a href="{{ route($indexRoute) }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
