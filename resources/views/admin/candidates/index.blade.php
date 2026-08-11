@extends('layouts.admin')

@section('title', 'السير الذاتية')

@section('content')
    <div class="admin-card mb-3">
        <div class="admin-card__body">
            <form method="GET" action="{{ route('admin.candidates.index') }}" class="row g-2 align-items-end">
                <div class="col-md-3">
                    <label for="q" class="form-label">بحث</label>
                    <input type="search" id="q" name="q" class="form-control"
                           value="{{ $filters['q'] ?? '' }}" placeholder="الاسم أو رقم السيرة">
                </div>

                <div class="col-md-2">
                    <label for="nationality" class="form-label">الجنسية</label>
                    <select id="nationality" name="nationality" class="form-select">
                        <option value="">الكل</option>
                        @foreach ($nationalities as $nationality)
                            <option value="{{ $nationality->id }}" @selected(($filters['nationality'] ?? '') == $nationality->id)>
                                {{ $nationality->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="category" class="form-label">التصنيف</label>
                    <select id="category" name="category" class="form-select">
                        <option value="">الكل</option>
                        @foreach ($categories as $category)
                            <option value="{{ $category->id }}" @selected(($filters['category'] ?? '') == $category->id)>
                                {{ $category->name_ar }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="availability" class="form-label">التوفر</label>
                    <select id="availability" name="availability" class="form-select">
                        <option value="">الكل</option>
                        @foreach (\App\Enums\AvailabilityStatus::options() as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['availability'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>

                <div class="col-md-2">
                    <label for="status" class="form-label">الحالة</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">الكل</option>
                        <option value="active" @selected(($filters['status'] ?? '') === 'active')>مفعّلة</option>
                        <option value="inactive" @selected(($filters['status'] ?? '') === 'inactive')>معطّلة</option>
                    </select>
                </div>

                <div class="col-md-1 d-grid">
                    <button type="submit" class="btn btn-primary">بحث</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">السير الذاتية ({{ $candidates->total() }})</h2>
            <div class="d-flex gap-2">
                <a href="{{ route('admin.candidates.bulk') }}" class="btn btn-outline-primary btn-sm">
                    رفع سير متعددة
                </a>
                <a href="{{ route('admin.candidates.create') }}" class="btn btn-primary btn-sm">إضافة سيرة</a>
            </div>
        </div>

        <div class="table-responsive">
            <table class="table admin-table align-middle">
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>الرقم</th>
                        <th>الاسم</th>
                        <th>الجنسية</th>
                        <th>التصنيف</th>
                        <th>التوفر</th>
                        <th>مميزة</th>
                        <th>التاريخ</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($candidates as $candidate)
                        <tr>
                            <td><img src="{{ $candidate->profile_image_url }}" alt="" class="admin-thumb"></td>
                            <td dir="ltr">{{ $candidate->reference_number }}</td>
                            <td>
                                <a href="{{ route('admin.candidates.show', $candidate) }}">{{ $candidate->name }}</a>
                                @unless ($candidate->is_active)
                                    <span class="badge bg-secondary ms-1">معطّلة</span>
                                @endunless
                            </td>
                            <td>{{ $candidate->nationality?->name_ar ?? '—' }}</td>
                            <td>{{ $candidate->category?->name_ar ?? '—' }}</td>
                            <td>
                                <form method="POST" action="{{ route('admin.candidates.availability', $candidate) }}">
                                    @csrf
                                    @method('PATCH')
                                    <select name="availability_status" class="form-select form-select-sm"
                                            onchange="this.form.submit()" style="min-width: 140px;">
                                        @foreach (\App\Enums\AvailabilityStatus::options() as $value => $label)
                                            <option value="{{ $value }}" @selected($candidate->availability_status->value === $value)>
                                                {{ $label }}
                                            </option>
                                        @endforeach
                                    </select>
                                </form>
                            </td>
                            <td>
                                <form method="POST" action="{{ route('admin.candidates.toggle-featured', $candidate) }}">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="btn btn-sm {{ $candidate->featured ? 'btn-warning' : 'btn-outline-secondary' }}"
                                            title="{{ $candidate->featured ? 'إزالة من المميزة' : 'إضافة للمميزة' }}">
                                        ★
                                    </button>
                                </form>
                            </td>
                            <td class="small text-muted-soft">{{ $candidate->created_at->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('candidates.show', $candidate) }}" target="_blank" rel="noopener"
                                       class="btn btn-sm btn-outline-secondary" title="معاينة">عرض</a>
                                    <a href="{{ route('admin.candidates.edit', $candidate) }}"
                                       class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.candidates.destroy', $candidate) }}"
                                          onsubmit="return confirm('هل أنت متأكد من حذف هذه السيرة؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد سير ذاتية حتى الآن.', 'colspan' => 9])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($candidates->hasPages())
            <div class="admin-card__body">
                {{ $candidates->links() }}
            </div>
        @endif
    </div>
@endsection
