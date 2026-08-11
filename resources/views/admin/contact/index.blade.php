@extends('layouts.admin')

@section('title', 'رسائل التواصل')

@section('content')
    <div class="admin-card mb-3">
        <div class="admin-card__body">
            <form method="GET" action="{{ route('admin.contact-messages.index') }}" class="row g-2 align-items-end">
                <div class="col-md-5">
                    <label for="q" class="form-label">بحث</label>
                    <input type="search" id="q" name="q" class="form-control" value="{{ $filters['q'] ?? '' }}"
                           placeholder="الاسم، الجوال، البريد، الموضوع">
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
            <h2 class="admin-card__title">الرسائل ({{ $messages->total() }})</h2>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>الجوال</th>
                        <th>الموضوع</th>
                        <th>الحالة</th>
                        <th>التاريخ</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($messages as $message)
                        <tr>
                            <td>{{ $message->name }}</td>
                            <td dir="ltr">{{ $message->mobile }}</td>
                            <td>{{ $message->subject ?: '—' }}</td>
                            <td>
                                <span class="badge-soft bg-{{ $message->status->badge() }}-subtle text-{{ $message->status->badge() }}-emphasis">
                                    {{ $message->status->label() }}
                                </span>
                            </td>
                            <td class="small text-muted-soft">{{ $message->created_at->format('Y-m-d H:i') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.contact-messages.show', $message) }}"
                                       class="btn btn-sm btn-outline-primary">عرض</a>
                                    <form method="POST" action="{{ route('admin.contact-messages.destroy', $message) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد رسائل حتى الآن.', 'colspan' => 6])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($messages->hasPages())
            <div class="admin-card__body">{{ $messages->links() }}</div>
        @endif
    </div>
@endsection
