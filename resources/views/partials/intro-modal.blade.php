{{--
    Intro modal: the shortened request form, opened shortly after a visitor
    lands on a public page.

    Shown once per browser session rather than on every navigation — sessionStorage
    keeps a repeat visitor from being interrupted on each page, and clears itself
    when the tab closes so a later visit still sees it.

    Suppressed on the request page itself (the same form is already the whole
    page) and on the success page, where re-asking would be nonsense.
--}}
@php
    $introHidden = request()->routeIs('recruitment-requests.*');
@endphp

@unless ($introHidden)
    <div class="modal fade" id="introRequestModal" tabindex="-1"
         aria-labelledby="introRequestModalLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content intro-modal">
                <div class="modal-header border-0">
                    <h2 class="modal-title h5" id="introRequestModalLabel">اطلب الآن</h2>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="إغلاق"></button>
                </div>
                <div class="modal-body pt-0">
                    <p class="text-muted-soft small mb-4">
                        اترك بياناتك وسيتواصل معك فريقنا في أقرب وقت.
                    </p>

                    @include('partials.quick-request-form', [
                        'services' => $introServices ?? [],
                        'idPrefix' => 'intro',
                    ])
                </div>
            </div>
        </div>
    </div>

    @push('scripts')
        <script>
            (function () {
                var KEY = 'cidic:intro-shown';
                var el = document.getElementById('introRequestModal');

                if (!el || !window.bootstrap) return;

                // A failed submit re-renders the page with old() values; reopening
                // over them would hide the errors the visitor needs to see.
                if (el.querySelector('.is-invalid')) return;

                try {
                    if (sessionStorage.getItem(KEY)) return;
                    sessionStorage.setItem(KEY, '1');
                } catch (e) {
                    // Private mode or blocked storage: show it, just don't remember.
                }

                window.setTimeout(function () {
                    bootstrap.Modal.getOrCreateInstance(el).show();
                }, 1500);
            })();
        </script>
    @endpush
@endunless
