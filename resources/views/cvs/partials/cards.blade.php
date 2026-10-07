@foreach ($workers as $worker)
    @php
        // The card is the CV itself: a flag strip, the preview, and one action.
        // Everything else (name, experience, religion) is already printed on
        // the CV, so repeating it below the preview only adds noise.
        $pdfUrl = route('cvs.pdf', $worker->id);
        $shareUrl = route('cvs.show', $worker->id);
        $whatsapp = 'https://wa.me/?text=' . rawurlencode(
            'أرغب في حجز هذه العاملة (رقم ' . $worker->id . ')' . "\n" . $shareUrl
        );

        // The related grid passes a plain Collection, which has no paginator.
        $isPaginated = $workers instanceof \Illuminate\Contracts\Pagination\Paginator;
    @endphp

    <div class="col-12 col-sm-6 col-lg-4 col-xl-3"
        @if ($loop->last && $isPaginated && $workers->hasMorePages())
            data-next-page="{{ $workers->nextPageUrl() }}"
        @endif>

        <article class="cv-card">

            {{-- Flag strip across the top of the card. --}}
            <div class="cv-card-head">
                <x-cv-panel.nationality :nationality="$worker->nationality" class="cv-card-nat" />

                <span class="cv-card-id">#{{ $worker->id }}</span>

                <a href="{{ $whatsapp }}" target="_blank" rel="noopener"
                   class="cv-card-wa" aria-label="مشاركة عبر واتساب" title="مشاركة عبر واتساب">
                    <svg viewBox="0 0 24 24" fill="currentColor" width="15" height="15">
                        <path d="M12.04 2A9.9 9.9 0 0 0 2.1 11.9c0 1.75.46 3.45 1.33 4.95L2 22l5.3-1.38a9.9 9.9 0 0 0 4.74 1.2h.01a9.9 9.9 0 0 0 9.9-9.9A9.9 9.9 0 0 0 12.04 2m0 18.14h-.01a8.2 8.2 0 0 1-4.19-1.15l-.3-.18-3.12.82.83-3.05-.2-.31a8.2 8.2 0 1 1 15.2-4.37 8.2 8.2 0 0 1-8.21 8.24m4.5-6.16c-.24-.12-1.46-.72-1.68-.8s-.39-.13-.56.12-.64.8-.78.97-.29.18-.53.06a6.7 6.7 0 0 1-3.35-2.93c-.25-.44.25-.4.72-1.35.08-.17.04-.3-.02-.43s-.55-1.35-.76-1.84c-.2-.48-.4-.42-.55-.42h-.48c-.16 0-.43.06-.65.3s-.86.84-.86 2.05.88 2.38 1 2.54 1.73 2.65 4.2 3.72c1.56.67 2.17.73 2.95.61.47-.07 1.46-.6 1.67-1.17s.2-1.08.14-1.18-.22-.17-.46-.29"/>
                    </svg>
                </a>
            </div>

            <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="cv-card-preview"
               aria-label="عرض السيرة الذاتية رقم {{ $worker->id }}">
                <embed src="{{ $pdfUrl }}#toolbar=0&navpanes=0&view=FitH"
                       type="application/pdf" title="معاينة السيرة الذاتية">
            </a>

            <div class="cv-card-foot">
                <a href="{{ $pdfUrl }}" target="_blank" rel="noopener" class="cv-card-btn">
                    عرض السيرة PDF
                </a>
            </div>
        </article>
    </div>
@endforeach
