@extends('cv-panel.layout')

@section('title', __('cvp-video.title'))

@section('content')
    <div class="mb-4">
        <h1 class="cvp-page-title">{{ __('cvp-video.title') }} — #{{ $worker->id }} {{ $worker->name }}</h1>
        <p class="cvp-page-sub">{{ __('cvp-video.subtitle') }}</p>
        <a href="{{ route('cv-panel.cvs.index') }}" class="small">{{ __('cvp-video.back') }}</a>
    </div>

    @if ($errors->any())
        <div class="alert alert-danger">{{ $errors->first() }}</div>
    @endif

    <div class="row g-3">
        {{-- Current video --}}
        <div class="col-12">
            <div class="cvp-card p-4">
                <h2 class="cvp-section-title mb-3">{{ __('cvp-video.current') }}</h2>
                @if ($worker->video_path)
                    <video src="{{ route('cv-panel.cvs.video.file', $worker->id) }}" controls preload="metadata"
                           style="max-width:100%;max-height:360px;border-radius:8px;background:#000"></video>
                @elseif ($worker->video_url)
                    <a href="{{ $worker->video_url }}" target="_blank" rel="noopener" dir="ltr">{{ $worker->video_url }}</a>
                @else
                    <p class="text-muted mb-0">{{ __('cvp-video.none') }}</p>
                @endif

                @if ($worker->hasVideo())
                    <form method="POST" action="{{ route('cv-panel.cvs.video.destroy', $worker->id) }}" class="mt-3"
                          onsubmit="return confirm(@js(__('cvp-video.confirm_delete')))">
                        @csrf
                        @method('DELETE')
                        <button class="cvp-btn cvp-btn-danger">{{ __('cvp-video.delete') }}</button>
                    </form>
                @endif
            </div>
        </div>

        {{-- Option 1: link --}}
        <div class="col-12 col-lg-6">
            <div class="cvp-card p-4 h-100">
                <h2 class="cvp-section-title mb-3">{{ __('cvp-video.link_title') }}</h2>
                <form method="POST" action="{{ route('cv-panel.cvs.video.link', $worker->id) }}">
                    @csrf
                    @method('PUT')
                    <input type="url" name="video_url" class="form-control mb-2" dir="ltr" required
                           placeholder="https://drive.google.com/..." value="{{ old('video_url', $worker->video_url) }}">
                    <p class="small text-muted">{{ __('cvp-video.link_hint') }}</p>
                    <button class="cvp-btn cvp-btn-navy">{{ __('cvp-video.save_link') }}</button>
                </form>
            </div>
        </div>

        {{-- Option 2: chunked upload --}}
        <div class="col-12 col-lg-6">
            <div class="cvp-card p-4 h-100" x-data="videoUpload()">
                <h2 class="cvp-section-title mb-3">{{ __('cvp-video.upload_title') }}</h2>
                <input type="file" accept="video/*,.mp4,.mov,.webm,.m4v,.3gp"
                       class="form-control mb-2" x-ref="file" :disabled="busy">
                <p class="small text-muted">{{ __('cvp-video.upload_hint', ['mb' => intdiv(\App\Http\Controllers\CvPanel\WorkerVideoController::MAX_BYTES, 1048576)]) }}</p>

                <div class="progress mb-2" style="height:20px" x-show="busy || progress > 0">
                    <div class="progress-bar" :style="'width:' + progress + '%'" x-text="progress + '%'"></div>
                </div>
                <div class="text-danger small mb-2" x-show="error" x-text="error"></div>

                <button type="button" class="cvp-btn cvp-btn-navy" @click="start()" :disabled="busy">
                    <span x-show="!busy">{{ __('cvp-video.upload') }}</span>
                    <span x-show="busy">{{ __('cvp-video.uploading') }}</span>
                </button>
            </div>
        </div>
    </div>
@endsection

@push('scripts')
<script>
    function videoUpload() {
        const CHUNK = {{ \App\Http\Controllers\CvPanel\WorkerVideoController::CHUNK_BYTES }};
        const MAX = {{ \App\Http\Controllers\CvPanel\WorkerVideoController::MAX_BYTES }};
        const url = @js(route('cv-panel.cvs.video.chunk', $worker->id));
        const token = @js(csrf_token());

        return {
            busy: false, progress: 0, error: '',
            async start() {
                const file = this.$refs.file.files[0];
                this.error = '';
                if (!file) { this.error = @js(__('cvp-video.pick_file')); return; }
                if (file.size > MAX) { this.error = @js(__('cvp-video.too_big')); return; }

                this.busy = true;
                this.progress = 0;
                const total = Math.max(1, Math.ceil(file.size / CHUNK));
                const uploadId = Date.now().toString(36) + Math.random().toString(36).slice(2, 10);

                try {
                    for (let i = 0; i < total; i++) {
                        const fd = new FormData();
                        fd.append('upload_id', uploadId);
                        fd.append('index', i);
                        fd.append('total', total);
                        fd.append('name', file.name);
                        fd.append('chunk', file.slice(i * CHUNK, (i + 1) * CHUNK), 'chunk');

                        // Retry each chunk a few times: mobile connections drop.
                        let res = null, tries = 0;
                        do {
                            res = await fetch(url, {
                                method: 'POST', body: fd,
                                headers: { 'X-CSRF-TOKEN': token, 'Accept': 'application/json' },
                            }).catch(() => null);
                        } while ((!res || res.status >= 500) && ++tries < 3);

                        if (!res || !res.ok) {
                            const body = res ? await res.json().catch(() => ({})) : {};
                            throw new Error(body.message || @js(__('cvp-video.failed')));
                        }
                        this.progress = Math.round(((i + 1) / total) * 100);
                    }
                    location.reload();
                } catch (e) {
                    this.error = e.message;
                    this.busy = false;
                }
            },
        };
    }
</script>
@endpush
