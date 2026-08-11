@php
    // $errors is normally shared by the web middleware; guard so this partial is
    // safe to render outside that context too.
    $errorBag = $errors ?? new \Illuminate\Support\MessageBag();
@endphp

@if (session('success') || session('error') || session('status') || $errorBag->any())
    <div class="container mt-3">
        @if (session('success'))
            <div class="alert alert-success alert-dismissible fade show" role="alert" data-auto-dismiss>
                {{ session('success') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('error'))
            <div class="alert alert-danger alert-dismissible fade show" role="alert">
                {{ session('error') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if (session('status'))
            <div class="alert alert-info alert-dismissible fade show" role="alert" data-auto-dismiss>
                {{ session('status') }}
                <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="إغلاق"></button>
            </div>
        @endif

        @if ($errorBag->any())
            <div class="alert alert-danger" role="alert">
                <strong>حدث خطأ، يرجى المحاولة مرة أخرى.</strong>
                <ul class="mb-0 mt-2 ps-3">
                    @foreach ($errorBag->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif
    </div>
@endif
