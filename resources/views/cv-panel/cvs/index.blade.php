@extends('cv-panel.layout')

@section('title', __('cv-panel.nav.cvs'))

@php
    use App\Support\CvPanel\CvPanelPermissions;

    $me = auth()->user();
    $canDelete = CvPanelPermissions::canDelete($me);
    $canReserve = CvPanelPermissions::canReserve($me);
@endphp

@section('content')

    <div class="d-flex flex-wrap align-items-center gap-2 mb-3">
        <h1 class="h6 fw-bold mb-0" style="color:#003f74">
            {{ __('cv-panel.list.heading', ['total' => $workers->total()]) }}
        </h1>

        @if (CvPanelPermissions::canUpload($me))
            <a href="{{ route('cv-panel.upload') }}" class="cvp-btn cvp-btn-gold ms-auto">
                + {{ __('cv-panel.nav.upload') }}
            </a>
        @endif
    </div>

    {{-- Filters --}}
    <form method="GET" action="{{ route('cv-panel.cvs.index') }}" class="cvp-card p-3 mb-3">
        <div class="row g-2">
            <div class="col-12 col-md-4">
                <input type="search" name="search" value="{{ request('search') }}"
                       class="form-control form-control-sm"
                       placeholder="{{ __('cv-panel.list.search') }}">
            </div>

            <div class="col-6 col-md-2">
                <select name="nationality_id" class="form-select form-select-sm">
                    <option value="">{{ __('cv-panel.list.all_nationalities') }}</option>
                    @foreach ($nationalities as $nationality)
                        <option value="{{ $nationality->id }}"
                            @selected(request('nationality_id') == $nationality->id)>
                            {{ $nationality->display_name }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="status" class="form-select form-select-sm">
                    <option value="">{{ __('cv-panel.list.all_statuses') }}</option>
                    {{-- assigned is excluded: it never appears in this list. --}}
                    @foreach (['available', 'reserved'] as $status)
                        <option value="{{ $status }}" @selected(request('status') === $status)>
                            {{ __('workers.status.'.$status) }}
                        </option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="experience" class="form-select form-select-sm">
                    <option value="">{{ __('cv-panel.list.all_experiences') }}</option>
                    @foreach (__('workers.experience') as $key => $label)
                        <option value="{{ $key }}" @selected(request('experience') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>

            <div class="col-6 col-md-2">
                <select name="religion" class="form-select form-select-sm">
                    <option value="">{{ __('cv-panel.list.all_religions') }}</option>
                    @foreach (__('workers.religion') as $key => $label)
                        <option value="{{ $key }}" @selected(request('religion') === $key)>{{ $label }}</option>
                    @endforeach
                </select>
            </div>
        </div>

        <div class="d-flex gap-2 mt-2">
            <button type="submit" class="cvp-btn cvp-btn-navy">{{ __('cv-panel.list.filter') }}</button>
            <a href="{{ route('cv-panel.cvs.index') }}" class="cvp-btn cvp-btn-ghost">
                {{ __('cv-panel.list.reset') }}
            </a>
        </div>
    </form>

    @php
        // Row actions live in their own forms, declared once at the end of the
        // page and referenced with form="": nesting them inside the bulk form
        // would be invalid HTML and silently break submission.
        $deletable = $workers->filter(fn ($w) => CvPanelPermissions::canDeleteWorker($me, $w));
    @endphp

    <div x-data="{ selected: [] }">

        {{-- Bulk delete form: holds only the checkboxes. --}}
        @if ($canDelete && $deletable->isNotEmpty())
            <form method="POST" action="{{ route('cv-panel.cvs.bulk-destroy') }}" id="bulk-delete-form"
                  @submit="if (! confirm(@js(__('cv-panel.delete.confirm_bulk')))) $event.preventDefault()">
                @csrf
                @method('DELETE')
                <template x-for="id in selected" :key="id">
                    <input type="hidden" name="ids[]" :value="id">
                </template>
            </form>
        @endif

        {{-- Desktop table --}}
        <div class="cvp-card p-0 overflow-hidden d-none d-lg-block">
            <div class="table-responsive">
                <table class="table cvp-table mb-0 align-middle">
                    <thead class="table-light">
                        <tr>
                            @if ($canDelete && $deletable->isNotEmpty())
                                <th style="width:2.5rem">
                                    <input type="checkbox" class="form-check-input"
                                           @change="selected = $event.target.checked ? @js($deletable->pluck('id')->values()) : []"
                                           :checked="selected.length === {{ $deletable->count() }}">
                                </th>
                            @endif
                            <th>#</th>
                            <th>الاسم</th>
                            <th>الجنسية</th>
                            <th>الخبرة</th>
                            <th>الديانة</th>
                            <th>الحالة</th>
                            <th>العميل</th>
                            <th>تاريخ الرفع</th>
                            <th>السيرة</th>
                            <th></th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse ($workers as $worker)
                            <tr>
                                @if ($canDelete && $deletable->isNotEmpty())
                                    <td>
                                        @if (CvPanelPermissions::canDeleteWorker($me, $worker))
                                            <input type="checkbox" class="form-check-input"
                                                   value="{{ $worker->id }}" x-model.number="selected">
                                        @endif
                                    </td>
                                @endif

                                <td class="text-muted">{{ $worker->id }}</td>
                                <td class="fw-bold">{{ $worker->name ?? '—' }}</td>
                                <td><x-cv-panel.nationality :nationality="$worker->nationality" /></td>
                                {{-- Labels, never the raw option keys. --}}
                                <td>{{ __('workers.experience.'.$worker->experience) }}</td>
                                <td>{{ __('workers.religion.'.$worker->religion) }}</td>

                                <td>
                                    <x-cv-panel.status-badge
                                        :status="$worker->status"
                                        :withdrawn="$worker->status === 'available' && $worker->isWithdrawn()" />

                                    @if ($worker->status === 'reserved' && $worker->assignedBy)
                                        <div class="text-muted" style="font-size:.7rem">
                                            {{ __('cv-panel.list.reserved_by', [
                                                'name' => $worker->assignedBy->name,
                                                'time' => $worker->assigned_at?->diffForHumans(null, true) ?? '—',
                                            ]) }}
                                        </div>
                                    @endif
                                </td>

                                <td>{{ $worker->client?->name ?? '—' }}</td>
                                <td class="text-muted small">{{ $worker->created_at?->format('Y-m-d') }}</td>

                                <td>
                                    <a href="{{ route('cv-panel.cvs.file', $worker->id) }}" target="_blank"
                                       rel="noopener" class="cvp-btn cvp-btn-ghost">PDF</a>
                                </td>

                                <td class="text-nowrap">
                                    @include('cv-panel.cvs.partials.row-actions', ['worker' => $worker])
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="11" class="text-center text-muted py-5">
                                    {{ __('cv-panel.list.empty') }}
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Mobile cards --}}
        <div class="d-lg-none">
            @forelse ($workers as $worker)
                <div class="cvp-card p-3 mb-2">
                    <div class="d-flex align-items-start gap-2 mb-2">
                        @if ($canDelete && CvPanelPermissions::canDeleteWorker($me, $worker))
                            <input type="checkbox" class="form-check-input mt-1"
                                   value="{{ $worker->id }}" x-model.number="selected">
                        @endif
                        <div class="flex-grow-1">
                            <div class="fw-bold">{{ $worker->name ?? '—' }}</div>
                            <div class="small text-muted">
                                #{{ $worker->id }} · <x-cv-panel.nationality :nationality="$worker->nationality" />
                            </div>
                        </div>
                        <x-cv-panel.status-badge
                            :status="$worker->status"
                            :withdrawn="$worker->status === 'available' && $worker->isWithdrawn()" />
                    </div>

                    <div class="small text-muted mb-2">
                        {{ __('workers.experience.'.$worker->experience) }} ·
                        {{ __('workers.religion.'.$worker->religion) }}
                        @if ($worker->client)
                            · {{ $worker->client->name }}
                        @endif
                    </div>

                    @if ($worker->status === 'reserved' && $worker->assignedBy)
                        <div class="text-muted mb-2" style="font-size:.7rem">
                            {{ __('cv-panel.list.reserved_by', [
                                'name' => $worker->assignedBy->name,
                                'time' => $worker->assigned_at?->diffForHumans(null, true) ?? '—',
                            ]) }}
                        </div>
                    @endif

                    <div class="d-flex flex-wrap gap-2">
                        <a href="{{ route('cv-panel.cvs.file', $worker->id) }}" target="_blank"
                           rel="noopener" class="cvp-btn cvp-btn-ghost">PDF</a>
                        @include('cv-panel.cvs.partials.row-actions', ['worker' => $worker])
                    </div>
                </div>
            @empty
                <div class="cvp-card p-4 text-center text-muted small">
                    {{ __('cv-panel.list.empty') }}
                </div>
            @endforelse
        </div>

        {{-- Sticky bulk bar --}}
        @if ($canDelete && $deletable->isNotEmpty())
            <div class="cvp-bulkbar mt-3" x-show="selected.length > 0" x-cloak>
                <span class="fw-bold small"
                      x-text="@js(__('cv-panel.list.selected', ['count' => ':n'])).replace(':n', selected.length)"></span>
                <button type="submit" form="bulk-delete-form" class="cvp-btn"
                        style="background:#fff;color:#dc2626">
                    {{ __('cv-panel.list.delete_selected') }}
                </button>
            </div>
        @endif
    </div>

    <div class="mt-3">
        {{ $workers->links() }}
    </div>

    {{-- Row action forms, kept outside every other form. --}}
    @foreach ($workers as $worker)
        @include('cv-panel.cvs.partials.row-forms', ['worker' => $worker])
    @endforeach

@endsection
