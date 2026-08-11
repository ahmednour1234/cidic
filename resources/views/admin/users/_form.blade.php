@php
    $isSelf = $user->exists && $user->id === auth()->id();
@endphp

<div class="admin-card">
    <div class="admin-card__body">
        @if ($isSelf)
            <div class="alert alert-info">
                لا يمكنك تغيير صلاحيتك أو تعطيل حسابك الحالي.
            </div>
        @endif

        <div class="row g-3">
            <div class="col-md-6">
                <label for="name" class="form-label">الاسم <span class="required-mark">*</span></label>
                <input type="text" id="name" name="name"
                       class="form-control @error('name') is-invalid @enderror"
                       value="{{ old('name', $user->name) }}" required maxlength="150">
                @error('name')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="email" class="form-label">البريد الإلكتروني <span class="required-mark">*</span></label>
                <input type="email" id="email" name="email" dir="ltr"
                       class="form-control @error('email') is-invalid @enderror"
                       value="{{ old('email', $user->email) }}" required maxlength="255">
                @error('email')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="password" class="form-label">
                    كلمة المرور
                    @unless ($user->exists) <span class="required-mark">*</span> @endunless
                </label>
                <input type="password" id="password" name="password" dir="ltr"
                       class="form-control @error('password') is-invalid @enderror"
                       @unless ($user->exists) required @endunless
                       autocomplete="new-password">
                @if ($user->exists)
                    <div class="form-text">اتركها فارغة للإبقاء على كلمة المرور الحالية.</div>
                @else
                    <div class="form-text">8 أحرف على الأقل.</div>
                @endif
                @error('password')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="password_confirmation" class="form-label">تأكيد كلمة المرور</label>
                <input type="password" id="password_confirmation" name="password_confirmation" dir="ltr"
                       class="form-control" @unless ($user->exists) required @endunless
                       autocomplete="new-password">
            </div>

            <div class="col-md-6">
                <label for="role" class="form-label">الصلاحية <span class="required-mark">*</span></label>
                <select id="role" name="role" class="form-select @error('role') is-invalid @enderror"
                        required @disabled($isSelf)>
                    @foreach ($roles as $value => $label)
                        <option value="{{ $value }}" @selected(old('role', $user->role?->value) === $value)>
                            {{ $label }}
                        </option>
                    @endforeach
                </select>
                @if ($isSelf)
                    {{-- Disabled inputs are not submitted; keep the value intact. --}}
                    <input type="hidden" name="role" value="{{ $user->role->value }}">
                @endif
                @error('role')<div class="invalid-feedback">{{ $message }}</div>@enderror
            </div>

            <div class="col-md-6">
                <label for="phone" class="form-label">رقم الجوال</label>
                <input type="tel" id="phone" name="phone" dir="ltr" class="form-control"
                       value="{{ old('phone', $user->phone) }}" maxlength="32">
            </div>

            <div class="col-12">
                <div class="form-check">
                    <input type="checkbox" class="form-check-input" id="is_active" name="is_active" value="1"
                           @checked(old('is_active', $user->exists ? $user->is_active : true)) @disabled($isSelf)>
                    <label class="form-check-label" for="is_active">حساب نشط</label>
                </div>
            </div>
        </div>
    </div>
</div>

<div class="d-flex gap-2 mt-3">
    <button type="submit" class="btn btn-primary">
        حفظ
        <span class="btn-spinner" aria-hidden="true"></span>
    </button>
    <a href="{{ route('admin.users.index') }}" class="btn btn-outline-secondary">إلغاء</a>
</div>
