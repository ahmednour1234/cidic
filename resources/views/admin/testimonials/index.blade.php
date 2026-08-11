@extends('layouts.admin')

@section('title', 'آراء العملاء')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">الآراء ({{ $records->total() }})</h2>
            <a href="{{ route('admin.testimonials.create') }}" class="btn btn-primary btn-sm">إضافة رأي</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الصورة</th>
                        <th>الاسم</th>
                        <th>المدينة</th>
                        <th>التقييم</th>
                        <th>الرأي</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>
                                @if ($record->avatar_url)
                                    <img src="{{ $record->avatar_url }}" alt="" class="admin-thumb">
                                @else
                                    <span class="text-muted-soft">—</span>
                                @endif
                            </td>
                            <td>{{ $record->name }}</td>
                            <td>{{ $record->city ?: '—' }}</td>
                            <td style="color: #f0a92a;">{{ str_repeat('★', max(0, min(5, (int) $record->rating))) }}</td>
                            <td class="text-wrap" style="white-space: normal; max-width: 320px;">
                                {{ Str::limit($record->review, 90) }}
                            </td>
                            <td>
                                <span class="badge-soft {{ $record->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $record->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.testimonials.edit', $record) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.testimonials.destroy', $record) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد آراء حتى الآن.', 'colspan' => 7])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="admin-card__body">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
