@props(['faq', 'index' => 0, 'parent' => 'faqAccordion'])

@php
    $headingId = "faq-heading-{$faq->id}";
    $collapseId = "faq-collapse-{$faq->id}";
    $expanded = $index === 0;
@endphp

<div class="accordion-item">
    <h3 class="accordion-header" id="{{ $headingId }}">
        <button class="accordion-button @unless($expanded) collapsed @endunless"
                type="button"
                data-bs-toggle="collapse"
                data-bs-target="#{{ $collapseId }}"
                aria-expanded="{{ $expanded ? 'true' : 'false' }}"
                aria-controls="{{ $collapseId }}">
            {{ $faq->question }}
        </button>
    </h3>

    <div id="{{ $collapseId }}"
         class="accordion-collapse collapse @if($expanded) show @endif"
         aria-labelledby="{{ $headingId }}"
         data-bs-parent="#{{ $parent }}">
        <div class="accordion-body">
            {{ $faq->answer }}
        </div>
    </div>
</div>
