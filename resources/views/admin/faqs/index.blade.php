@extends('layouts.admin')

@section('title', 'الأسئلة الشائعة')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">الأسئلة ({{ $records->total() }})</h2>
            <a href="{{ route('admin.faqs.create') }}" class="btn btn-primary btn-sm">إضافة سؤال</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>السؤال</th>
                        <th>الإجابة</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($records as $record)
                        <tr>
                            <td>{{ $record->sort_order }}</td>
                            <td class="text-wrap" style="white-space: normal; max-width: 280px;">{{ $record->question }}</td>
                            <td class="text-wrap" style="white-space: normal; max-width: 340px;">
                                {{ Str::limit($record->answer, 90) }}
                            </td>
                            <td>
                                <span class="badge-soft {{ $record->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $record->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.faqs.edit', $record) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.faqs.destroy', $record) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد أسئلة حتى الآن.', 'colspan' => 5])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($records->hasPages())
            <div class="admin-card__body">{{ $records->links() }}</div>
        @endif
    </div>
@endsection
