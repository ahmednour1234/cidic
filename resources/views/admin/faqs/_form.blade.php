<div class="admin-card">
    <div class="admin-card__body">
        <div class="row g-3">
            <div class="col-md-9">
                <label for="question" class="form-label">السؤال <span class="required-mark">*</span></label>
                <input type="text" id="question" name="question"
                       class="form-control @error('question') is-invalid @enderror"
                       value="{{ old('question', $record->question) }}" required maxlength="255">
                @error('question')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-3">
                <label for="sort_order" class="form-label">ترتيب العرض</label>
                <input type="number" id="sort_order" name="sort_order" min="0" class="form-control"
                       value="{{ old('sort_order', $record->sort_order ?? 0) }}">
            </div>

            <div class="col-12">
                <label for="answer" class="form-label">الإجابة <span class="required-mark">*</span></label>
                <textarea id="answer" name="answer" rows="5"
                          class="form-control @error('answer') is-invalid @enderror"
                          required maxlength="5000">{{ old('answer', $record->answer) }}</textarea>
                @error('answer')<div class="invalid-feedback">{{ $message }}</div>@enderror
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
    <a href="{{ route('admin.faqs.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
