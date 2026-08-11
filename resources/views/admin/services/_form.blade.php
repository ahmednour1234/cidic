<div class="admin-card">
    <div class="admin-card__body">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="title" class="form-label">العنوان <span class="required-mark">*</span></label>
                <input type="text" id="title" name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $service->title) }}" required maxlength="150">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="slug" class="form-label">الرابط (slug)</label>
                <input type="text" id="slug" name="slug" dir="ltr"
                       class="form-control @error('slug') is-invalid @enderror"
                       value="{{ old('slug', $service->slug) }}" maxlength="150">
                <div class="form-text">يُنشأ تلقائياً إذا تُرك فارغاً.</div>
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="short_description" class="form-label">الوصف المختصر</label>
                <textarea id="short_description" name="short_description" rows="2" class="form-control"
                          maxlength="500">{{ old('short_description', $service->short_description) }}</textarea>
            </div>

            <div class="col-12">
                <label for="description" class="form-label">الوصف التفصيلي</label>
                <textarea id="description" name="description" rows="6" class="form-control"
                          maxlength="10000">{{ old('description', $service->description) }}</textarea>
            </div>

            <div class="col-md-4">
                <label for="icon" class="form-label">الأيقونة (رمز)</label>
                <input type="text" id="icon" name="icon" class="form-control"
                       value="{{ old('icon', $service->icon) }}" maxlength="100">
            </div>

            <div class="col-md-4">
                <label for="image" class="form-label">الصورة</label>
                <input type="file" id="image" name="image" class="form-control @error('image') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.webp">
                @error('image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($service->image_url)
                    <img src="{{ $service->image_url }}" alt="" class="file-preview">
                @endif
            </div>

            <div class="col-md-4">
                <label for="sort_order" class="form-label">ترتيب العرض</label>
                <input type="number" id="sort_order" name="sort_order" min="0" class="form-control"
                       value="{{ old('sort_order', $service->sort_order ?? 0) }}">
            </div>

            <div class="col-md-6">
                <label for="meta_title" class="form-label">عنوان الميتا</label>
                <input type="text" id="meta_title" name="meta_title" class="form-control"
                       value="{{ old('meta_title', $service->meta_title) }}" maxlength="255">
            </div>

            <div class="col-md-6">
                <label for="meta_description" class="form-label">وصف الميتا</label>
                <input type="text" id="meta_description" name="meta_description" class="form-control"
                       value="{{ old('meta_description', $service->meta_description) }}" maxlength="500">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $service->exists ? $service->is_active : true))>
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
    <a href="{{ route('admin.services.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
