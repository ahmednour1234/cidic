@extends('layouts.admin')

@section('title', 'تفاصيل المستخدم')

@section('content')
    <div class="row justify-content-center">
        <div class="col-lg-7">
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">{{ $user->name }}</h2>
                    <span class="badge-soft bg-{{ $user->role->badge() }}-subtle text-{{ $user->role->badge() }}-emphasis">
                        {{ $user->role->label() }}
                    </span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">البريد الإلكتروني</span>
                            <strong dir="ltr">{{ $user->email }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">رقم الجوال</span>
                            <strong dir="ltr">{{ $user->phone ?: '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">الحالة</span>
                            <strong>{{ $user->is_active ? 'نشط' : 'معطّل' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">آخر دخول</span>
                            <strong>{{ $user->last_login_at?->format('Y-m-d H:i') ?? '—' }}</strong>
                        </div>
                    </div>

                    <div class="mt-4">
                        <a href="{{ route('admin.users.edit', $user) }}" class="btn btn-primary">تعديل</a>
                        <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">رجوع</a>
                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
