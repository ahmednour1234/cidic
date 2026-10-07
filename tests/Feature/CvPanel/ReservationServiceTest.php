<?php

namespace Tests\Feature\CvPanel;

use App\Models\RecruitmentContract;
use App\Models\Worker;
use App\Services\CvPanel\CvReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use RuntimeException;

class ReservationServiceTest extends CvPanelTestCase
{
    use RefreshDatabase;

    private CvReservationService $service;

    protected function setUp(): void
    {
        parent::setUp();
        $this->service = app(CvReservationService::class);
    }

    public function test_reserving_holds_the_cv_and_withdraws_it(): void
    {
        $worker = $this->worker();
        $client = $this->client();
        $agent = $this->agent();

        $this->service->reserve($worker, $client, $agent);

        $worker = $worker->fresh();

        $this->assertSame(Worker::STATUS_RESERVED, $worker->status);
        $this->assertSame($client->id, $worker->client_id);
        $this->assertSame($agent->id, $worker->assigned_by_admin_id);
        $this->assertNotNull($worker->assigned_at);
        $this->assertNotNull($worker->cv_withdrawn_at);
    }

    public function test_only_one_of_two_concurrent_reservations_succeeds(): void
    {
        $worker = $this->worker();
        $first = $this->agent();
        $second = $this->agent();

        $this->service->reserve($worker, $this->client('الأول'), $first);

        // The second agent is working from a stale copy of the same row.
        $stale = Worker::find($worker->id);
        $stale->setRawAttributes(
            array_merge($stale->getAttributes(), [
                'status' => Worker::STATUS_AVAILABLE,
                'client_id' => null,
            ]),
            true,
        );

        $this->expectException(RuntimeException::class);

        $this->service->reserve($stale, $this->client('الثاني'), $second);
    }

    public function test_a_reserved_cv_cannot_be_reserved_again(): void
    {
        $worker = $this->worker();
        $this->service->reserve($worker, $this->client(), $this->agent());

        $this->expectException(RuntimeException::class);

        $this->service->reserve($worker->fresh(), $this->client('آخر'), $this->agent());
    }

    public function test_only_the_reserver_or_a_super_admin_may_cancel(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        $this->service->reserve($worker, $this->client(), $reserver);

        $other = $this->agent();

        $this->expectException(RuntimeException::class);

        $this->service->cancel($worker->fresh(), $other);
    }

    public function test_a_super_admin_may_cancel_someone_elses_reservation(): void
    {
        $worker = $this->worker();
        $this->service->reserve($worker, $this->client(), $this->agent());

        $this->service->cancel($worker->fresh(), $this->superAdmin());

        $worker = $worker->fresh();

        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->status);
        $this->assertNull($worker->client_id);
        $this->assertNull($worker->assigned_by_admin_id);
        // Withdrawal is permanent.
        $this->assertNotNull($worker->cv_withdrawn_at);
    }

    public function test_a_branch_manager_may_not_cancel_someone_elses_reservation(): void
    {
        $worker = $this->worker();
        $this->service->reserve($worker, $this->client(), $this->agent());

        $this->expectException(RuntimeException::class);

        $this->service->cancel($worker->fresh(), $this->manager());
    }

    public function test_a_reservation_with_a_contract_cannot_be_cancelled_here(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        $client = $this->client();
        $this->service->reserve($worker, $client, $reserver);

        RecruitmentContract::create([
            'worker_id' => $worker->id,
            'client_id' => $client->id,
            'current_status' => RecruitmentContract::STATUS_ACTIVE,
        ]);

        $this->expectExceptionMessage('صفحة العقد');

        $this->service->cancel($worker->fresh(), $reserver);
    }

    public function test_tamara_is_recorded_without_changing_status_or_client(): void
    {
        $worker = $this->worker();
        $client = $this->client();
        $reserver = $this->agent();
        $this->service->reserve($worker, $client, $reserver);

        $this->service->recordTamara($worker->fresh(), $reserver);

        $worker = $worker->fresh();

        $this->assertNotNull($worker->tamara_paid_at);
        $this->assertSame($reserver->id, $worker->tamara_paid_by_admin_id);
        $this->assertSame(Worker::STATUS_RESERVED, $worker->status);
        $this->assertSame($client->id, $worker->client_id);
    }

    public function test_tamara_cannot_be_recorded_twice(): void
    {
        $worker = $this->worker();
        $reserver = $this->agent();
        $this->service->reserve($worker, $this->client(), $reserver);
        $this->service->recordTamara($worker->fresh(), $reserver);

        $this->expectException(RuntimeException::class);

        $this->service->recordTamara($worker->fresh(), $reserver);
    }

    public function test_marking_assigned_ends_the_cycle(): void
    {
        $worker = $this->worker();
        $this->service->reserve($worker, $this->client(), $this->agent());

        $this->service->markAssigned($worker->fresh(), $this->coordinator());

        $this->assertSame(Worker::STATUS_ASSIGNED, $worker->fresh()->status);
    }

    public function test_an_inline_client_is_created_from_a_name_and_phone(): void
    {
        $agent = $this->agent();

        $client = $this->service->resolveClient(null, 'عميل واتساب', '0501234567', $agent);

        $this->assertSame('عميل واتساب', $client->name);
        $this->assertSame('confirmed', $client->classification);
        $this->assertSame($agent->branch_id, $client->branch_id);
    }

    public function test_resolving_a_client_without_a_name_or_phone_fails(): void
    {
        $this->expectException(RuntimeException::class);

        $this->service->resolveClient(null, '', '', $this->agent());
    }
}
