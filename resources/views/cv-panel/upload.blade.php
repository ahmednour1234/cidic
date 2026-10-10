@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.upload'))

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <div>
            <h1 class="cvp-page-title">{{ __('cv-panel.nav.upload') }}</h1>
            <p class="cvp-page-sub">
                اسم العاملة يؤخذ من اسم الملف، والملف المكرّر يُتخطّى تلقائيًا.
            </p>
        </div>
    </div>

    {{-- Alpine v3 throughout; v2's __x.$data does not exist here. --}}
    <form method="POST" action="{{ route('cv-panel.upload.store') }}"
          enctype="multipart/form-data"
          x-data="cvUpload()" @submit="sending = true">
        @csrf

        <div class="row g-3">

            {{-- Attributes shared by the whole batch --}}
            <div class="col-12 col-lg-5">
                <div class="cvp-card p-4 h-100">
                    <h2 class="cvp-section-title mb-3">بيانات الدفعة</h2>

                    <div class="mb-3">
                        <label for="nationality_id" class="cvp-label">الجنسية *</label>
                        <select id="nationality_id" name="nationality_id" class="form-select" required
                                onchange="window.location = '{{ route('cv-panel.upload') }}?nationality_id=' + this.value">
                            <option value="">اختر الجنسية</option>
                            @foreach ($nationalities as $nationality)
                                <option value="{{ $nationality->id }}"
                                    @selected($selectedNationalityId == $nationality->id)>
                                    {{ $nationality->display_name }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div class="row g-2 mb-3">
                        <div class="col-12 col-sm-6">
                            <label for="experience" class="cvp-label">الخبرة *</label>
                            <select id="experience" name="experience" class="form-select" required>
                                <option value="">اختر</option>
                                @foreach (__('workers.experience') as $key => $label)
                                    <option value="{{ $key }}" @selected(old('experience') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>

                        <div class="col-12 col-sm-6">
                            <label for="religion" class="cvp-label">الديانة *</label>
                            <select id="religion" name="religion" class="form-select" required>
                                <option value="">اختر</option>
                                @foreach (__('workers.religion') as $key => $label)
                                    <option value="{{ $key }}" @selected(old('religion') === $key)>{{ $label }}</option>
                                @endforeach
                            </select>
                        </div>
                    </div>

                    <div class="mb-3">
                        <label for="profession" class="cvp-label">المهنة</label>
                        <input type="text" id="profession" name="profession" value="{{ old('profession') }}"
                               class="cvp-input" placeholder="عاملة منزلية">
                    </div>

                    <p class="small mb-0" style="color:#6f7b88">
                        الخبرة والديانة إلزاميتان لأن الموقع العام يفلتر بهما.
                    </p>
                </div>
            </div>

            {{-- Files --}}
            <div class="col-12 col-lg-7">
                <div class="cvp-card p-4 h-100 d-flex flex-column">
                    <h2 class="cvp-section-title mb-3">ملفات السير الذاتية (PDF) *</h2>

                    <label for="cvs" class="cvp-drop" :class="dragging && 'is-dragging'"
                           @dragover.prevent="dragging = true"
                           @dragleave.prevent="dragging = false"
                           @drop.prevent="drop($event)">
                        <span class="cvp-drop-icon">
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.7"
                                 stroke-linecap="round" stroke-linejoin="round" width="24" height="24">
                                <path d="M21 15v4a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2v-4"/>
                                <path d="M17 8l-5-5-5 5"/><path d="M12 3v13"/>
                            </svg>
                        </span>

                        <span class="cvp-drop-title">اسحب ملفات الـ PDF هنا</span>
                        <span class="cvp-drop-or">— أو —</span>
                        <span class="cvp-btn cvp-btn-ghost">اختر من جهازك</span>
                        <span class="cvp-drop-hint">{{ __('cv-panel.upload.hint') }}</span>

                        <input type="file" id="cvs" name="cvs[]" class="d-none" accept="application/pdf"
                               multiple required x-ref="input" @change="pick($event)">
                    </label>

                    {{-- Chosen files, so a mis-pick is obvious before sending. --}}
                    <div class="cvp-filelist mt-3" x-show="files.length > 0" x-cloak>
                        <div class="d-flex align-items-center mb-2">
                            <span class="cvp-label mb-0"
                                  x-text="@js(__('cv-panel.upload.selected', ['count' => ':n'])).replace(':n', files.length)"></span>
                            <button type="button" class="cvp-btn cvp-btn-ghost ms-auto"
                                    style="padding:.25rem .6rem" @click="clear()">مسح الكل</button>
                        </div>

                        <ul class="cvp-files">
                            <template x-for="(f, i) in files" :key="i">
                                <li>
                                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                         stroke-width="1.7" stroke-linecap="round" stroke-linejoin="round"
                                         width="15" height="15">
                                        <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                                        <path d="M14 2v6h6"/>
                                    </svg>
                                    <span class="flex-grow-1 text-truncate" x-text="f.name"></span>
                                    <span class="cvp-file-size" x-text="size(f.size)"></span>
                                </li>
                            </template>
                        </ul>
                    </div>

                    {{-- Only offered when this nationality actually has stale CVs. --}}
                    @if ($selectedNationalityId && $purgeCount > 0)
                        <label for="purge_old" class="cvp-purge mt-3">
                            <input type="checkbox" id="purge_old" name="purge_old" value="1"
                                   class="form-check-input m-0">
                            <span>
                                <span class="d-block fw-bold">
                                    {{ __('cv-panel.upload.purge_label', ['count' => $purgeCount]) }}
                                </span>
                                <span class="d-block small">{{ __('cv-panel.upload.purge_note') }}</span>
                            </span>
                        </label>
                    @endif

                    <div class="mt-auto pt-3">
                        <button type="submit" class="cvp-btn cvp-btn-gold cvp-btn-lg"
                                :disabled="sending || files.length === 0">
                            <span x-show="sending" x-cloak class="spinner-border spinner-border-sm"></span>
                            <span x-text="sending
                                ? 'جارٍ الرفع…'
                                : (files.length ? 'رفع ' + files.length + ' ملف' : 'رفع')">رفع</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </form>

@endsection

@push('scripts')
<script>
    function cvUpload() {
        return {
            files: [],
            dragging: false,
            sending: false,

            pick(event) {
                this.files = Array.from(event.target.files);
            },

            drop(event) {
                this.dragging = false;

                // Only PDFs; the server validates again, but rejecting here
                // saves the round trip.
                const dropped = Array.from(event.dataTransfer.files)
                    .filter((f) => f.type === 'application/pdf');

                if (dropped.length === 0) {
                    return;
                }

                // Assigning a DataTransfer list is the only way to put dropped
                // files onto the input so they are actually submitted.
                const bag = new DataTransfer();
                dropped.forEach((f) => bag.items.add(f));
                this.$refs.input.files = bag.files;

                this.files = dropped;
            },

            clear() {
                this.files = [];
                this.$refs.input.value = '';
            },

            size(bytes) {
                return bytes < 1024 * 1024
                    ? Math.max(1, Math.round(bytes / 1024)) + ' KB'
                    : (bytes / 1024 / 1024).toFixed(1) + ' MB';
            },
        };
    }
</script>
@endpush
