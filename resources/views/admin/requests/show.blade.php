@extends('layouts.admin')

@section('title', 'تفاصيل الطلب ' . $request->request_number)

@section('content')
    <div class="row g-4">
        <div class="col-lg-7">
            <div class="admin-card mb-3">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">بيانات العميل</h2>
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
                            <strong>{{ $request->customer_name }}</strong>
                        </div>
                        <div class="col-md-6">
                            <span class="d-block small text-muted-soft">الجوال</span>
                            <strong dir="ltr">
                                <a href="{{ tel_url($request->mobile) }}">{{ $request->mobile }}</a>
                            </strong>
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
                        @if ($request->service_type)
                            <div class="col-md-6">
                                <span class="d-block small text-muted-soft">نوع الخدمة</span>
                                <strong>{{ $request->service_type }}</strong>
                            </div>
                        @endif
                    </div>

                    @if ($request->notes)
                        <hr>
                        <span class="d-block small text-muted-soft mb-1">ملاحظات العميل</span>
                        <p class="mb-0">{{ $request->notes }}</p>
                    @endif
                </div>
            </div>

            {{-- Internal notes / assignment --}}
            <div class="admin-card mb-3">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">ملاحظات داخلية</h2>
                </div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.candidate-requests.update', $request) }}">
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

            {{-- Status history --}}
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">سجل الحالات</h2>
                </div>
                <div class="table-responsive">
                    <table class="table admin-table">
                        <thead>
                            <tr>
                                <th>من</th>
                                <th>إلى</th>
                                <th>بواسطة</th>
                                <th>ملاحظات</th>
                                <th>التاريخ</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse ($request->statusHistories as $history)
                                <tr>
                                    <td>{{ $history->old_status?->label() ?? '—' }}</td>
                                    <td>
                                        <span class="badge-soft bg-{{ $history->new_status->badge() }}-subtle text-{{ $history->new_status->badge() }}-emphasis">
                                            {{ $history->new_status->label() }}
                                        </span>
                                    </td>
                                    <td>{{ $history->changedBy?->name ?? 'النظام' }}</td>
                                    <td class="text-wrap" style="white-space: normal; max-width: 260px;">
                                        {{ $history->notes ?: '—' }}
                                    </td>
                                    <td class="small text-muted-soft">{{ $history->created_at?->format('Y-m-d H:i') }}</td>
                                </tr>
                            @empty
                                @include('admin.partials.empty', ['message' => 'لا يوجد سجل حالات.', 'colspan' => 5])
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        {{-- Candidate + status change --}}
        <div class="col-lg-5">
            @if ($request->candidate)
                <div class="admin-card mb-3">
                    <div class="admin-card__header">
                        <h2 class="admin-card__title">العاملة المطلوبة</h2>
                    </div>
                    <div class="admin-card__body text-center">
                        <img src="{{ $request->candidate->profile_image_url }}" alt=""
                             style="width: 160px; aspect-ratio: 3/4; object-fit: cover; border-radius: var(--radius);">

                        <h3 class="h6 mt-3 mb-1">{{ $request->candidate->name }}</h3>
                        <p class="text-muted-soft small mb-2" dir="ltr">{{ $request->candidate->reference_number }}</p>
                        <p class="small mb-3">
                            {{ $request->candidate->nationality?->name_ar }} —
                            {{ $request->candidate->profession }}
                        </p>

                        <a href="{{ route('admin.candidates.show', $request->candidate) }}"
                           class="btn btn-sm btn-outline-primary">عرض السيرة</a>
                    </div>
                </div>
            @endif

            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">تغيير الحالة</h2>
                </div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.candidate-requests.status', $request) }}">
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

                        <div class="mb-3">
                            <label for="notes" class="form-label">ملاحظة على التغيير</label>
                            <textarea id="notes" name="notes" rows="3" class="form-control" maxlength="1000"></textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">تحديث الحالة</button>
                    </form>
                </div>
            </div>
        </div>
    </div>
@endsection
