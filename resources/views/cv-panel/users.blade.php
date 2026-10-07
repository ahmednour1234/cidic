@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.users'))

@php
    use App\Enums\Department;

    // Tint per department, so a role is recognisable at a glance.
    $deptStyle = [
        Department::Coordination->value   => ['#e6f0f9', '#0060a8'],
        Department::CustomerService->value => ['#e8f6ef', '#1a9d63'],
        Department::BranchManager->value  => ['#fdf3e3', '#d38b1a'],
    ];
@endphp

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <div>
            <h1 class="cvp-page-title">{{ __('cv-panel.nav.users') }}</h1>
            <p class="cvp-page-sub">
                المستخدم يُوقف ولا يُحذف، ويرث فرع من أنشأه.
            </p>
        </div>

        <span class="cvp-badge ms-auto" style="background:#e9eef4;color:#003f74">
            {{ $users->count() }} مستخدم
        </span>
    </div>

    {{-- Add: four fields across one row on desktop, so the card uses its width. --}}
    <div class="cvp-card p-4 mb-4">
        <h2 class="cvp-section-title mb-3">إضافة مستخدم</h2>

        <form method="POST" action="{{ route('cv-panel.users.store') }}" data-submit-guard>
            @csrf

            <div class="row g-3 align-items-start">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">الاسم</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="cvp-input" placeholder="اسم المستخدم" required>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">البريد الإلكتروني</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="cvp-input cvp-ltr" dir="ltr" placeholder="name@example.com" required>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">كلمة المرور</label>
                    <input type="password" name="password" class="cvp-input cvp-ltr" dir="ltr"
                           minlength="8" placeholder="٨ أحرف على الأقل" required>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">القسم</label>
                    <select name="department" class="form-select" required>
                        @foreach ($departments as $value => $label)
                            <option value="{{ $value }}" @selected(old('department') === $value)>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                </div>
            </div>

            <button type="submit" class="cvp-btn cvp-btn-gold mt-3">
                <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.2"
                     stroke-linecap="round" width="15" height="15">
                    <path d="M12 5v14M5 12h14"/>
                </svg>
                إضافة مستخدم
            </button>
        </form>
    </div>

    <h2 class="cvp-section-title mb-3">المستخدمون الحاليون</h2>

    {{-- Existing users: disabled, never deleted. --}}
    <div class="row g-3">
        @forelse ($users as $user)
            @php
                [$deptBg, $deptFg] = $deptStyle[$user->department] ?? ['#f1f5f9', '#475569'];
                $isMe = $user->id === auth()->id();
            @endphp

            <div class="col-12 col-xl-6">
                <div class="cvp-card cvp-user h-100">

                    <div class="cvp-user-head">
                        <span class="cvp-user-avatar">{{ mb_substr($user->name, 0, 1) }}</span>

                        <span class="flex-grow-1 min-w-0">
                            <span class="cvp-user-name">
                                {{ $user->name }}
                                @if ($isMe)
                                    <span class="cvp-badge"
                                          style="background:#e9eef4;color:#475569">أنت</span>
                                @endif
                            </span>
                            <span class="cvp-user-mail cvp-ltr" dir="ltr">{{ $user->email }}</span>
                        </span>

                        {{-- Hex values inline: a hex in class= renders colourless. --}}
                        <span class="cvp-badge" style="background:{{ $deptBg }};color:{{ $deptFg }}">
                            {{ $departments[$user->department] ?? '—' }}
                        </span>

                        <span class="cvp-badge"
                              style="background:{{ $user->is_active ? '#e8f6ef' : '#fef2f2' }};
                                     color:{{ $user->is_active ? '#1a9d63' : '#b91c1c' }}">
                            {{ $user->is_active ? 'مفعّل' : 'موقوف' }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('cv-panel.users.update', $user) }}"
                          id="user-save-{{ $user->id }}" class="cvp-user-body">
                        @csrf
                        @method('PUT')

                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">الاسم</label>
                                <input type="text" name="name" value="{{ $user->name }}"
                                       class="cvp-input" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">البريد</label>
                                <input type="email" name="email" value="{{ $user->email }}"
                                       class="cvp-input cvp-ltr" dir="ltr" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">كلمة مرور جديدة</label>
                                <input type="password" name="password" class="cvp-input cvp-ltr"
                                       dir="ltr" minlength="8" placeholder="اتركها فارغة لعدم التغيير">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">القسم</label>
                                <select name="department" class="form-select" required>
                                    @foreach ($departments as $value => $label)
                                        <option value="{{ $value }}" @selected($user->department === $value)>
                                            {{ $label }}
                                        </option>
                                    @endforeach
                                </select>
                            </div>
                        </div>
                    </form>

                    <div class="cvp-user-foot">
                        {{-- Outside the edit form: nesting forms is invalid HTML
                             and the inner one would never submit. --}}
                        <button type="submit" form="user-save-{{ $user->id }}"
                                class="cvp-btn cvp-btn-navy">حفظ التعديلات</button>

                        @unless ($isMe)
                            <form method="POST" action="{{ route('cv-panel.users.toggle', $user) }}"
                                  class="ms-auto m-0"
                                  onsubmit="return confirm('{{ $user->is_active ? 'إيقاف هذا المستخدم؟ لن يتمكن من الدخول.' : 'تفعيل هذا المستخدم؟' }}')">
                                @csrf
                                <button type="submit"
                                        class="cvp-btn {{ $user->is_active ? 'cvp-btn-danger' : 'cvp-btn-success' }}">
                                    {{ $user->is_active ? 'إيقاف' : 'تفعيل' }}
                                </button>
                            </form>
                        @else
                            <span class="ms-auto small" style="color:#9aa7b4">
                                لا يمكنك إيقاف حسابك
                            </span>
                        @endunless
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="cvp-card cvp-empty">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                         stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                        <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/>
                        <circle cx="9" cy="7" r="4"/><path d="M22 21v-2a4 4 0 0 0-3-3.87"/>
                    </svg>
                    <p class="mb-0" style="font-size:.9rem">لا يوجد مستخدمون بعد.</p>
                </div>
            </div>
        @endforelse
    </div>

@endsection
