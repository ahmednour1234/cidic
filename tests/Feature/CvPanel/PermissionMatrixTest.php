<?php

namespace Tests\Feature\CvPanel;

use App\Enums\Department;
use App\Enums\UserRole;
use App\Models\Worker;
use App\Services\CvPanel\CvReservationService;
use App\Support\CvPanel\CvPanelPermissions;
use Illuminate\Foundation\Testing\RefreshDatabase;

class PermissionMatrixTest extends CvPanelTestCase
{
    use RefreshDatabase;

    public function test_upload_is_for_coordinators_and_super_admins(): void
    {
        $this->assertTrue(CvPanelPermissions::canUpload($this->coordinator()));
        $this->assertTrue(CvPanelPermissions::canUpload($this->superAdmin()));
        $this->assertFalse(CvPanelPermissions::canUpload($this->agent()));
        $this->assertFalse(CvPanelPermissions::canUpload($this->manager()));
    }

    public function test_coordinators_cannot_reserve(): void
    {
        $this->assertFalse(CvPanelPermissions::canReserve($this->coordinator()));
        $this->assertTrue(CvPanelPermissions::canReserve($this->agent()));
        $this->assertTrue(CvPanelPermissions::canReserve($this->manager()));
        $this->assertTrue(CvPanelPermissions::canReserve($this->superAdmin()));
    }

    public function test_user_management_is_for_managers_and_super_admins(): void
    {
        $this->assertTrue(CvPanelPermissions::canManageUsers($this->manager()));
        $this->assertTrue(CvPanelPermissions::canManageUsers($this->superAdmin()));
        $this->assertFalse(CvPanelPermissions::canManageUsers($this->coordinator()));
        $this->assertFalse(CvPanelPermissions::canManageUsers($this->agent()));
    }

    public function test_an_inactive_user_holds_no_permissions(): void
    {
        $inactive = $this->panelUser(Department::Coordination, active: false);

        $this->assertFalse(CvPanelPermissions::canUpload($inactive));
        $this->assertFalse(CvPanelPermissions::canDelete($inactive));
        $this->assertFalse($inactive->canAccessCvPanel());
    }

    public function test_a_user_without_a_department_has_no_panel_access(): void
    {
        $outsider = $this->panelUser(null, UserRole::Staff);

        $this->assertFalse($outsider->canAccessCvPanel());
        $this->assertFalse(CvPanelPermissions::canUpload($outsider));
    }

    public function test_a_coordinator_is_scoped_to_their_own_nationalities(): void
    {
        $mine = $this->nationality('ethiopia', 'et');
        $theirs = $this->nationality('kenya', 'ke');

        $coordinator = $this->coordinator([$mine]);

        $this->assertSame([$mine->id], $coordinator->managedNationalities());
        $this->assertTrue($coordinator->managesNationality($mine->id));
        $this->assertFalse($coordinator->managesNationality($theirs->id));
    }

    public function test_everyone_else_sees_every_nationality(): void
    {
        $this->nationality();

        // Null means unrestricted.
        $this->assertNull($this->superAdmin()->managedNationalities());
        $this->assertNull($this->agent()->managedNationalities());
        $this->assertNull($this->manager()->managedNationalities());
    }

    public function test_a_booked_worker_is_never_deletable(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $free = $this->worker(['nationality_id' => $nationality->id]);
        $this->assertTrue(CvPanelPermissions::canDeleteWorker($coordinator, $free));

        $reserved = $this->worker(['nationality_id' => $nationality->id]);
        app(CvReservationService::class)->reserve($reserved, $this->client(), $this->agent());

        $this->assertFalse(CvPanelPermissions::canDeleteWorker($coordinator, $reserved->fresh()));
    }

    public function test_a_coordinator_cannot_delete_outside_their_nationalities(): void
    {
        $mine = $this->nationality('ethiopia', 'et');
        $theirs = $this->nationality('kenya', 'ke');

        $coordinator = $this->coordinator([$mine]);
        $worker = $this->worker(['nationality_id' => $theirs->id]);

        $this->assertFalse(CvPanelPermissions::canDeleteWorker($coordinator, $worker));
    }

    public function test_agents_cannot_delete_even_a_free_cv(): void
    {
        $worker = $this->worker();

        $this->assertFalse(CvPanelPermissions::canDeleteWorker($this->agent(), $worker));
    }

    public function test_only_the_reserver_may_act_on_the_reservation(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        app(CvReservationService::class)->reserve($worker, $this->client(), $reserver);

        $worker = $worker->fresh();
        $other = $this->agent();

        $this->assertTrue($worker->canBeUnassignedBy($reserver));
        $this->assertTrue($worker->canRecordTamaraBy($reserver));
        $this->assertTrue($worker->canCreateContractBy($reserver));

        $this->assertFalse($worker->canBeUnassignedBy($other));
        $this->assertFalse($worker->canRecordTamaraBy($other));
        // A branch manager is not the reserver either.
        $this->assertFalse($worker->canBeUnassignedBy($this->manager()));

        $this->assertTrue($worker->canBeUnassignedBy($this->superAdmin()));
    }

    public function test_an_available_worker_has_no_reservation_actions(): void
    {
        $worker = $this->worker();

        $this->assertFalse($worker->canBeUnassignedBy($this->superAdmin()));
        $this->assertFalse($worker->canRecordTamaraBy($this->superAdmin()));
    }
}
