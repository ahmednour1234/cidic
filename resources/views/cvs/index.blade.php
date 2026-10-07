@extends('layouts.app')

@section('meta_title', $nationality
    ? 'عاملات من ' . $nationality->display_name . ' | ' . setting('company_name_ar', 'سدك للإستقدام')
    : 'السير الذاتية المتاحة | ' . setting('company_name_ar', 'سدك للإستقدام'))

@section('meta_description', $nationality
    ? 'تصفّح السير الذاتية المتاحة للعاملات من ' . $nationality->display_name . '.'
    : 'تصفّح السير الذاتية المتاحة للعاملات المنزليات.')

@push('styles')
    @vite(['resources/css/cv-panel.css'])
@endpush

@section('content')

    <section class="page-hero">
        <div class="container">
            @if ($nationality)
                <nav aria-label="مسار التنقل">
                    <ol class="breadcrumb">
                        <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                        <li class="breadcrumb-item"><a href="{{ route('cvs.index') }}">السير الذاتية</a></li>
                        <li class="breadcrumb-item active" aria-current="page">
                            {{ $nationality->display_name }}
                        </li>
                    </ol>
                </nav>

                <div class="d-flex align-items-center gap-3 flex-wrap">
                    @if ($photo = $nationality->photoUrl())
                        <img src="{{ $photo }}" alt="" width="56" height="56"
                             class="rounded-3" style="object-fit:cover">
                    @endif
                    <div>
                        <h1 class="mb-1">عاملات من {{ $nationality->display_name }}</h1>
                        <p class="mb-0">{{ $workers->total() }} سيرة ذاتية متاحة</p>
                    </div>
                </div>
            @else
                <h1>السير الذاتية المتاحة</h1>
                <p class="mb-0">{{ $workers->total() }} سيرة ذاتية متاحة</p>
            @endif
        </div>
    </section>

    <section class="section">
        <div class="container">

            {{-- Nationality pills, with flags. Only those with CVs to show. --}}
            @if ($nationalities->isNotEmpty())
                <div class="cv-pills mb-4">
                    @foreach ($nationalities as $item)
                        <a href="{{ route('cvs.nationality', $item->public_key) }}"
                           class="cv-pill {{ $nationality?->id === $item->id ? 'is-active' : '' }}">
                            <x-cv-panel.nationality :nationality="$item" />
                        </a>
                    @endforeach
                </div>
            @endif

            {{-- Chip filters. Each keeps the other's value in its URL. --}}
            @php
                $base = $nationality
                    ? fn (array $q) => route('cvs.nationality', [$nationality->public_key] + $q)
                    : fn (array $q) => route('cvs.index', $q);
            @endphp

            <div class="cv-filterbar mb-4">
                <div class="row g-3">
                    <div class="col-12 col-md-6">
                        <div class="cv-filter-label">الخبرة</div>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ $base(['religion' => request('religion')]) }}"
                               class="cv-chip {{ request('experience') ? '' : 'is-active' }}">الكل</a>
                            @foreach (__('workers.experience') as $key => $label)
                                <a href="{{ $base(['experience' => $key, 'religion' => request('religion')]) }}"
                                   class="cv-chip {{ request('experience') === $key ? 'is-active' : '' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>

                    <div class="col-12 col-md-6">
                        <div class="cv-filter-label">الديانة</div>
                        <div class="d-flex flex-wrap gap-2 justify-content-center">
                            <a href="{{ $base(['experience' => request('experience')]) }}"
                               class="cv-chip {{ request('religion') ? '' : 'is-active' }}">الكل</a>
                            @foreach (__('workers.religion') as $key => $label)
                                <a href="{{ $base(['religion' => $key, 'experience' => request('experience')]) }}"
                                   class="cv-chip {{ request('religion') === $key ? 'is-active' : '' }}">
                                    {{ $label }}
                                </a>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            @if ($workers->isEmpty())
                <div class="text-center text-muted py-5">
                    {{ __('cv-panel.public.empty') }}
                </div>
            @else
                <div class="row g-3" id="cv-grid">
                    @include('cvs.partials.cards', ['workers' => $workers])
                </div>

                {{-- Infinite scroll sentinel; the paginator below is the no-JS path. --}}
                @if ($workers->hasMorePages())
                    <div id="cv-sentinel" data-next="{{ $workers->nextPageUrl() }}"
                         class="text-center py-4">
                        <span class="spinner-border spinner-border-sm text-muted"></span>
                    </div>
                @endif

                <noscript>
                    <div class="mt-3">{{ $workers->links() }}</div>
                </noscript>
            @endif
        </div>
    </section>

@endsection

@push('scripts')
<script>
    // Infinite scroll. Fetches ?partial=1, which returns only the cards.
    (() => {
        const sentinel = document.getElementById('cv-sentinel');
        const grid = document.getElementById('cv-grid');

        if (!sentinel || !grid || !('IntersectionObserver' in window)) {
            return;
        }

        let loading = false;

        const observer = new IntersectionObserver(async (entries) => {
            if (!entries[0].isIntersecting || loading) {
                return;
            }

            const next = sentinel.dataset.next;

            if (!next) {
                observer.disconnect();
                sentinel.remove();
                return;
            }

            loading = true;

            try {
                const url = new URL(next, window.location.origin);
                url.searchParams.set('partial', '1');

                const response = await fetch(url);

                if (!response.ok) {
                    throw new Error('request failed');
                }

                grid.insertAdjacentHTML('beforeend', await response.text());

                // The partial carries the following page's URL in a data
                // attribute on its last element.
                const marker = grid.querySelector('[data-next-page]:last-of-type');
                sentinel.dataset.next = marker?.dataset.nextPage || '';

                if (!sentinel.dataset.next) {
                    observer.disconnect();
                    sentinel.remove();
                }
            } catch (error) {
                observer.disconnect();
                sentinel.remove();
            } finally {
                loading = false;
            }
        }, { rootMargin: '200px' });

        observer.observe(sentinel);
    })();
</script>
@endpush
