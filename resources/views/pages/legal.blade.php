@extends('layouts.app')

{{-- Cast to string: @section with a null value leaves an output buffer open. --}}
@section('meta_title', $page->meta_title ?: $page->title . ' | ' . setting('company_name_ar'))
@section('meta_description', (string) $page->meta_description)

@section('content')
    <section class="page-hero">
        <div class="container">
            <h1>{{ $page->title }}</h1>
            <nav aria-label="مسار التنقل">
                <ol class="breadcrumb">
                    <li class="breadcrumb-item"><a href="{{ route('home') }}">الرئيسية</a></li>
                    <li class="breadcrumb-item active" aria-current="page">{{ $page->title }}</li>
                </ol>
            </nav>
        </div>
    </section>

    <section class="section">
        <div class="container">
            <div class="row justify-content-center">
                <div class="col-lg-9">
                    @if (filled($page->content))
                        <div class="cms-content" style="line-height: 2.1; color: var(--muted);">
                            {{-- Admin-authored HTML from the CMS. --}}
                            {!! $page->content !!}
                        </div>
                    @else
                        <div class="empty-state">
                            <p class="mb-0">لم يتم إضافة محتوى لهذه الصفحة بعد.</p>
                        </div>
                    @endif
                </div>
            </div>
        </div>
    </section>
@endsection
