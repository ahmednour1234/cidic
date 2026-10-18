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
                {{ __('cvp-users.users.subtitle') }}
            </p>
        </div>

        <span class="cvp-badge ms-auto" style="background:#e9eef4;color:#003f74">
            {{ __('cvp-users.users.count', ['count' => $users->count()]) }}
        </span>
    </div>

    {{-- Add: four fields across one row on desktop, so the card uses its width. --}}
    <div class="cvp-card p-4 mb-4">
        <h2 class="cvp-section-title mb-3">{{ __('cvp-users.users.add') }}</h2>

        <form method="POST" action="{{ route('cv-panel.users.store') }}" data-submit-guard>
            @csrf

            <div class="row g-3 align-items-start">
                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">{{ __('cvp-users.users.name') }}</label>
                    <input type="text" name="name" value="{{ old('name') }}"
                           class="cvp-input" placeholder="{{ __('cvp-users.users.name_placeholder') }}" required>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">{{ __('cvp-users.users.email') }}</label>
                    <input type="email" name="email" value="{{ old('email') }}"
                           class="cvp-input cvp-ltr" dir="ltr" placeholder="name@example.com" required>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">{{ __('cvp-users.users.password') }}</label>
                    <input type="password" name="password" class="cvp-input cvp-ltr" dir="ltr"
                           minlength="8" placeholder="{{ __('cvp-users.users.password_placeholder') }}" required>
                </div>

                <div class="col-12 col-md-6 col-xl-3">
                    <label class="cvp-label">{{ __('cvp-users.users.department') }}</label>
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
                {{ __('cvp-users.users.add') }}
            </button>
        </form>
    </div>

    <h2 class="cvp-section-title mb-3">{{ __('cvp-users.users.existing') }}</h2>

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
                                          style="background:#e9eef4;color:#475569">{{ __('cvp-users.users.you') }}</span>
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
                            {{ $user->is_active ? __('cvp-users.users.active') : __('cvp-users.users.disabled') }}
                        </span>
                    </div>

                    <form method="POST" action="{{ route('cv-panel.users.update', $user) }}"
                          id="user-save-{{ $user->id }}" class="cvp-user-body">
                        @csrf
                        @method('PUT')

                        <div class="row g-2">
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">{{ __('cvp-users.users.name') }}</label>
                                <input type="text" name="name" value="{{ $user->name }}"
                                       class="cvp-input" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">{{ __('cvp-users.users.email_short') }}</label>
                                <input type="email" name="email" value="{{ $user->email }}"
                                       class="cvp-input cvp-ltr" dir="ltr" required>
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">{{ __('cvp-users.users.new_password') }}</label>
                                <input type="password" name="password" class="cvp-input cvp-ltr"
                                       dir="ltr" minlength="8" placeholder="{{ __('cvp-users.users.new_password_placeholder') }}">
                            </div>
                            <div class="col-12 col-sm-6">
                                <label class="cvp-label">{{ __('cvp-users.users.department') }}</label>
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
                                class="cvp-btn cvp-btn-navy">{{ __('cvp-users.users.save') }}</button>

                        @unless ($isMe)
                            <form method="POST" action="{{ route('cv-panel.users.toggle', $user) }}"
                                  class="ms-auto m-0"
                                  onsubmit="return confirm(@js($user->is_active ? __('cvp-users.users.confirm_disable') : __('cvp-users.users.confirm_enable')))">
                                @csrf
                                <button type="submit"
                                        class="cvp-btn {{ $user->is_active ? 'cvp-btn-danger' : 'cvp-btn-success' }}">
                                    {{ $user->is_active ? __('cvp-users.users.disable') : __('cvp-users.users.enable') }}
                                </button>
                            </form>
                        @else
                            <span class="ms-auto small" style="color:#9aa7b4">
                                {{ __('cvp-users.users.cannot_disable_self') }}
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
                    <p class="mb-0" style="font-size:.9rem">{{ __('cvp-users.users.empty') }}</p>
                </div>
            </div>
        @endforelse
    </div>

@endsection
