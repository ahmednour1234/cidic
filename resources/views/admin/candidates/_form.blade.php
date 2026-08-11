@php
    /** @var \App\Models\Candidate $candidate */
    $skillsValue = old('skills', is_array($candidate->skills) ? implode('، ', $candidate->skills) : '');
    $countriesValue = old('previous_countries', is_array($candidate->previous_countries) ? implode('، ', $candidate->previous_countries) : '');
    $levels = ['none' => 'لا توجد', 'basic' => 'مبتدئة', 'good' => 'جيدة', 'fluent' => 'ممتازة'];
@endphp

<div class="row g-4">
    <div class="col-lg-8">
        {{-- Basic --}}
        <div class="admin-card mb-3">
            <div class="admin-card__header">
                <h2 class="admin-card__title">البيانات الأساسية</h2>
            </div>
            <div class="admin-card__body">
                <div class="row g-3">
                    <div class="col-md-6">
                        <label for="name" class="form-label">الاسم <span class="required-mark">*</span></label>
                        <input type="text" id="name" name="name"
                               class="form-control @error('name') is-invalid @enderror"
                               value="{{ old('name', $candidate->name) }}" required maxlength="150">
                        @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="profession" class="form-label">المهنة <span class="required-mark">*</span></label>
                        <input type="text" id="profession" name="profession"
                               class="form-control @error('profession') is-invalid @enderror"
                               value="{{ old('profession', $candidate->profession) }}" required maxlength="150">
                        @error('profession')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="nationality_id" class="form-label">الجنسية <span class="required-mark">*</span></label>
                        <select id="nationality_id" name="nationality_id"
                                class="form-select @error('nationality_id') is-invalid @enderror" required>
                            <option value="">اختر الجنسية</option>
                            @foreach ($nationalities as $nationality)
                                <option value="{{ $nationality->id }}"
                                    @selected(old('nationality_id', $candidate->nationality_id) == $nationality->id)>
                                    {{ $nationality->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        @error('nationality_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-6">
                        <label for="candidate_category_id" class="form-label">التصنيف <span class="required-mark">*</span></label>
                        <select id="candidate_category_id" name="candidate_category_id"
                                class="form-select @error('candidate_category_id') is-invalid @enderror" required>
                            <option value="">اختر التصنيف</option>
                            @foreach ($categories as $category)
                                <option value="{{ $category->id }}"
                                    @selected(old('candidate_category_id', $candidate->candidate_category_id) == $category->id)>
                                    {{ $category->name_ar }}
                                </option>
                            @endforeach
                        </select>
                        @error('candidate_category_id')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="date_of_birth" class="form-label">تاريخ الميلاد</label>
                        <input type="date" id="date_of_birth" name="date_of_birth" dir="ltr"
                               class="form-control @error('date_of_birth') is-invalid @enderror"
                               value="{{ old('date_of_birth', $candidate->date_of_birth?->format('Y-m-d')) }}">
                        @error('date_of_birth')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="age" class="form-label">العمر</label>
                        <input type="number" id="age" name="age" min="18" max="70"
                               class="form-control @error('age') is-invalid @enderror"
                               value="{{ old('age', $candidate->age) }}">
                        @error('age')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="gender" class="form-label">الجنس <span class="required-mark">*</span></label>
                        <select id="gender" name="gender" class="form-select" required>
                            <option value="female" @selected(old('gender', $candidate->gender ?: 'female') === 'female')>أنثى</option>
                            <option value="male" @selected(old('gender', $candidate->gender) === 'male')>ذكر</option>
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="years_of_experience" class="form-label">سنوات الخبرة <span class="required-mark">*</span></label>
                        <input type="number" id="years_of_experience" name="years_of_experience" min="0" max="50"
                               class="form-control @error('years_of_experience') is-invalid @enderror"
                               value="{{ old('years_of_experience', $candidate->years_of_experience ?? 0) }}" required>
                        @error('years_of_experience')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    </div>

                    <div class="col-md-4">
                        <label for="religion" class="form-label">الديانة</label>
                        <input type="text" id="religion" name="religion" class="form-control"
                               value="{{ old('religion', $candidate->religion) }}" maxlength="64">
                    </div>

                    <div class="col-md-4">
                        <label for="marital_status" class="form-label">الحالة الاجتماعية</label>
                        <select id="marital_status" name="marital_status" class="form-select">
                            <option value="">غير محدد</option>
                            @foreach (['single' => 'عزباء', 'married' => 'متزوجة', 'divorced' => 'مطلقة', 'widowed' => 'أرملة'] as $value => $label)
                                <option value="{{ $value }}" @selected(old('marital_status', $candidate->marital_status) === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="children_count" class="form-label">عدد الأبناء</label>
                        <input type="number" id="children_count" name="children_count" min="0" max="20"
                               class="form-control" value="{{ old('children_count', $candidate->children_count) }}">
                    </div>

                    <div class="col-md-4">
                        <label for="education" class="form-label">المؤهل</label>
                        <input type="text" id="education" name="education" class="form-control"
                               value="{{ old('education', $candidate->education) }}" maxlength="128">
                    </div>

                    <div class="col-md-4">
                        <label for="salary" class="form-label">الراتب (ريال)</label>
                        <input type="number" step="0.01" min="0" id="salary" name="salary" class="form-control"
                               value="{{ old('salary', $candidate->salary) }}">
                    </div>

                    <div class="col-md-4">
                        <label for="contract_price" class="form-label">قيمة العقد (ريال)</label>
                        <input type="number" step="0.01" min="0" id="contract_price" name="contract_price"
                               class="form-control" value="{{ old('contract_price', $candidate->contract_price) }}">
                    </div>
                </div>
            </div>
        </div>

        {{-- Languages & skills --}}
        <div class="admin-card mb-3">
            <div class="admin-card__header">
                <h2 class="admin-card__title">اللغات والمهارات</h2>
            </div>
            <div class="admin-card__body">
                <div class="row g-3">
                    <div class="col-md-4">
                        <label for="language_arabic" class="form-label">اللغة العربية <span class="required-mark">*</span></label>
                        <select id="language_arabic" name="language_arabic" class="form-select" required>
                            @foreach ($levels as $value => $label)
                                <option value="{{ $value }}" @selected(old('language_arabic', $candidate->language_arabic ?: 'none') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="language_english" class="form-label">اللغة الإنجليزية <span class="required-mark">*</span></label>
                        <select id="language_english" name="language_english" class="form-select" required>
                            @foreach ($levels as $value => $label)
                                <option value="{{ $value }}" @selected(old('language_english', $candidate->language_english ?: 'none') === $value)>
                                    {{ $label }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="col-md-4">
                        <label for="other_languages" class="form-label">لغات أخرى</label>
                        <input type="text" id="other_languages" name="other_languages" class="form-control"
                               value="{{ old('other_languages', $candidate->other_languages) }}" maxlength="255">
                    </div>

                    <div class="col-12">
                        <label for="skills" class="form-label">المهارات</label>
                        <input type="text" id="skills" name="skills" class="form-control"
                               value="{{ $skillsValue }}" placeholder="افصل بين المهارات بفاصلة">
                        <div class="form-text">مثال: تنظيف المنزل، الطبخ، كي الملابس</div>
                    </div>

                    <div class="col-12">
                        <label for="previous_countries" class="form-label">دول الخبرة السابقة</label>
                        <input type="text" id="previous_countries" name="previous_countries" class="form-control"
                               value="{{ $countriesValue }}" placeholder="افصل بين الدول بفاصلة">
                    </div>

                    <div class="col-12">
                        <label for="description" class="form-label">نبذة</label>
                        <textarea id="description" name="description" rows="4" class="form-control"
                                  maxlength="5000">{{ old('description', $candidate->description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>

        {{-- SEO --}}
        <div class="admin-card">
            <div class="admin-card__header">
                <h2 class="admin-card__title">تحسين الظهور (SEO)</h2>
            </div>
            <div class="admin-card__body">
                <div class="row g-3">
                    <div class="col-12">
                        <label for="meta_title" class="form-label">عنوان الميتا</label>
                        <input type="text" id="meta_title" name="meta_title" class="form-control"
                               value="{{ old('meta_title', $candidate->meta_title) }}" maxlength="255">
                    </div>
                    <div class="col-12">
                        <label for="meta_description" class="form-label">وصف الميتا</label>
                        <textarea id="meta_description" name="meta_description" rows="2" class="form-control"
                                  maxlength="500">{{ old('meta_description', $candidate->meta_description) }}</textarea>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Sidebar: status + files --}}
    <div class="col-lg-4">
        <div class="admin-card mb-3">
            <div class="admin-card__header">
                <h2 class="admin-card__title">الحالة</h2>
            </div>
            <div class="admin-card__body">
                <div class="mb-3">
                    <label for="availability_status" class="form-label">حالة التوفر <span class="required-mark">*</span></label>
                    <select id="availability_status" name="availability_status" class="form-select" required>
                        @foreach ($availabilityOptions as $value => $label)
                            <option value="{{ $value }}"
                                @selected(old('availability_status', $candidate->availability_status?->value ?? 'available') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="mb-3">
                    <label for="sort_order" class="form-label">ترتيب العرض</label>
                    <input type="number" id="sort_order" name="sort_order" min="0" class="form-control"
                           value="{{ old('sort_order', $candidate->sort_order ?? 0) }}">
                </div>

                <div class="form-check mb-2">
                    <input type="checkbox" class="form-check-input" id="featured" name="featured" value="1"
                           @checked(old('featured', $candidate->featured))>
                    <label class="form-check-label" for="featured">سيرة مميزة</label>
                </div>

                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $candidate->exists ? $candidate->is_active : true))>
                    <label class="form-check-label" for="is_active">مفعّلة (تظهر في الموقع)</label>
                </div>
            </div>
        </div>

        <div class="admin-card">
            <div class="admin-card__header">
                <h2 class="admin-card__title">الملفات</h2>
            </div>
            <div class="admin-card__body">
                <div class="mb-3">
                    <label for="profile_image" class="form-label">الصورة الشخصية</label>
                    <input type="file" id="profile_image" name="profile_image"
                           class="form-control @error('profile_image') is-invalid @enderror"
                           accept=".jpg,.jpeg,.png,.webp">
                    <div class="form-text">jpg, jpeg, png, webp — بحد أقصى 4 ميجابايت.</div>
                    @error('profile_image')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($candidate->profile_image)
                        <img src="{{ $candidate->profile_image_url }}" alt="" class="file-preview">
                    @endif
                </div>

                <div class="mb-3">
                    <label for="cv_file" class="form-label">ملف السيرة (PDF)</label>
                    <input type="file" id="cv_file" name="cv_file"
                           class="form-control @error('cv_file') is-invalid @enderror" accept=".pdf">
                    <div class="form-text">pdf فقط — بحد أقصى 10 ميجابايت.</div>
                    @error('cv_file')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($candidate->cv_file_url)
                        <a href="{{ $candidate->cv_file_url }}" target="_blank" rel="noopener" class="d-block mt-2 small">
                            عرض الملف الحالي
                        </a>
                    @endif
                </div>

                <div class="mb-0">
                    <label for="intro_video" class="form-label">الفيديو التعريفي</label>
                    <input type="file" id="intro_video" name="intro_video"
                           class="form-control @error('intro_video') is-invalid @enderror" accept="video/mp4,video/webm">
                    <div class="form-text">mp4 أو webm — بحد أقصى 50 ميجابايت.</div>
                    @error('intro_video')<div class="invalid-feedback">{{ $message }}</div>@enderror
                    @if ($candidate->intro_video_url)
                        <a href="{{ $candidate->intro_video_url }}" target="_blank" rel="noopener" class="d-block mt-2 small">
                            عرض الفيديو الحالي
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-4">
    <button type="submit" class="btn btn-primary">
        حفظ
        <span class="btn-spinner" aria-hidden="true"></span>
    </button>
    <a href="{{ route('admin.candidates.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
