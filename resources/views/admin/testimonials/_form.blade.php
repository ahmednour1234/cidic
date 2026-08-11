<div class="admin-card">
    <div class="admin-card__body">
        <div class="row g-3">
            <div class="col-md-5">
                <label for="name" class="form-label">الاسم <span class="required-mark">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $record->name) }}" required maxlength="150">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label for="city" class="form-label">المدينة</label>
                <input type="text" id="city" name="city" class="form-control"
                       value="{{ old('city', $record->city) }}" maxlength="128">
            </div>

            <div class="col-md-2">
                <label for="rating" class="form-label">التقييم <span class="required-mark">*</span></label>
                <select id="rating" name="rating" class="form-select" required>
                    @for ($i = 5; $i >= 1; $i--)
                        <option value="{{ $i }}" @selected(old('rating', $record->rating ?? 5) == $i)>{{ $i }}</option>
                    @endfor
                </select>
            </div>

            <div class="col-md-2">
                <label for="sort_order" class="form-label">الترتيب</label>
                <input type="number" id="sort_order" name="sort_order" min="0" class="form-control"
                       value="{{ old('sort_order', $record->sort_order ?? 0) }}">
            </div>

            <div class="col-12">
                <label for="review" class="form-label">الرأي <span class="required-mark">*</span></label>
                <textarea id="review" name="review" rows="4"
                          class="form-control @error('review') is-invalid @enderror"
                          required maxlength="2000">{{ old('review', $record->review) }}</textarea>
                @error('review')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="avatar" class="form-label">الصورة</label>
                <input type="file" id="avatar" name="avatar" class="form-control @error('avatar') is-invalid @enderror"
                       accept=".jpg,.jpeg,.png,.webp">
                @error('avatar')<div class="invalid-feedback">{{ $message }}</div>@enderror
                @if ($record->avatar_url)
                    <img src="{{ $record->avatar_url }}" alt="" class="file-preview">
                @endif
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
    <a href="{{ route('admin.testimonials.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
