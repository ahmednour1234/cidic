@extends('layouts.admin')

@section('title', 'تفاصيل السيرة الذاتية')

@section('content')
    <div class="row g-4">
        <div class="col-lg-4">
            <div class="admin-card">
                <div class="admin-card__body text-center">
                    <img src="{{ $candidate->profile_image_url }}" alt=""
                         style="width: 100%; max-width: 240px; aspect-ratio: 3/4; object-fit: cover; border-radius: var(--radius);">

                    <h2 class="h5 mt-3 mb-1">{{ $candidate->name }}</h2>
                    <p class="text-muted-soft mb-2" dir="ltr">{{ $candidate->reference_number }}</p>

                    <span class="badge-soft bg-{{ $candidate->availability_status->badge() }}-subtle text-{{ $candidate->availability_status->badge() }}-emphasis">
                        {{ $candidate->availability_status->label() }}
                    </span>

                    <div class="d-grid gap-2 mt-3">
                        <a href="{{ route('admin.candidates.edit', $candidate) }}" class="btn btn-primary">تعديل</a>
                        <a href="{{ route('candidates.show', $candidate) }}" target="_blank" rel="noopener"
                           class="btn btn-outline-secondary">معاينة في الموقع</a>
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-8">
            <div class="admin-card mb-3">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">البيانات</h2>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        @php
                            $rows = [
                                'الجنسية' => $candidate->nationality?->name_ar,
                                'التصنيف' => $candidate->category?->name_ar,
                                'المهنة' => $candidate->profession,
                                'العمر' => $candidate->display_age ? $candidate->display_age . ' سنة' : null,
                                'سنوات الخبرة' => $candidate->years_of_experience . ' سنوات',
                                'اللغات' => $candidate->languages_label,
                                'الديانة' => $candidate->religion,
                                'المؤهل' => $candidate->education,
                                'الراتب' => $candidate->salary ? number_format((float) $candidate->salary) . ' ريال' : null,
                                'قيمة العقد' => $candidate->contract_price ? number_format((float) $candidate->contract_price) . ' ريال' : null,
                            ];
                        @endphp

                        @foreach (array_filter($rows) as $label => $value)
                            <div class="col-md-6">
                                <span class="d-block small text-muted-soft">{{ $label }}</span>
                                <strong>{{ $value }}</strong>
                            </div>
                        @endforeach
                    </div>

                    @if (filled($candidate->skills))
                        <hr>
                        <span class="d-block small text-muted-soft mb-2">المهارات</span>
                        @foreach ((array) $candidate->skills as $skill)
                            <span class="skill-chip">{{ $skill }}</span>
                        @endforeach
                    @endif

                    @if ($candidate->description)
                        <hr>
                        <span class="d-block small text-muted-soft mb-1">نبذة</span>
                        <p class="mb-0">{{ $candidate->description }}</p>
                    @endif
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">الطلبات على هذه السيرة ({{ $candidate->requests->count() }})</h2>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>الجوال</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($candidate->requests as $request)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.candidate-requests.show', $request) }}" dir="ltr">
                                            {{ $request->request_number }}
                                        </a>
                                    </td>
                                    <td>{{ $request->customer_name }}</td>
                                    <td dir="ltr">{{ $request->mobile }}</td>
                                    <td>
                                        <span class="badge-soft bg-{{ $request->status->badge() }}-subtle text-{{ $request->status->badge() }}-emphasis">
                                            {{ $request->status->label() }}
                                        </span>
                                    </td>
                                    <td class="small text-muted-soft">{{ $request->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                @include('admin.partials.empty', ['message' => 'لا توجد طلبات على هذه السيرة.', 'colspan' => 5])
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
