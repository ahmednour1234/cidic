@extends('layouts.app')

@section('meta_title', ($worker->name ?? ('سيرة ذاتية رقم ' . $worker->id)) . ' | ' . setting('company_name_ar', 'سدك للإستقدام'))
@section('meta_description', 'سيرة ذاتية متاحة للعاملة ' . ($worker->name ?? '') . ' من ' . ($worker->nationality?->display_name ?? '') . '.')

@push('styles')
    @vite(['resources/css/cv-panel.css'])
@endpush

@php
    $pdfUrl = route('cvs.pdf', $worker->id);
    $pageUrl = route('cvs.show', $worker->id);
    $whatsapp = 'https://wa.me/?text=' . rawurlencode(
        'أرغب في حجز هذه العاملة: ' . ($worker->name ?? ('رقم ' . $worker->id))
        . ' (رقم ' . $worker->id . ')' . "\n" . $pageUrl
    );
@endphp

@section('content')

    <section class="page-hero">
        <div class="container">
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item"><a href="{{ route('cvs.index') }}">السير الذاتية</a></li>
                    @if ($worker->nationality)
                        <li class="breadcrumb-item">
                            <a href="{{ route('cvs.nationality', $worker->nationality->public_key) }}">
                                {{ $worker->nationality->display_name }}
                            </a>
                        </li>
                    @endif
                    <li class="breadcrumb-item active" aria-current="page">
                        {{ $worker->name ?? ('سيرة رقم ' . $worker->id) }}
                    </li>
                </ol>
            </nav>

            <h1 class="mb-0">{{ $worker->name ?? ('سيرة ذاتية رقم ' . $worker->id) }}</h1>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row g-4">

                <div class="col-12 col-lg-5">
                    <div class="cvp-card p-3 mb-3">
                        <h2 class="h6 fw-bold mb-3">البيانات</h2>

                        {{-- Labels, never the stored option keys. --}}
                        <dl class="row small mb-0">
                            <dt class="col-5 text-muted fw-normal">الجنسية</dt>
                            <dd class="col-7">{{ $worker->nationality?->display_name ?? '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">العمر</dt>
                            <dd class="col-7">{{ $worker->age ? $worker->age . ' سنة' : '—' }}</dd>

                            <dt class="col-5 text-muted fw-normal">الخبرة</dt>
                            <dd class="col-7">{{ __('workers.experience.'.$worker->experience) }}</dd>

                            <dt class="col-5 text-muted fw-normal">الديانة</dt>
                            <dd class="col-7">{{ __('workers.religion.'.$worker->religion) }}</dd>

                            <dt class="col-5 text-muted fw-normal">الجنس</dt>
                            <dd class="col-7">{{ __('workers.gender.'.$worker->gender) }}</dd>

                            @if ($worker->profession)
                                <dt class="col-5 text-muted fw-normal">المهنة</dt>
                                <dd class="col-7">{{ $worker->profession }}</dd>
                            @endif
                        </dl>
                    </div>

                    <div class="d-grid gap-2">
                        <a href="{{ route('recruitment-requests.create') }}" class="cvp-btn cvp-btn-navy justify-content-center">
                            اطلب هذه العاملة
                        </a>
                        <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                           class="cvp-btn cvp-btn-success justify-content-center">
                            اطلب عبر واتساب
                        </a>
                        <a href="{{ $pdfUrl }}" target="_blank" rel="noopener"
                           class="cvp-btn cvp-btn-ghost justify-content-center">
                            فتح السيرة PDF
                        </a>

                        <div x-data="{ copied: false }">
                            <button type="button" class="cvp-btn cvp-btn-ghost w-100 justify-content-center"
                                    :class="copied && 'cvp-copy-ok'"
                                    @click="
                                        const url = @js($pageUrl);
                                        const done = () => { copied = true; setTimeout(() => copied = false, 1500); };
                                        if (navigator.share) {
                                            navigator.share({ url }).catch(() => {});
                                        } else if (navigator.clipboard) {
                                            navigator.clipboard.writeText(url).then(done).catch(() => {});
                                        }
                                    ">
                                <span x-show="! copied">مشاركة الرابط</span>
                                <span x-show="copied" x-cloak>✓ تم النسخ</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-12 col-lg-7">
                    {{-- Always the public PDF route; never an admin route. --}}
                    <embed src="{{ $pdfUrl }}#view=FitH" type="application/pdf"
                           class="cvp-pdf-frame rounded-4 border" title="السيرة الذاتية">
                </div>
            </div>

            @if ($related->isNotEmpty())
                <h2 class="h6 fw-bold mt-5 mb-3">عاملات من نفس الجنسية</h2>
                <div class="row g-3">
                    @include('cvs.partials.cards', ['workers' => $related])
                </div>
            @endif
        </div>
    </section>

@endsection
