@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.reserved'))

@section('content')

    {{-- Pill tabs. Counts ignore the nationality filter by design. --}}
    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        @foreach ([
            'reserved' => [__('workers.status.reserved'), $reservedCount],
            'assigned' => [__('workers.status.assigned'), $assignedCount],
        ] as $key => [$label, $count])
            <a href="{{ route('cv-panel.cvs.reserved', array_filter([
                   'tab' => $key,
                   'nationality_id' => request('nationality_id'),
               ])) }}"
               class="cvp-btn {{ $tab === $key ? 'cvp-btn-navy' : 'cvp-btn-ghost' }}">
                {{ $label }}
                <span class="cvp-badge"
                      style="background:rgb(255 255 255 / .2);color:inherit">{{ $count }}</span>
            </a>
        @endforeach

        {{-- Only worth showing when the coordinator holds more than one. --}}
        @if ($nationalities->count() > 1)
            <form method="GET" action="{{ route('cv-panel.cvs.reserved') }}" class="ms-auto">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <select name="nationality_id" class="form-select form-select-sm"
                        onchange="this.form.submit()">
                    <option value="">كل جنسياتي</option>
                    @foreach ($nationalities as $nationality)
                        <option value="{{ $nationality->id }}"
                            @selected(request('nationality_id') == $nationality->id)>
                            {{ $nationality->display_name }}
                        </option>
                    @endforeach
                </select>
            </form>
        @endif
    </div>

    <div class="row g-3">
        @forelse ($workers as $worker)
            <div class="col-12 col-md-6 col-xl-4">
                <div class="cvp-card p-3 h-100 d-flex flex-column">

                    <div class="fw-bold mb-1">{{ $worker->name ?? '—' }}</div>
                    <div class="small text-muted mb-2">
                        #{{ $worker->id }} · <x-cv-panel.nationality :nationality="$worker->nationality" />
                    </div>

                    <dl class="small mb-3">
                        <div class="d-flex gap-2">
                            <dt class="text-muted fw-normal">العميل:</dt>
                            <dd class="mb-0 fw-bold">{{ $worker->client?->name ?? '—' }}</dd>
                        </div>
                        <div class="d-flex gap-2">
                            <dt class="text-muted fw-normal">الحاجز:</dt>
                            <dd class="mb-0">{{ $worker->assignedBy?->name ?? '—' }}</dd>
                        </div>
                        <div class="d-flex gap-2">
                            <dt class="text-muted fw-normal">التاريخ:</dt>
                            <dd class="mb-0">{{ $worker->assigned_at?->format('Y-m-d H:i') ?? '—' }}</dd>
                        </div>
                    </dl>

                    <div class="d-flex gap-2 mt-auto">
                        <a href="{{ route('cv-panel.cvs.file', $worker->id) }}" target="_blank"
                           rel="noopener" class="cvp-btn cvp-btn-ghost">عرض الملف</a>

                        @if ($tab === 'reserved')
                            <form method="POST" action="{{ route('cv-panel.cvs.assigned', $worker->id) }}"
                                  onsubmit="return confirm('تأكيد تعيين هذه العاملة؟ لا يمكن التراجع من اللوحة.')">
                                @csrf
                                <button type="submit" class="cvp-btn cvp-btn-success">تم التعيين</button>
                            </form>
                        @endif
                    </div>
                </div>
            </div>
        @empty
            <div class="col-12">
                <div class="cvp-card p-4 text-center text-muted small">
                    {{ __('cv-panel.list.empty') }}
                </div>
            </div>
        @endforelse
    </div>

    <div class="mt-3">{{ $workers->links() }}</div>

@endsection
