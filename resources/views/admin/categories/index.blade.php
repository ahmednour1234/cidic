@extends('layouts.admin')

@section('title', 'تصنيفات العمالة')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">التصنيفات ({{ $categories->total() }})</h2>
            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm">إضافة تصنيف</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>الاسم</th>
                        <th>الرابط</th>
                        <th>عدد السير</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($categories as $category)
                        <tr>
                            <td>{{ $category->sort_order }}</td>
                            <td>
                                {{ $category->name_ar }}
                                <span class="d-block small text-muted-soft" dir="ltr">{{ $category->name_en }}</span>
                            </td>
                            <td dir="ltr" class="small text-muted-soft">{{ $category->slug }}</td>
                            <td>{{ $category->candidates_count }}</td>
                            <td>
                                <span class="badge-soft {{ $category->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $category->is_active ? 'مفعّل' : 'معطّل' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.categories.edit', $category) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.categories.destroy', $category) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد تصنيفات حتى الآن.', 'colspan' => 6])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($categories->hasPages())
            <div class="admin-card__body">{{ $categories->links() }}</div>
        @endif
    </div>
@endsection
