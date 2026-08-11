@extends('layouts.admin')

@section('title', 'صفحات الموقع')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">الصفحات ({{ $pages->total() }})</h2>
            <a href="{{ route('admin.pages.create') }}" class="btn btn-primary btn-sm">إضافة صفحة</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>العنوان</th>
                        <th>الرابط</th>
                        <th>الحالة</th>
                        <th>آخر تحديث</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($pages as $page)
                        <tr>
                            <td>{{ $page->title }}</td>
                            <td dir="ltr" class="small text-muted-soft">{{ $page->slug }}</td>
                            <td>
                                <span class="badge-soft {{ $page->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $page->is_active ? 'مفعّلة' : 'معطّلة' }}
                                </span>
                            </td>
                            <td class="small text-muted-soft">{{ $page->updated_at?->format('Y-m-d') }}</td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.pages.edit', $page) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.pages.destroy', $page) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد صفحات حتى الآن.', 'colspan' => 5])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($pages->hasPages())
            <div class="admin-card__body">{{ $pages->links() }}</div>
        @endif
    </div>
@endsection
