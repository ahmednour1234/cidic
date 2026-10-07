@extends('cv-panel.layout')

@section('title', 'حجز سيرة ذاتية')

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <div>
            <h1 class="cvp-page-title">حجز سيرة ذاتية</h1>
            <p class="cvp-page-sub">{{ __('cv-panel.reserve.intro') }}</p>
        </div>
    </div>

    <div class="row g-3">

        {{-- The CV being reserved, so the agent can confirm at a glance. --}}
        <div class="col-12 col-lg-5">
            <div class="cvp-card cvp-user h-100">
                <div class="cvp-user-head">
                    <span class="cvp-user-avatar">{{ mb_substr($worker->name ?? '?', 0, 1) }}</span>
                    <span class="flex-grow-1 min-w-0">
                        <span class="cvp-user-name">{{ $worker->name ?? '—' }}</span>
                        <span class="cvp-user-mail">السيرة رقم {{ $worker->id }}</span>
                    </span>
                </div>

                <div class="cvp-user-body">
                    <dl class="cvp-facts mb-3">
                        <div>
                            <dt>الجنسية</dt>
                            <dd><x-cv-panel.nationality :nationality="$worker->nationality" /></dd>
                        </div>
                        <div>
                            <dt>الخبرة</dt>
                            <dd>{{ __('workers.experience.'.$worker->experience) }}</dd>
                        </div>
                        <div>
                            <dt>الديانة</dt>
                            <dd>{{ __('workers.religion.'.$worker->religion) }}</dd>
                        </div>
                        @if ($worker->profession)
                            <div>
                                <dt>المهنة</dt>
                                <dd>{{ $worker->profession }}</dd>
                            </div>
                        @endif
                    </dl>

                    <a href="{{ route('cv-panel.cvs.file', $worker->id) }}" target="_blank"
                       rel="noopener" class="cvp-btn cvp-btn-ghost w-100 justify-content-center">
                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                             stroke-linecap="round" stroke-linejoin="round" width="15" height="15">
                            <path d="M14 2H6a2 2 0 0 0-2 2v16a2 2 0 0 0 2 2h12a2 2 0 0 0 2-2V8z"/>
                            <path d="M14 2v6h6"/>
                        </svg>
                        عرض ملف السيرة
                    </a>
                </div>
            </div>
        </div>

        {{-- Client picker --}}
        <div class="col-12 col-lg-7">
            <form method="POST" action="{{ route('cv-panel.cvs.reserve.store', $worker->id) }}"
                  x-data="{ mode: '{{ old('client_name') ? 'new' : 'existing' }}' }"
                  class="cvp-card p-4 h-100 d-flex flex-column" data-submit-guard>
                @csrf

                <h2 class="cvp-section-title mb-3">العميل</h2>

                {{-- Registered client --}}
                <label for="mode-existing" class="cvp-choice" :class="mode === 'existing' && 'is-on'">
                    <input type="radio" id="mode-existing" class="form-check-input m-0"
                           value="existing" x-model="mode">
                    <span>
                        <span class="d-block fw-bold">عميل مسجّل</span>
                        <span class="d-block small" style="color:#6f7b88">ابحث بالاسم من قاعدة العملاء</span>
                    </span>
                </label>

                <div class="cvp-choice-body" x-show="mode === 'existing'" x-cloak>
                    <div x-data="clientSearch()" @click.outside="results = []">
                        <div class="cvp-field">
                            <svg class="cvp-field-icon" viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                 stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
                                <circle cx="11" cy="11" r="7"/><path d="m21 21-4.3-4.3"/>
                            </svg>
                            <input type="text" class="cvp-input" autocomplete="off"
                                   placeholder="اكتب اسم العميل للبحث…"
                                   x-model="term" @input.debounce.300ms="search()">
                        </div>

                        <input type="hidden" name="client_id" :value="selectedId">

                        <div class="cvp-picked mt-2" x-show="selectedName" x-cloak>
                            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
                                 stroke-linecap="round" stroke-linejoin="round" width="14" height="14">
                                <path d="m5 13 4 4L19 7"/>
                            </svg>
                            <span x-text="selectedName"></span>
                        </div>

                        <ul class="cvp-results mt-1" x-show="results.length > 0" x-cloak>
                            <template x-for="client in results" :key="client.id">
                                <li @click="selectedId = client.id; selectedName = client.name; term = client.name; results = []"
                                    x-text="client.name"></li>
                            </template>
                        </ul>
                    </div>
                </div>

                {{-- Inline client: WhatsApp leads are usually unregistered. --}}
                <label for="mode-new" class="cvp-choice mt-2" :class="mode === 'new' && 'is-on'">
                    <input type="radio" id="mode-new" class="form-check-input m-0" value="new" x-model="mode">
                    <span>
                        <span class="d-block fw-bold">عميل جديد</span>
                        <span class="d-block small" style="color:#6f7b88">اسم ورقم جوال فقط — بدون جواز</span>
                    </span>
                </label>

                <div class="cvp-choice-body" x-show="mode === 'new'" x-cloak>
                    <div class="row g-2">
                        <div class="col-12 col-sm-6">
                            <label class="cvp-label">اسم العميل</label>
                            <input type="text" name="client_name" value="{{ old('client_name') }}"
                                   class="cvp-input" placeholder="الاسم الكامل">
                        </div>
                        <div class="col-12 col-sm-6">
                            <label class="cvp-label">رقم الجوال</label>
                            <input type="tel" name="client_phone" value="{{ old('client_phone') }}"
                                   class="cvp-input cvp-ltr" dir="ltr" placeholder="05xxxxxxxx">
                        </div>
                    </div>
                </div>

                <div class="cvp-note mt-3">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.9"
                         stroke-linecap="round" stroke-linejoin="round" width="16" height="16">
                        <circle cx="12" cy="12" r="10"/><path d="M12 16v-4"/><path d="M12 8h.01"/>
                    </svg>
                    <span>الحجز لا ينتهي تلقائيًا — يبقى حتى يفكّه موظّف يدويًا.</span>
                </div>

                <div class="d-flex gap-2 mt-auto pt-3">
                    <button type="submit" class="cvp-btn cvp-btn-gold">تأكيد الحجز</button>
                    <a href="{{ route('cv-panel.cvs.index') }}" class="cvp-btn cvp-btn-ghost">رجوع</a>
                </div>
            </form>
        </div>
    </div>

@endsection

@push('scripts')
<script>
    // Alpine v3 component: remote client search, from two characters up.
    function clientSearch() {
        return {
            term: '',
            results: [],
            selectedId: '',
            selectedName: '',

            async search() {
                if (this.term.trim().length < 2) {
                    this.results = [];
                    return;
                }

                try {
                    const response = await fetch(
                        '{{ route('cv-panel.clients.search') }}?q=' + encodeURIComponent(this.term),
                        { headers: { 'Accept': 'application/json' } },
                    );

                    this.results = response.ok ? await response.json() : [];
                } catch (error) {
                    this.results = [];
                }
            },
        };
    }
</script>
@endpush
