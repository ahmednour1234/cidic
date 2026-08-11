@extends('layouts.admin')

@section('title', 'الخدمات')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">الخدمات ({{ $services->total() }})</h2>
            <a href="{{ route('admin.services.create') }}" class="btn btn-primary btn-sm">إضافة خدمة</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الترتيب</th>
                        <th>العنوان</th>
                        <th>الرابط</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($services as $service)
                        <tr>
                            <td>{{ $service->sort_order }}</td>
                            <td>{{ $service->title }}</td>
                            <td dir="ltr" class="small text-muted-soft">{{ $service->slug }}</td>
                            <td>
                                <span class="badge-soft {{ $service->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $service->is_active ? 'مفعّلة' : 'معطّلة' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.services.edit', $service) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.services.destroy', $service) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد خدمات حتى الآن.', 'colspan' => 5])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($services->hasPages())
            <div class="admin-card__body">{{ $services->links() }}</div>
        @endif
    </div>
@endsection
