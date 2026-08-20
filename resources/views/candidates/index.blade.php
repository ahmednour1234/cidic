@extends('layouts.app')

@section('meta_title', 'السير الذاتية المتاحة | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'تصفح السير الذاتية المتوفرة للعمالة المنزلية واختر العاملة المناسبة لاحتياجاتك.')

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>السير الذاتية المتاحة</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">السير الذاتية</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4">
                {{-- ---------------- Filters ---------------- --}}
                <div class="col-lg-3">
                    <form method="GET" action="{{ route('candidates.index') }}" class="filter-card">
                        <h2 class="h6 mb-3">تصفية النتائج</h2>

                        <div class="mb-3">
                            <label for="q" class="form-label">بحث</label>
                            <input type="search" id="q" name="q" class="form-control"
                                   value="{{ $filters['q'] ?? '' }}"
                                   placeholder="الاسم أو رقم السيرة">
                        </div>

                        <div class="mb-3">
                            <label for="nationality" class="form-label">الجنسية</label>
                            <select id="nationality" name="nationality" class="form-select">
                                <option value="">كل الجنسيات</option>
                                @foreach ($nationalities as $nationality)
                                    <option value="{{ $nationality->slug }}"
                                        @selected(($filters['nationality'] ?? '') == $nationality->slug)>
                                        {{ $nationality->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="category" class="form-label">المهنة</label>
                            <select id="category" name="category" class="form-select">
                                <option value="">كل المهن</option>
                                @foreach ($categories as $category)
                                    <option value="{{ $category->slug }}"
                                        @selected(($filters['category'] ?? '') == $category->slug)>
                                        {{ $category->name_ar }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="experience" class="form-label">الخبرة (سنوات فأكثر)</label>
                            <select id="experience" name="experience" class="form-select">
                                <option value="">الكل</option>
                                @foreach ([1, 2, 3, 5, 8, 10] as $years)
                                    <option value="{{ $years }}" @selected(($filters['experience'] ?? '') == $years)>
                                        {{ $years }} سنوات فأكثر
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="row g-2 mb-3">
                            <div class="col-6">
                                <label for="age_min" class="form-label">العمر من</label>
                                <input type="number" id="age_min" name="age_min" class="form-control"
                                       min="18" max="70" value="{{ $filters['age_min'] ?? '' }}">
                            </div>
                            <div class="col-6">
                                <label for="age_max" class="form-label">إلى</label>
                                <input type="number" id="age_max" name="age_max" class="form-control"
                                       min="18" max="70" value="{{ $filters['age_max'] ?? '' }}">
                            </div>
                        </div>

                        <div class="mb-3">
                            <label for="language" class="form-label">اللغة</label>
                            <select id="language" name="language" class="form-select">
                                <option value="">الكل</option>
                                <option value="arabic" @selected(($filters['language'] ?? '') === 'arabic')>العربية</option>
                                <option value="english" @selected(($filters['language'] ?? '') === 'english')>الإنجليزية</option>
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="availability" class="form-label">حالة التوفر</label>
                            <select id="availability" name="availability" class="form-select">
                                <option value="">الكل</option>
                                @foreach (\App\Enums\AvailabilityStatus::options() as $value => $label)
                                    <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="d-grid gap-2">
                            <button type="submit" class="btn btn-primary">تطبيق</button>
                            <a href="{{ route('candidates.index') }}" class="btn btn-outline-secondary">إعادة تعيين</a>
                        </div>
                    </form>
                </div>

                {{-- ---------------- Results ---------------- --}}
                <div class="col-lg-9">
                    <div class="d-flex flex-wrap align-items-center justify-content-between gap-3 mb-4">
                        <p class="mb-0 text-muted-soft">
                            عدد النتائج: <strong>{{ $candidates->total() }}</strong>
                        </p>

                        <form method="GET" action="{{ route('candidates.index') }}" class="d-flex align-items-center gap-2">
                            {{-- Preserve active filters when changing the sort. --}}
                            @foreach (($filters ?? []) as $key => $value)
                                @if ($key !== 'sort' && filled($value))
                                    <input type="hidden" name="{{ $key }}" value="{{ $value }}">
                                @endif
                            @endforeach

                            <label for="sort" class="form-label mb-0 text-nowrap">ترتيب حسب</label>
                            <select id="sort" name="sort" class="form-select" onchange="this.form.submit()">
                                <option value="newest" @selected(($filters['sort'] ?? '') === 'newest')>الأحدث</option>
                                <option value="experience" @selected(($filters['sort'] ?? '') === 'experience')>الخبرة</option>
                                <option value="age_asc" @selected(($filters['sort'] ?? '') === 'age_asc')>العمر (تصاعدي)</option>
                                <option value="age_desc" @selected(($filters['sort'] ?? '') === 'age_desc')>العمر (تنازلي)</option>
                            </select>
                            <noscript><button type="submit" class="btn btn-primary btn-sm">ترتيب</button></noscript>
                        </form>
                    </div>

                    @if ($candidates->isNotEmpty())
                        <div class="row g-4">
                            @foreach ($candidates as $candidate)
                                <div class="col-12 col-sm-6 col-xl-4">
                                    <x-candidate-card :candidate="$candidate" />
                                </div>
                            @endforeach
                        </div>

                        <div class="mt-5 d-flex justify-content-center">
                            {{ $candidates->links() }}
                        </div>
                    @else
                        <div class="empty-state">
                            <div class="empty-state__icon" aria-hidden="true">&#128269;</div>
                            <h2 class="h5">لا توجد سير ذاتية مطابقة لخيارات البحث.</h2>
                            <p class="text-muted-soft mb-3">جرّب تعديل خيارات التصفية أو إعادة تعيين البحث.</p>
                            <a href="{{ route('candidates.index') }}" class="btn btn-outline-primary">إعادة تعيين البحث</a>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection

@push('scripts')
    @vite('resources/js/cv-thumbs.js')
@endpush
