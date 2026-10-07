<?php

namespace Tests\Feature\CvPanel;

use App\Models\Client;
use App\Models\Nationality;
use App\Models\RecruitmentContract;
use App\Models\Worker;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class WorkerGuardTest extends TestCase
{
    use RefreshDatabase;

    private function nationality(): Nationality
    {
        return Nationality::firstOrCreate(
            ['slug' => 'ethiopia'],
            ['name_ar' => 'إثيوبيا', 'code' => 'et'],
        );
    }

    private function client(): Client
    {
        return Client::create(['name' => 'عميل تجريبي', 'phone' => '0500000000']);
    }

    private function worker(array $attributes = []): Worker
    {
        return Worker::create(array_merge([
            'name' => 'عاملة تجريبية',
            'nationality_id' => $this->nationality()->id,
            'experience' => '1-3',
            'religion' => 'muslim',
            'cv_path' => 'cvs/test.pdf',
            'cv_disk' => 'cv_private',
            'status' => Worker::STATUS_AVAILABLE,
        ], $attributes));
    }

    public function test_a_fresh_available_worker_is_not_withdrawn(): void
    {
        $this->assertNull($this->worker()->cv_withdrawn_at);
    }

    public function test_reserving_stamps_the_public_withdrawal(): void
    {
        $worker = $this->worker();

        $worker->update([
            'client_id' => $this->client()->id,
            'status' => Worker::STATUS_RESERVED,
        ]);

        $this->assertNotNull($worker->fresh()->cv_withdrawn_at);
    }

    public function test_withdrawal_survives_a_cancelled_reservation(): void
    {
        $worker = $this->worker();
        $worker->update([
            'client_id' => $this->client()->id,
            'status' => Worker::STATUS_RESERVED,
        ]);

        $stampedAt = $worker->fresh()->cv_withdrawn_at;

        // The legitimate release path clears the client in the same update.
        $worker->update([
            'status' => Worker::STATUS_AVAILABLE,
            'client_id' => null,
            'assigned_by_admin_id' => null,
            'assigned_at' => null,
        ]);

        $worker = $worker->fresh();

        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->status);
        $this->assertNull($worker->client_id);
        // Permanent: the CV stays off the public site.
        $this->assertNotNull($worker->cv_withdrawn_at);
        $this->assertEquals($stampedAt, $worker->cv_withdrawn_at);
    }

    public function test_guard_blocks_available_while_a_client_is_still_linked(): void
    {
        $worker = $this->worker();
        $worker->update([
            'client_id' => $this->client()->id,
            'status' => Worker::STATUS_RESERVED,
        ]);

        // Releasing the status without clearing the client is not a real
        // release, so the guard forces it back to assigned.
        $worker->update(['status' => Worker::STATUS_AVAILABLE]);

        $this->assertSame(Worker::STATUS_ASSIGNED, $worker->fresh()->status);
    }

    public function test_guard_blocks_available_while_an_open_contract_exists(): void
    {
        $worker = $this->worker();
        $client = $this->client();

        $worker->update(['client_id' => $client->id, 'status' => Worker::STATUS_RESERVED]);

        RecruitmentContract::create([
            'worker_id' => $worker->id,
            'client_id' => $client->id,
            'current_status' => RecruitmentContract::STATUS_ACTIVE,
        ]);

        // Even the otherwise-legitimate clear-the-client path cannot release a
        // worker who is bound to a live contract.
        $worker->refresh();
        $worker->update(['status' => Worker::STATUS_AVAILABLE, 'client_id' => null]);

        $this->assertSame(Worker::STATUS_ASSIGNED, $worker->fresh()->status);
    }

    public function test_an_ended_contract_does_not_block_release(): void
    {
        $worker = $this->worker();
        $client = $this->client();

        $worker->update(['client_id' => $client->id, 'status' => Worker::STATUS_RESERVED]);

        RecruitmentContract::create([
            'worker_id' => $worker->id,
            'client_id' => $client->id,
            'current_status' => RecruitmentContract::STATUS_RETURNED,
        ]);

        $worker->refresh();
        $worker->update([
            'status' => Worker::STATUS_AVAILABLE,
            'client_id' => null,
            'assigned_by_admin_id' => null,
            'assigned_at' => null,
        ]);

        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->fresh()->status);
    }

    public function test_reserved_without_a_client_or_contract_is_reverted(): void
    {
        $worker = $this->worker();

        // A stuck row: reserved but held for nobody.
        $worker->update(['status' => Worker::STATUS_RESERVED]);

        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->fresh()->status);
    }

    public function test_publicly_visible_scope_excludes_withdrawn_and_reserved(): void
    {
        $visible = $this->worker();

        $reserved = $this->worker();
        $reserved->update(['client_id' => $this->client()->id, 'status' => Worker::STATUS_RESERVED]);

        // Available again, but permanently withdrawn from the public site.
        $reserved->update([
            'status' => Worker::STATUS_AVAILABLE,
            'client_id' => null,
        ]);

        $ids = Worker::publiclyVisible()->pluck('id')->all();

        $this->assertContains($visible->id, $ids);
        $this->assertNotContains($reserved->id, $ids);
    }

    public function test_reservation_hours_are_display_only_and_reflect_tamara(): void
    {
        $worker = $this->worker();
        $this->assertSame(72, $worker->reservationHours());

        $worker->update([
            'client_id' => $this->client()->id,
            'status' => Worker::STATUS_RESERVED,
            'tamara_paid_at' => now(),
        ]);

        $this->assertSame(120, $worker->fresh()->reservationHours());
    }
}
