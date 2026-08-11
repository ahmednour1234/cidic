@extends('layouts.admin')

@section('title', 'الرئيسية')

@section('content')
    <div class="row g-3 mb-4">
        @php
            $tiles = [
                ['label' => 'إجمالي السير الذاتية', 'value' => $stats['candidates_total'], 'icon' => '&#9776;'],
                ['label' => 'العاملات المتاحات', 'value' => $stats['candidates_available'], 'icon' => '&#10003;'],
                ['label' => 'العاملات المحجوزات', 'value' => $stats['candidates_reserved'], 'icon' => '&#9203;'],
                ['label' => 'طلبات جديدة', 'value' => $stats['requests_new'], 'icon' => '&#9993;'],
                ['label' => 'طلبات هذا الشهر', 'value' => $stats['requests_month'], 'icon' => '&#128197;'],
                ['label' => 'رسائل التواصل', 'value' => $stats['messages'], 'icon' => '&#9990;'],
                ['label' => 'الخدمات', 'value' => $stats['services'], 'icon' => '&#9881;'],
                ['label' => 'الجنسيات', 'value' => $stats['nationalities'], 'icon' => '&#9873;'],
            ];
        @endphp

        @foreach ($tiles as $tile)
            <div class="col-6 col-lg-3">
                <div class="admin-stat">
                    <span class="admin-stat__icon" aria-hidden="true">{!! $tile['icon'] !!}</span>
                    <span>
                        <span class="admin-stat__value d-block">{{ number_format($tile['value']) }}</span>
                        <span class="admin-stat__label">{{ $tile['label'] }}</span>
                    </span>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3 mb-4">
        <div class="col-lg-7">
            <div class="admin-card h-100">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">الطلبات خلال آخر 6 أشهر</h2>
                </div>
                <div class="admin-card__body">
                    <div class="mini-chart">
                        @foreach ($monthlyChart as $bar)
                            <div class="mini-chart__col">
                                <span class="mini-chart__value">{{ $bar['value'] }}</span>
                                <div class="mini-chart__bar" style="height: {{ max($bar['height'], 3) }}%"></div>
                                <span class="mini-chart__label">{{ $bar['label'] }}</span>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="admin-card h-100">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">السير حسب الجنسية</h2>
                </div>
                <div class="admin-card__body">
                    @if (collect($nationalityChart)->sum('value') === 0)
                        <p class="text-muted-soft mb-0">لا توجد بيانات كافية لعرضها.</p>
                    @else
                        <div class="mini-chart">
                            @foreach ($nationalityChart as $bar)
                                <div class="mini-chart__col">
                                    <span class="mini-chart__value">{{ $bar['value'] }}</span>
                                    <div class="mini-chart__bar" style="height: {{ max($bar['height'], 3) }}%"></div>
                                    <span class="mini-chart__label" title="{{ $bar['label'] }}">{{ $bar['label'] }}</span>
                                </div>
                            @endforeach
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </div>

    <div class="row g-3">
        <div class="col-xl-7">
            <div class="admin-card h-100">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">أحدث الطلبات</h2>
                    <a href="{{ route('admin.candidate-requests.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>رقم الطلب</th>
                                <th>العميل</th>
                                <th>العاملة</th>
                                <th>الحالة</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentRequests as $request)
                                <tr>
                                    <td>
                                        <a href="{{ route('admin.candidate-requests.show', $request) }}" dir="ltr">
                                            {{ $request->request_number }}
                                        </a>
                                    </td>
                                    <td>
                                        {{ $request->customer_name }}
                                        <span class="d-block small text-muted-soft" dir="ltr">{{ $request->mobile }}</span>
                                    </td>
                                    <td>
                                        @if ($request->candidate)
                                            {{ $request->candidate->name }}
                                            <span class="d-block small text-muted-soft" dir="ltr">
                                                {{ $request->candidate->reference_number }}
                                            </span>
                                        @else
                                            <span class="text-muted-soft">—</span>
                                        @endif
                                    </td>
                                    <td>
                                        <span class="badge-soft bg-{{ $request->status->badge() }}-subtle text-{{ $request->status->badge() }}-emphasis">
                                            {{ $request->status->label() }}
                                        </span>
                                    </td>
                                    <td class="small text-muted-soft">{{ $request->created_at->format('Y-m-d') }}</td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="5" class="text-center text-muted-soft py-4">لا توجد طلبات حتى الآن.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-5">
            <div class="admin-card h-100">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">أحدث السير الذاتية</h2>
                    <a href="{{ route('admin.candidates.index') }}" class="btn btn-sm btn-outline-primary">عرض الكل</a>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>الصورة</th>
                                <th>الاسم</th>
                                <th>الجنسية</th>
                                <th>الحالة</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($recentCandidates as $candidate)
                                <tr>
                                    <td>
                                        <img src="{{ $candidate->profile_image_url }}" alt="" class="admin-thumb">
                                    </td>
                                    <td>
                                        <a href="{{ route('admin.candidates.edit', $candidate) }}">{{ $candidate->name }}</a>
                                        <span class="d-block small text-muted-soft" dir="ltr">{{ $candidate->reference_number }}</span>
                                    </td>
                                    <td>{{ $candidate->nationality?->name_ar ?? '—' }}</td>
                                    <td>
                                        <span class="badge-soft bg-{{ $candidate->availability_status->badge() }}-subtle text-{{ $candidate->availability_status->badge() }}-emphasis">
                                            {{ $candidate->availability_status->label() }}
                                        </span>
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="text-center text-muted-soft py-4">لا توجد سير ذاتية حتى الآن.</td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
@endsection
