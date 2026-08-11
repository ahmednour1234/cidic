@extends('layouts.admin')

@section('title', 'رفع سير ذاتية متعددة')

@section('content')
    <form method="POST" action="{{ route('admin.candidates.bulk.store') }}"
          enctype="multipart/form-data" data-submit-guard
          x-data="bulkUpload()">
        @csrf

        <div class="row g-4">
            <div class="col-lg-8">
                <div class="admin-card mb-3">
                    <div class="admin-card__header">
                        <h2 class="admin-card__title">ملفات السير الذاتية (PDF)</h2>
                    </div>
                    <div class="admin-card__body">
                        <div class="alert alert-info">
                            اختر عدة ملفات PDF مرة واحدة. سيتم إنشاء سيرة ذاتية لكل ملف،
                            <strong>ويُؤخذ اسم العاملة من اسم الملف</strong> — ويمكنك تعديله لاحقاً من صفحة التعديل.
                        </div>

                        <div class="mb-3">
                            <label for="cv_files" class="form-label">
                                الملفات <span class="required-mark">*</span>
                            </label>
                            <input type="file" id="cv_files" name="cv_files[]"
                                   class="form-control @error('cv_files') is-invalid @enderror @error('cv_files.*') is-invalid @enderror"
                                   accept="application/pdf,.pdf" multiple required
                                   x-on:change="onPick($event)">
                            <div class="form-text">
                                PDF فقط — بحد أقصى {{ $maxFiles }} ملف، وكل ملف حتى 10 ميجابايت.
                            </div>
                            @error('cv_files')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                            @error('cv_files.*')<div class="invalid-feedback d-block">{{ $message }}</div>@enderror
                        </div>

                        {{-- Live preview of the names that will be created. --}}
                        <template x-if="files.length > 0">
                            <div>
                                <div class="d-flex align-items-center justify-content-between mb-2">
                                    <strong>سيتم إنشاء <span x-text="files.length"></span> سيرة ذاتية:</strong>
                                    <span class="small text-muted-soft" x-text="totalSize"></span>
                                </div>

                                <div class="table-responsive" style="max-height: 340px; overflow-y: auto;">
                                    <table class="table admin-table">
                                        <thead>
                                            <tr>
                                                <th style="width: 48px;">#</th>
                                                <th>اسم الملف</th>
                                                <th>الاسم المستخرج</th>
                                                <th style="width: 90px;">الحجم</th>
                                            </tr>
                                        </thead>
                                        <tbody>
                                            <template x-for="(f, i) in files" :key="i">
                                                <tr>
                                                    <td x-text="i + 1"></td>
                                                    <td class="small text-muted-soft" dir="ltr" x-text="f.name"></td>
                                                    <td><strong x-text="f.derived"></strong></td>
                                                    <td class="small" x-text="f.size"></td>
                                                </tr>
                                            </template>
                                        </tbody>
                                    </table>
                                </div>

                                <p class="form-text mt-2">
                                    الاسم المعروض هنا تقديري؛ الاسم النهائي يُحسب على الخادم ويمكن تعديله بعد الرفع.
                                </p>
                            </div>
                        </template>
                    </div>
                </div>
            </div>

            {{-- Shared values applied to every CV in the batch --}}
            <div class="col-lg-4">
                <div class="admin-card mb-3">
                    <div class="admin-card__header">
                        <h2 class="admin-card__title">بيانات مشتركة لكل الملفات</h2>
                    </div>
                    <div class="admin-card__body">
                        <div class="mb-3">
                            <label for="nationality_id" class="form-label">
                                الجنسية <span class="required-mark">*</span>
                            </label>
                            <select id="nationality_id" name="nationality_id"
                                    class="form-select @error('nationality_id') is-invalid @enderror" required>
                                <option value="">اختر الجنسية</option>
                                @foreach ($nationalities as $nationality)
                                    <option value="{{ $nationality->id }}"
                                        @selected(old('nationality_id') == $nationality->id)>
                                        {{ $nationality->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="candidate_category_id" class="form-label">
                                التصنيف <span class="required-mark">*</span>
                            </label>
                            <select id="candidate_category_id" name="candidate_category_id"
                                    class="form-select @error('candidate_category_id') is-invalid @enderror" required>
                                <option value="">اختر التصنيف</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->id }}"
                                        @selected(old('candidate_category_id') == $category->id)>
                                        {{ $category->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                            <div class="form-text">تُستخدم أيضاً كمهنة افتراضية لكل السير.</div>
                            @error('candidate_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                        </div>

                        <div class="mb-3">
                            <label for="availability_status" class="form-label">حالة التوفر</label>
                            <select id="availability_status" name="availability_status" class="form-select">
                                @foreach ($availabilityOptions as $value => $label)
                                    <option value="{{ $value }}"
                                        @selected(old('availability_status', 'available') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="years_of_experience" class="form-label">سنوات الخبرة</label>
                            <input type="number" id="years_of_experience" name="years_of_experience"
                                   min="0" max="50" class="form-control"
                                   value="{{ old('years_of_experience', 0) }}">
                            <div class="form-text">يمكن تعديلها لكل سيرة لاحقاً.</div>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="language_arabic" class="form-label">العربية</label>
                                <select id="language_arabic" name="language_arabic" class="form-select">
                                    @foreach (['none' => 'لا توجد', 'basic' => 'مبتدئة', 'good' => 'جيدة', 'fluent' => 'ممتازة'] as $v => $l)
                                        <option value="{{ $v }}" @selected(old('language_arabic', 'none') === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="col-6">
                                <label for="language_english" class="form-label">الإنجليزية</label>
                                <select id="language_english" name="language_english" class="form-select">
                                    @foreach (['none' => 'لا توجد', 'basic' => 'مبتدئة', 'good' => 'جيدة', 'fluent' => 'ممتازة'] as $v => $l)
                                        <option value="{{ $v }}" @selected(old('language_english', 'none') === $v)>{{ $l }}</option>
                                    @endforeach
                                </select>
                            </div>
                        </div>

                        <div class="form-check">
                            <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                                   @checked(old('is_active', true))>
                            <label class="form-check-label" for="is_active">تفعيل السير مباشرة (تظهر في الموقع)</label>
                        </div>
                    </div>
                </div>

                <div class="d-grid gap-2">
                    <button type="submit" class="btn btn-primary btn-lg">
                        رفع وإنشاء السير الذاتية
                        <span class="btn-spinner" aria-hidden="true"></span>
                    </button>
                    <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary">إلغاء</a>
                </div>
            </div>
        </div>
    </form>
@endsection

@push('scripts')
<script>
    /**
     * Client-side preview only. Mirrors App\Support\CvFilename loosely so the
     * admin can sanity-check the batch before uploading; the server remains the
     * source of truth for the stored name.
     */
    function bulkUpload() {
        const NOISE = new Set([
            'cv', 'cvs', 'resume', 'resumes', 'curriculum', 'vitae', 'copy',
            'final', 'new', 'scan', 'scanned', 'doc', 'document', 'file',
            'profile', 'bio', 'data', 'form', 'sheet',
        ]);

        const derive = (filename) => {
            let base = filename.replace(/\.[^.]+$/, '');
            base = base.replace(/[_\-.+]+/g, ' ').replace(/[([{]\s*\d+\s*[)\]}]/g, ' ');

            const kept = base.split(/\s+/).filter((t) => {
                const s = t.trim();
                if (!s) return false;
                if (NOISE.has(s.toLowerCase())) return false;
                if (/^\d+$/.test(s)) return false;
                if (/^[0-9a-f]{8,}$/i.test(s)) return false;
                return true;
            });

            if (kept.length === 0) return base.trim() || 'سيرة ذاتية';

            return kept
                .map((w) => (/^[a-z]/.test(w) ? w.charAt(0).toUpperCase() + w.slice(1) : w))
                .join(' ');
        };

        const humanSize = (bytes) => {
            if (bytes < 1024) return bytes + ' B';
            if (bytes < 1048576) return Math.round(bytes / 1024) + ' KB';
            return (bytes / 1048576).toFixed(1) + ' MB';
        };

        return {
            files: [],
            totalSize: '',

            onPick(event) {
                const picked = Array.from(event.target.files || []);

                this.files = picked.map((f) => ({
                    name: f.name,
                    derived: derive(f.name),
                    size: humanSize(f.size),
                }));

                const total = picked.reduce((sum, f) => sum + f.size, 0);
                this.totalSize = picked.length ? 'الحجم الإجمالي: ' + humanSize(total) : '';
            },
        };
    }
</script>
@endpush
