@extends('layouts.admin')

@section('title', 'الجنسيات')

@section('content')
    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">الجنسيات ({{ $nationalities->total() }})</h2>
            <a href="{{ route('admin.nationalities.create') }}" class="btn btn-primary btn-sm">إضافة جنسية</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>العلم</th>
                        <th>الاسم</th>
                        <th>الرابط</th>
                        <th>عدد السير</th>
                        <th>الحالة</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($nationalities as $nationality)
                        <tr>
                            <td>
                                @if ($nationality->flag_url)
                                    <img src="{{ $nationality->flag_url }}" alt="" class="admin-thumb">
                                @else
                                    <span class="text-muted-soft">—</span>
                                @endif
                            </td>
                            <td>
                                {{ $nationality->name_ar }}
                                <span class="d-block small text-muted-soft" dir="ltr">{{ $nationality->name_en }}</span>
                            </td>
                            <td dir="ltr" class="small text-muted-soft">{{ $nationality->slug }}</td>
                            <td>{{ $nationality->candidates_count }}</td>
                            <td>
                                <span class="badge-soft {{ $nationality->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $nationality->is_active ? 'مفعّلة' : 'معطّلة' }}
                                </span>
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.nationalities.edit', $nationality) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    <form method="POST" action="{{ route('admin.nationalities.destroy', $nationality) }}"
                                          onsubmit="return confirm('هل أنت متأكد من الحذف؟');">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا توجد جنسيات حتى الآن.', 'colspan' => 6])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($nationalities->hasPages())
            <div class="admin-card__body">{{ $nationalities->links() }}</div>
        @endif
    </div>
@endsection
