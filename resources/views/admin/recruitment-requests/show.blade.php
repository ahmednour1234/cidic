@extends('layouts.admin')

@section('title', 'تفاصيل طلب الاستقدام ' . $request->request_number)

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="admin-card mb-3">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">بيانات الطلب</h2>
                    <span class="badge-soft bg-{{ $request->status->badge() }}-subtle text-{{ $request->status->badge() }}-emphasis">
                        {{ $request->status->label() }}
                    </span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3">
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">رقم الطلب</span>
                            <strong dir="ltr">{{ $request->request_number }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">تاريخ الطلب</span>
                            <strong>{{ $request->created_at->format('Y-m-d H:i') }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">الاسم</span>
                            <strong>{{ $request->name }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">الجوال</span>
                            <strong dir="ltr"><a href="{{ tel_url($request->mobile) }}">{{ $request->mobile }}</a></strong>
                        </div>
                        @if ($request->whatsapp)
                            <div class="col-md-6">
                                <span class="d-block small text-muted-soft">واتساب</span>
                                <strong dir="ltr">
                                    <a href="{{ whatsapp_url(null, $request->whatsapp) }}" target="_blank" rel="noopener">
                                        {{ $request->whatsapp }}
                                    </a>
                                </strong>
                            </div>
                        @endif
                        @if ($request->email)
                            <div class="col-md-6">
                                <span class="d-block small text-muted-soft">البريد</span>
                                <strong dir="ltr">{{ $request->email }}</strong>
                            </div>
                        @endif
                        @if ($request->city)
                            <div class="col-md-6">
                                <span class="d-block small text-muted-soft">المدينة</span>
                                <strong>{{ $request->city }}</strong>
                            </div>
                        @endif
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">الخدمة</span>
                            <strong>{{ $request->service?->title ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">الجنسية المطلوبة</span>
                            <strong>{{ $request->nationality?->name_ar ?? '—' }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">تصنيف العمالة</span>
                            <strong>{{ $request->category?->name_ar ?? '—' }}</strong>
                        </div>
                    </div>

                    @if ($request->notes)
                        <hr>
                        <span class="d-block small text-muted-soft mb-1">ملاحظات العميل</span>
                        <p class="mb-0">{{ $request->notes }}</p>
                    @endif
                </div>
            </div>

            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">ملاحظات داخلية</h2>
                </div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.recruitment-requests.update', $request) }}">
                        @csrf
                        @method('PUT')

                        <div class="mb-3">
                            <label for="assigned_to" class="form-label">المسؤول عن المتابعة</label>
                            <select id="assigned_to" name="assigned_to" class="form-select">
                                <option value="">غير محدد</option>
                                @foreach ($staff as $member)
                                    <option value="{{ $member->id }}" @selected($request->assigned_to == $member->id)>
                                        {{ $member->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">ملاحظات</label>
                            <textarea id="admin_notes" name="admin_notes" rows="4" class="form-control"
                                      maxlength="5000">{{ old('admin_notes', $request->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary">حفظ</button>
                    </form>
                </div>
            </div>
        </div>

        <div class="col-lg-5">
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">تغيير الحالة</h2>
                </div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.recruitment-requests.status', $request) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="status" class="form-label">الحالة الجديدة</label>
                            <select id="status" name="status" class="form-select" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($request->status->value === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">تحديث الحالة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
