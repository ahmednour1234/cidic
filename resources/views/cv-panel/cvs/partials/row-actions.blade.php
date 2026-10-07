@php
    use App\Models\Worker;
    use App\Support\CvPanel\CvPanelPermissions;

    $me = auth()->user();
    $isReserved = $worker->status === Worker::STATUS_RESERVED;
    // Buttons target the forms rendered by row-forms.blade.php.
    $mayActOnReservation = $worker->canBeUnassignedBy($me)
        || $worker->canRecordTamaraBy($me)
        || $worker->canCreateContractBy($me);
@endphp

<div class="d-flex flex-wrap gap-1">

    @if ($worker->status === Worker::STATUS_AVAILABLE)

        @if (CvPanelPermissions::canReserve($me))
            <a href="{{ route('cv-panel.cvs.reserve', $worker->id) }}" class="cvp-btn cvp-btn-gold">
                حجز للعميل
            </a>
        @endif

        @if (CvPanelPermissions::canDeleteWorker($me, $worker))
            <button type="submit" form="delete-{{ $worker->id }}" class="cvp-btn cvp-btn-danger">
                حذف
            </button>
        @endif

    @elseif ($isReserved)

        @if ($worker->canCreateContractBy($me))
            <button type="submit" form="contract-{{ $worker->id }}" class="cvp-btn cvp-btn-success">
                {{ __('cv-panel.contract.create') }}
            </button>
        @endif

        @if ($worker->tamara_paid_at)
            <span class="cvp-badge" style="background:#f0fdfa;color:#0f766e">
                {{ __('cv-panel.tamara.paid_badge') }}
            </span>
        @elseif ($worker->canRecordTamaraBy($me))
            <button type="submit" form="tamara-{{ $worker->id }}" class="cvp-btn cvp-btn-ghost">
                سدّد تمارا
            </button>
        @endif

        @if ($worker->canBeUnassignedBy($me))
            <button type="submit" form="cancel-{{ $worker->id }}" class="cvp-btn cvp-btn-danger">
                إلغاء الحجز
            </button>
        @endif

        @unless ($mayActOnReservation)
            <span class="small text-muted">{{ __('cv-panel.reserve.owner_only') }}</span>
        @endunless

    @endif
</div>
