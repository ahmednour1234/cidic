@extends('layouts.admin')

@section('title', 'كيف نعمل')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">خطوات العمل ({{ $records->total() }})</h2>
            <a href="{{ route('admin.how-it-works.create') }}" class="btn btn-primary btn-sm">إضافة خطوة</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>العنوان</th>
                        <th>الوصف</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->sort_order }}</td>
                            <td>{{ $record->title }}</td>
                            <td class="text-wrap" style="white-space: normal; max-width: 380px;">
                                {{ Str::limit($record->description, 90) }}
                            </td>
                            <td>
                                <span class="badge-soft {{ $record->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $record->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.how-it-works.edit', $record) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.how-it-works.destroy', $record) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد خطوات حتى الآن.', 'colspan' => 5])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="admin-card__body">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
