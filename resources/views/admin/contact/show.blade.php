@extends('layouts.admin')

@section('title', 'تفاصيل الرسالة')

@section('content')
    <div class="row g-4">
        <div class="col-lg-8">
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">{{ $message->subject ?: 'رسالة تواصل' }}</h2>
                    <span class="badge-soft bg-{{ $message->status->badge() }}-subtle text-{{ $message->status->badge() }}-emphasis">
                        {{ $message->status->label() }}
                    </span>
                </div>
                <div class="admin-card__body">
                    <div class="row g-3 mb-3">
                        <div class="col-md-4">
                            <span class="d-block small text-muted-soft">الاسم</span>
                            <strong>{{ $message->name }}</strong>
                        </div>
                        <div class="col-md-4">
                            <span class="d-block small text-muted-soft">الجوال</span>
                            <strong dir="ltr"><a href="{{ tel_url($message->mobile) }}">{{ $message->mobile }}</a></strong>
                        </div>
                        @if ($message->email)
                            <div class="col-md-4">
                                <span class="d-block small text-muted-soft">البريد</span>
                                <strong dir="ltr"><a href="mailto:{{ $message->email }}">{{ $message->email }}</a></strong>
                            </div>
                        @endif
                        <div class="col-md-4">
                            <span class="d-block small text-muted-soft">التاريخ</span>
                            <strong>{{ $message->created_at->format('Y-m-d H:i') }}</strong>
                        </div>
                    </div>

                    <hr>

                    <span class="d-block small text-muted-soft mb-2">نص الرسالة</span>
                    <p class="mb-0" style="line-height: 2; white-space: pre-line;">{{ $message->message }}</p>
                </div>
            </div>
        </div>

        <div class="col-lg-4">
            <div class="admin-card">
                <div class="admin-card__header">
                    <h2 class="admin-card__title">الحالة والملاحظات</h2>
                </div>
                <div class="admin-card__body">
                    <form method="POST" action="{{ route('admin.contact-messages.status', $message) }}">
                        @csrf
                        @method('PATCH')

                        <div class="mb-3">
                            <label for="status" class="form-label">الحالة</label>
                            <select id="status" name="status" class="form-select" required>
                                @foreach ($statusOptions as $value => $label)
                                    <option value="{{ $value }}" @selected($message->status->value === $value)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>

                        <div class="mb-3">
                            <label for="admin_notes" class="form-label">ملاحظات داخلية</label>
                            <textarea id="admin_notes" name="admin_notes" rows="4" class="form-control"
                                      maxlength="5000">{{ old('admin_notes', $message->admin_notes) }}</textarea>
                        </div>

                        <button type="submit" class="btn btn-primary w-100">حفظ</button>
                    </form>

                    @if (setting('whatsapp'))
                        <a href="{{ whatsapp_url('السلام عليكم ' . $message->name . '، بخصوص رسالتك.', $message->mobile) }}"
                           target="_blank" rel="noopener" class="btn btn-outline-secondary w-100 mt-2">
                            الرد عبر واتساب
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
@endsection
