<?php

namespace Tests\Feature\CvPanel;

use App\Models\RecruitmentContract;
use App\Models\Worker;
use App\Services\CvPanel\ContractService;
use App\Services\CvPanel\CvReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

class ContractServiceTest extends CvPanelTestCase
{
    use RefreshDatabase;

    private ContractService $contracts;
    private CvReservationService $reservations;

    protected function setUp(): void
    {
        parent::setUp();
        $this->contracts = app(ContractService::class);
        $this->reservations = app(CvReservationService::class);
    }

    public function test_creating_a_contract_assigns_the_worker(): void
    {
        $worker = $this->worker();
        $client = $this->client();
        $reserver = $this->agent();

        $this->reservations->reserve($worker, $client, $reserver);

        $contract = $this->contracts->createFromReservation($worker->fresh(), $reserver);

        $this->assertSame($worker->id, $contract->worker_id);
        $this->assertSame($client->id, $contract->client_id);
        $this->assertSame(RecruitmentContract::STATUS_ACTIVE, $contract->current_status);
        $this->assertNotNull($contract->number);

        $worker = $worker->fresh();

        $this->assertSame(Worker::STATUS_ASSIGNED, $worker->status);
        $this->assertSame($client->id, $worker->client_id);
        $this->assertNotNull($worker->cv_withdrawn_at);
    }

    public function test_an_assigned_worker_leaves_the_panel_list(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        $this->reservations->reserve($worker, $this->client(), $reserver);
        $this->contracts->createFromReservation($worker->fresh(), $reserver);

        // The panel list hides assigned rows: that is the end of the cycle.
        $listed = Worker::query()
            ->where('status', '!=', Worker::STATUS_ASSIGNED)
            ->pluck('id');

        $this->assertNotContains($worker->id, $listed);
    }

    public function test_only_the_reserver_or_a_super_admin_may_create_the_contract(): void
    {
        $worker = $this->worker();
        $this->reservations->reserve($worker, $this->client(), $this->agent());

        $this->expectException(RuntimeException::class);

        $this->contracts->createFromReservation($worker->fresh(), $this->agent());
    }

    public function test_a_contract_cannot_be_created_for_an_available_cv(): void
    {
        $worker = $this->worker();

        $this->expectException(RuntimeException::class);

        $this->contracts->createFromReservation($worker, $this->superAdmin());
    }

    public function test_a_second_contract_is_refused(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        $this->reservations->reserve($worker, $this->client(), $reserver);
        $this->contracts->createFromReservation($worker->fresh(), $reserver);

        $this->expectException(RuntimeException::class);

        $this->contracts->createFromReservation($worker->fresh(), $this->superAdmin());
    }

    public function test_an_assigned_worker_cannot_be_released_from_the_panel(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        $this->reservations->reserve($worker, $this->client(), $reserver);
        $this->contracts->createFromReservation($worker->fresh(), $reserver);

        // Unlinking happens on the contract, never here.
        $this->expectException(RuntimeException::class);

        $this->reservations->cancel($worker->fresh(), $this->superAdmin());
    }

    public function test_contract_numbers_are_sequential(): void
    {
        $numbers = [];

        foreach (range(1, 3) as $i) {
            $worker = $this->worker();
            $reserver = $this->agent();
            $this->reservations->reserve($worker, $this->client('عميل '.$i), $reserver);
            $numbers[] = $this->contracts->createFromReservation($worker->fresh(), $reserver)->number;
        }

        $this->assertSame($numbers, array_unique($numbers));
        $this->assertStringEndsWith('0001', $numbers[0]);
        $this->assertStringEndsWith('0003', $numbers[2]);
    }
}
