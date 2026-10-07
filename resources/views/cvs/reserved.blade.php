@extends('layouts.app')

@section('meta_title', __('cv-panel.public.reserved_title'))
@section('meta_description', __('cv-panel.public.reserved_body'))

@push('styles')
    @vite(['resources/css/cv-panel.css'])
@endpush

@section('content')

    <section class="section">
        <div class="container" style="max-width:34rem">
            <div class="cvp-card p-5 text-center">

                <div class="mx-auto mb-3 d-grid place-items-center"
                     style="width:3.5rem;height:3.5rem;border-radius:1rem;background:#e6f0f9;color:#0060a8">
                    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
                         stroke-linecap="round" stroke-linejoin="round" width="26" height="26"
                         style="margin:auto">
                        <path d="M6 4h12v16l-6-4-6 4z"/>
                    </svg>
                </div>

                <h1 class="h5 fw-bold mb-2">{{ __('cv-panel.public.reserved_title') }}</h1>
                <p class="text-muted mb-4">{{ __('cv-panel.public.reserved_body') }}</p>

                <div class="d-flex flex-wrap justify-content-center gap-2">
                    <a href="{{ route('cvs.index') }}" class="cvp-btn cvp-btn-navy">
                        تصفّح السير المتاحة
                    </a>
                    <a href="{{ route('recruitment-requests.create') }}" class="cvp-btn cvp-btn-ghost">
                        اطلب عاملة
                    </a>
                </div>
            </div>
        </div>
    </section>

@endsection
