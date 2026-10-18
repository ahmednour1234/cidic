@php
    use App\Support\CvPanel\CvPanelPermissions;

    $me = auth()->user();
@endphp

{{--
    One <form> per row action, rendered outside the table and the bulk form.
    The buttons live in the row and point here with form="…": a nested form is
    invalid HTML and the inner one never submits.
--}}

@if (CvPanelPermissions::canDeleteWorker($me, $worker))
    <form method="POST" action="{{ route('cv-panel.cvs.destroy', $worker->id) }}"
          id="delete-{{ $worker->id }}" class="d-none"
          onsubmit="return confirm(@js(__('cvp-cvs.confirm_delete')))">
        @csrf
        @method('DELETE')
    </form>
@endif

@if ($worker->canBeUnassignedBy($me))
    <form method="POST" action="{{ route('cv-panel.cvs.reserve.cancel', $worker->id) }}"
          id="cancel-{{ $worker->id }}" class="d-none"
          onsubmit="return confirm(@js(__('cvp-cvs.confirm_cancel')))">
        @csrf
        @method('DELETE')
    </form>
@endif

@if ($worker->canRecordTamaraBy($me))
    <form method="POST" action="{{ route('cv-panel.cvs.tamara', $worker->id) }}"
          id="tamara-{{ $worker->id }}" class="d-none"
          onsubmit="return confirm(@js(__('cvp-cvs.confirm_tamara')))">
        @csrf
    </form>
@endif

@if ($worker->canCreateContractBy($me))
    <form method="POST" action="{{ route('cv-panel.cvs.contract', $worker->id) }}"
          id="contract-{{ $worker->id }}" class="d-none"
          onsubmit="return confirm(@js(__('cv-panel.contract.confirm')))">
        @csrf
    </form>
@endif
