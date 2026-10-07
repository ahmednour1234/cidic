@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.coordinators'))

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-4">
        <div>
            <h1 class="cvp-page-title">{{ __('cv-panel.nav.coordinators') }}</h1>
            <p class="cvp-page-sub">
                الجنسيات المرتبطة بالمنسّق تحدّد ما يراه ويرفع له ويحذفه.
            </p>
        </div>
    </div>

    @forelse ($coordinators as $coordinator)
        @php $assigned = $coordinator->nationalities->pluck('id')->all(); @endphp

        <div class="cvp-card cvp-user mb-3">
            <form method="POST" action="{{ route('cv-panel.coordinators.update', $coordinator) }}">
                @csrf
                @method('PUT')

                <div class="cvp-user-head">
                    <span class="cvp-user-avatar">{{ mb_substr($coordinator->name, 0, 1) }}</span>

                    <span class="flex-grow-1 min-w-0">
                        <span class="cvp-user-name">{{ $coordinator->name }}</span>
                        <span class="cvp-user-mail cvp-ltr" dir="ltr">{{ $coordinator->email }}</span>
                    </span>

                    {{-- Hex inline: a hex inside class= renders colourless. --}}
                    <span class="cvp-badge" style="background:#e6f0f9;color:#0060a8">
                        {{ count($assigned) }} جنسية
                    </span>
                </div>

                <div class="cvp-user-body">
                    <div class="row g-2">
                        @foreach ($nationalities as $nationality)
                            @php
                                $checked = in_array($nationality->id, $assigned, true);
                                $id = 'nat-'.$coordinator->id.'-'.$nationality->id;
                            @endphp

                            <div class="col-6 col-md-4 col-xl-3">
                                {{-- Alpine keeps the highlight in step with the
                                     checkbox; a server-rendered class alone
                                     would only update after a save. --}}
                                <label for="{{ $id }}" class="cvp-pick"
                                       x-data="{ on: {{ $checked ? 'true' : 'false' }} }"
                                       :class="on && 'is-on'">
                                    <input type="checkbox" id="{{ $id }}" name="nationality_ids[]"
                                           value="{{ $nationality->id }}" class="form-check-input m-0"
                                           x-model="on" @checked($checked)>
                                    <span class="flex-grow-1">
                                        <x-cv-panel.nationality :nationality="$nationality" />
                                    </span>
                                    <a href="{{ route('cvs.nationality', $nationality->public_key) }}"
                                       target="_blank" rel="noopener" class="cvp-pick-link"
                                       title="الصفحة العامة" aria-label="الصفحة العامة">
                                        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor"
                                             stroke-width="2" stroke-linecap="round" stroke-linejoin="round"
                                             width="13" height="13">
                                            <path d="M18 13v6a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2V8a2 2 0 0 1 2-2h6"/>
                                            <path d="M15 3h6v6"/><path d="M10 14 21 3"/>
                                        </svg>
                                    </a>
                                </label>
                            </div>
                        @endforeach
                    </div>
                </div>

                <div class="cvp-user-foot">
                    <button type="submit" class="cvp-btn cvp-btn-navy">حفظ الجنسيات</button>
                </div>
            </form>
        </div>
    @empty
        <div class="cvp-card cvp-empty">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5"
                 stroke-linecap="round" stroke-linejoin="round" width="40" height="40">
                <path d="M16 21v-2a4 4 0 0 0-4-4H6a4 4 0 0 0-4 4v2"/><circle cx="9" cy="7" r="4"/>
            </svg>
            <p class="mb-0" style="font-size:.9rem">لا يوجد منسّقون مفعّلون.</p>
        </div>
    @endforelse

@endsection
