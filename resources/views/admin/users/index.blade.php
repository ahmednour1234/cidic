@extends('layouts.admin')

@section('title', 'المستخدمون')

@section('content')
    <div class="admin-card mb-3">
        <div class="admin-card__body">
            <form method="GET" action="{{ route('admin.users.index') }}" class="row g-2 align-items-end">
                <div class="col-md-9">
                    <label for="q" class="form-label">بحث</label>
                    <input type="search" id="q" name="q" class="form-control"
                           value="{{ $filters['q'] ?? '' }}" placeholder="الاسم أو البريد الإلكتروني">
                </div>
                <div class="col-md-3 d-grid">
                    <button type="submit" class="btn btn-primary">بحث</button>
                </div>
            </form>
        </div>
    </div>

    <div class="admin-card">
        <div class="admin-card__header">
            <h2 class="admin-card__title">المستخدمون ({{ $users->total() }})</h2>
            <a href="{{ route('admin.users.create') }}" class="btn btn-primary btn-sm">إضافة مستخدم</a>
        </div>

        <div class="table-responsive">
            <table class="table admin-table">
                <thead>
                    <tr>
                        <th>الاسم</th>
                        <th>البريد الإلكتروني</th>
                        <th>الصلاحية</th>
                        <th>الحالة</th>
                        <th>آخر دخول</th>
                        <th>إجراءات</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($users as $user)
                        <tr>
                            <td>
                                {{ $user->name }}
                                @if ($user->id === auth()->id())
                                    <span class="badge bg-primary-subtle text-primary-emphasis ms-1">أنت</span>
                                @endif
                            </td>
                            <td dir="ltr">{{ $user->email }}</td>
                            <td>
                                <span class="badge-soft bg-{{ $user->role->badge() }}-subtle text-{{ $user->role->badge() }}-emphasis">
                                    {{ $user->role->label() }}
                                </span>
                            </td>
                            <td>
                                <span class="badge-soft {{ $user->is_active ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' }}">
                                    {{ $user->is_active ? 'نشط' : 'معطّل' }}
                                </span>
                            </td>
                            <td class="small text-muted-soft">
                                {{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}
                            </td>
                            <td>
                                <div class="d-flex gap-1">
                                    <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-sm btn-outline-primary">تعديل</a>
                                    @if ($user->id !== auth()->id())
                                        <form method="POST" action="{{ route('admin.users.destroy', $user) }}"
                                              onsubmit="return confirm('هل أنت متأكد من حذف هذا المستخدم؟');">
                                            @csrf
                                            @method('DELETE')
                                            <button type="submit" class="btn btn-sm btn-outline-danger">حذف</button>
                                        </form>
                                    @endif
                                </div>
                            </td>
                        </tr>
                    @empty
                        @include('admin.partials.empty', ['message' => 'لا يوجد مستخدمون.', 'colspan' => 6])
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($users->hasPages())
            <div class="admin-card__body">{{ $users->links() }}</div>
        @endif
    </div>
@endsection
