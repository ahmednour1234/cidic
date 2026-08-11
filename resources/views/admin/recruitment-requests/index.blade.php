@extends('layouts.admin')

@section('title', 'طلبات الاستقدام')

@section('content')
    <div class="admin-card mb-3">
        <div class="admin-card__body">
            <form method="GET" action="{{ route('admin.recruitment-requests.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="q" class="form-label">بحث</label>
                    <input type="search" id="q" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}"
                           placeholder="رقم الطلب، الاسم، الجوال">
                </div>
                <div class="col-md-4">
                    <label for="status" class="form-label">الحالة</label>
                    <select id="status" name="status" class="form-select">
                        <option value="">الكل</option>
                        @foreach ($statusOptions as $value => $label)
                            <option value="{{ $value }}" @selected(($filters['status'] ?? '') === $value)>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">بحث</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">طلبات الاستقدام ({{ $requests->total() }})</h2>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>رقم الطلب</th>
                        <th>الاسم</th>
                        <th>الجوال</th>
                        <th>الخدمة</th>
                        <th>الجنسية</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($requests as $request)
                        <tr>
                            <td dir="ltr">
                                <a href="{{ route('admin.recruitment-requests.show', $request) }}">
                                    {{ $request->request_number }}
                                </a>
                            </td>
                            <td>{{ $request->name }}</td>
                            <td dir="ltr">{{ $request->mobile }}</td>
                            <td>{{ $request->service?->title ?? '—' }}</td>
                            <td>{{ $request->nationality?->name_ar ?? '—' }}</td>
                            <td>
                                <span class="badge-soft bg-{{ $request->status->badge() }}-subtle text-{{ $request->status->badge() }}-emphasis">
                                    {{ $request->status->label() }}
                                </span>
                            </td>
                            <td class="small text-muted-soft">{{ $request->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <a href="{{ route('admin.recruitment-requests.show', $request) }}"
                                   class="btn btn-sm btn-outline-primary">عرض</a>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد طلبات حتى الآن.', 'colspan' => 8])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($requests->hasPages())
            <div class="admin-card__body">{{ $requests->links() }}</div>
        @endif
    </div>
@endsection
