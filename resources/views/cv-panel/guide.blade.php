@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.guide'))

@php
    use App\Support\CvPanel\CvPanelPermissions;

    $me = auth()->user();

    // Each section is shown only to the roles it concerns.
    $sections = [
        ['key' => 'roles', 'show' => true],
        ['key' => 'upload', 'show' => CvPanelPermissions::canUpload($me)],
        ['key' => 'public', 'show' => true],
        ['key' => 'reserve', 'show' => CvPanelPermissions::canReserve($me)],
        ['key' => 'after_reserve', 'show' => CvPanelPermissions::canReserve($me)],
        ['key' => 'follow_up', 'show' => CvPanelPermissions::canFollowUpReserved($me)],
        ['key' => 'delete', 'show' => CvPanelPermissions::canDelete($me)],
        ['key' => 'users', 'show' => CvPanelPermissions::canManageUsers($me)],
    ];
@endphp

@section('content')

    <div style="max-width:52rem">
        <h1 class="h6 fw-bold mb-3" style="color:#003f74">{{ __('cv-panel.nav.guide') }}</h1>

        <div class="alert rounded-4 border-0 small" style="background:#e6f0f9;color:#92400e">
            {!! __('cvp-auth.guide.notice') !!}
        </div>

        @foreach ($sections as $section)
            @continue (! $section['show'])

            <div class="cvp-card p-3 mb-3">
                <h2 class="h6 fw-bold mb-2" style="color:#003f74">{{ __('cvp-auth.guide.sections.'.$section['key'].'.title') }}</h2>
                <ul class="small mb-0 ps-3">
                    @foreach ((array) __('cvp-auth.guide.sections.'.$section['key'].'.items') as $item)
                        <li class="mb-1">{{ $item }}</li>
                    @endforeach
                </ul>
            </div>
        @endforeach
    </div>

@endsection
