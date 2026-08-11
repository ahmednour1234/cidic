<div class="admin-card">
    <div class="admin-card__body">
        <div class="row g-3">
            <div class="col-md-8">
                <label for="title" class="form-label">العنوان <span class="required-mark">*</span></label>
                <input type="text" id="title" name="title"
                       class="form-control @error('title') is-invalid @enderror"
                       value="{{ old('title', $page->title) }}" required maxlength="200">
                @error('title')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-4">
                <label for="slug" class="form-label">الرابط (slug)</label>
                <input type="text" id="slug" name="slug" dir="ltr"
                       class="form-control @error('slug') is-invalid @enderror"
                       value="{{ old('slug', $page->slug) }}" maxlength="200">
                @error('slug')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-12">
                <label for="content" class="form-label">المحتوى (HTML)</label>
                <textarea id="content" name="content" rows="16" class="form-control" dir="rtl"
                          style="font-family: monospace; font-size: 0.9rem;">{{ old('content', $page->content) }}</textarea>
                <div class="form-text">
                    يمكنك استخدام وسوم HTML مثل &lt;p&gt; و &lt;h3&gt; و &lt;ul&gt;.
                </div>
            </div>

            <div class="col-md-6">
                <label for="meta_title" class="form-label">عنوان الميتا</label>
                <input type="text" id="meta_title" name="meta_title" class="form-control"
                       value="{{ old('meta_title', $page->meta_title) }}" maxlength="255">
            </div>

            <div class="col-md-6">
                <label for="meta_description" class="form-label">وصف الميتا</label>
                <input type="text" id="meta_description" name="meta_description" class="form-control"
                       value="{{ old('meta_description', $page->meta_description) }}" maxlength="500">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $page->exists ? $page->is_active : true))>
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
    <a href="{{ route('admin.pages.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
