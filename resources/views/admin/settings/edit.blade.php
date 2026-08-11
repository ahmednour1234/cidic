@extends('layouts.admin')

@section('title', 'إعدادات الموقع')

@section('content')
    <form method="POST" action="{{ route('admin.settings.update') }}" enctype="multipart/form-data" data-submit-guard>
        @csrf
        @method('PUT')

        <div class="admin-card">
            <div class="admin-card__body">
                <ul class="nav nav-tabs mb-4" role="tablist">
                    @foreach ($groups as $key => $label)
                        <li class="nav-item" role="presentation">
                            <button class="nav-link @if($loop->first) active @endif"
                                    id="tab-{{ $key }}"
                                    data-bs-toggle="tab"
                                    data-bs-target="#pane-{{ $key }}"
                                    type="button"
                                    role="tab"
                                    aria-controls="pane-{{ $key }}"
                                    aria-selected="{{ $loop->first ? 'true' : 'false' }}">
                                {{ $label }}
                            </button>
                        </li>
                    @endforeach
                </ul>

                <div class="tab-content">
                    @foreach ($groups as $key => $label)
                        <div class="tab-pane fade @if($loop->first) show active @endif"
                             id="pane-{{ $key }}" role="tabpanel" aria-labelledby="tab-{{ $key }}">

                            <div class="row g-3">
                                @forelse ($grouped[$key] ?? [] as $setting)
                                    @php
                                        $fieldId = 'setting-' . $setting->key;
                                        $isImage = $setting->type === 'image';
                                        $isTextarea = $setting->type === 'textarea';
                                    @endphp

                                    <div class="{{ $isTextarea ? 'col-12' : 'col-md-6' }}">
                                        <label for="{{ $fieldId }}" class="form-label">
                                            {{ $setting->label ?: $setting->key }}
                                        </label>

                                        @if ($isImage)
                                            <input type="file" id="{{ $fieldId }}"
                                                   name="files[{{ $setting->key }}]"
                                                   class="form-control"
                                                   accept=".jpg,.jpeg,.png,.webp,.svg,.ico">
                                            @if ($setting->value)
                                                <img src="{{ \Illuminate\Support\Facades\Storage::url($setting->value) }}"
                                                     alt="" class="file-preview">
                                            @endif
                                        @elseif ($isTextarea)
                                            <textarea id="{{ $fieldId }}"
                                                      name="settings[{{ $setting->key }}]"
                                                      rows="3"
                                                      class="form-control"
                                                      maxlength="5000">{{ old('settings.' . $setting->key, $setting->value) }}</textarea>
                                        @else
                                            <input type="{{ in_array($setting->type, ['email', 'url', 'number'], true) ? $setting->type : 'text' }}"
                                                   id="{{ $fieldId }}"
                                                   name="settings[{{ $setting->key }}]"
                                                   class="form-control"
                                                   @if (in_array($setting->type, ['url', 'email'], true)) dir="ltr" @endif
                                                   value="{{ old('settings.' . $setting->key, $setting->value) }}"
                                                   maxlength="5000">
                                        @endif

                                        <div class="form-text" dir="ltr">{{ $setting->key }}</div>
                                    </div>
                                @empty
                                    <div class="col-12">
                                        <p class="text-muted-soft mb-0">لا توجد إعدادات في هذا القسم.</p>
                                    </div>
                                @endforelse
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        <div class="d-flex gap-2 mt-3">
            <button type="submit" class="btn btn-primary">
                حفظ الإعدادات
                <span class="btn-spinner" aria-hidden="true"></span>
            </button>
        </div>
    </form>
@endsection
