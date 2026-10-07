<?php

namespace Tests\Feature\CvPanel;

use App\Models\RecruitmentContract;
use App\Models\Worker;
use App\Services\CvPanel\CvReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;

class MaintenanceCommandTest extends CvPanelTestCase
{
    use RefreshDatabase;

    public function test_restore_withdrawn_is_a_dry_run_by_default(): void
    {
        $worker = $this->worker();
        app(CvReservationService::class)->reserve($worker, $this->client(), $this->agent());
        app(CvReservationService::class)->cancel($worker->fresh(), $this->superAdmin());

        $this->assertNotNull($worker->fresh()->cv_withdrawn_at);

        $this->artisan('workers:restore-withdrawn')->assertSuccessful();

        // Nothing written without --apply.
        $this->assertNotNull($worker->fresh()->cv_withdrawn_at);
    }

    public function test_restore_withdrawn_clears_the_stamp_with_apply(): void
    {
        $worker = $this->worker();
        app(CvReservationService::class)->reserve($worker, $this->client(), $this->agent());
        app(CvReservationService::class)->cancel($worker->fresh(), $this->superAdmin());

        $this->artisan('workers:restore-withdrawn --apply')->assertSuccessful();

        $this->assertNull($worker->fresh()->cv_withdrawn_at);
    }

    public function test_restore_withdrawn_skips_a_booked_cv(): void
    {
        $worker = $this->worker();
        app(CvReservationService::class)->reserve($worker, $this->client(), $this->agent());

        $this->artisan('workers:restore-withdrawn --apply')->assertSuccessful();

        // Still reserved, so it must stay withdrawn.
        $this->assertNotNull($worker->fresh()->cv_withdrawn_at);
    }

    public function test_fix_available_with_client_repairs_a_bypassed_row(): void
    {
        $worker = $this->worker();
        $client = $this->client();

        // Simulates the historical bug: a direct query update that skipped the
        // model guards entirely.
        Worker::withoutEvents(fn () => Worker::where('id', $worker->id)->update([
            'client_id' => $client->id,
            'status' => Worker::STATUS_AVAILABLE,
        ]));

        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->fresh()->status);

        $this->artisan('workers:fix-available-with-client --apply')->assertSuccessful();

        $fixed = $worker->fresh();

        $this->assertSame(Worker::STATUS_ASSIGNED, $fixed->status);
        $this->assertSame($client->id, $fixed->client_id);
        $this->assertNotNull($fixed->cv_withdrawn_at);
    }

    public function test_sync_contract_status_assigns_workers_with_an_open_contract(): void
    {
        $worker = $this->worker();
        $client = $this->client();

        RecruitmentContract::create([
            'worker_id' => $worker->id,
            'client_id' => $client->id,
            'current_status' => RecruitmentContract::STATUS_ACTIVE,
        ]);

        $this->artisan('workers:sync-contract-status --apply')->assertSuccessful();

        $this->assertSame(Worker::STATUS_ASSIGNED, $worker->fresh()->status);
    }

    public function test_sync_contract_status_is_a_dry_run_by_default(): void
    {
        $worker = $this->worker();

        RecruitmentContract::create([
            'worker_id' => $worker->id,
            'client_id' => $this->client()->id,
            'current_status' => RecruitmentContract::STATUS_ACTIVE,
        ]);

        $this->artisan('workers:sync-contract-status')->assertSuccessful();

        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->fresh()->status);
    }
}
