@php
    use App\Http\Middleware\SetCvPanelLocale;

    $current = app()->getLocale();
@endphp

{{-- Language switcher. Each option is its own POST form so it works without
     JavaScript; Alpine only opens and closes the menu. --}}
<div class="cvp-lang" x-data="{ open: false }" @keydown.escape.window="open = false"
     {{ $attributes }}>
    <button type="button" class="cvp-iconbtn cvp-lang-btn" @click="open = !open" :aria-expanded="open"
            aria-label="{{ __('cv-panel.language') }}" title="{{ __('cv-panel.language') }}">
        <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8"
             stroke-linecap="round" stroke-linejoin="round" width="17" height="17">
            <circle cx="12" cy="12" r="10"/><path d="M2 12h20"/>
            <path d="M12 2a15.3 15.3 0 0 1 4 10 15.3 15.3 0 0 1-4 10 15.3 15.3 0 0 1-4-10 15.3 15.3 0 0 1 4-10z"/>
        </svg>
        <span class="cvp-lang-code">{{ strtoupper($current) }}</span>
        <svg class="cvp-lang-chev" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2.4"
             stroke-linecap="round" stroke-linejoin="round" width="12" height="12"><path d="m6 9 6 6 6-6"/></svg>
    </button>

    <div class="cvp-lang-menu" x-show="open" x-cloak @click.outside="open = false" x-transition.opacity>
        @foreach (SetCvPanelLocale::LOCALES as $code => [$name, $dir])
            <form method="POST" action="{{ route('cv-panel.locale', $code) }}" class="m-0">
                @csrf
                <button type="submit" dir="{{ $dir }}"
                        class="cvp-lang-option {{ $code === $current ? 'is-active' : '' }}">
                    <span>{{ $name }}</span>
                    <small>{{ strtoupper($code) }}</small>
                </button>
            </form>
        @endforeach
    </div>
</div>
