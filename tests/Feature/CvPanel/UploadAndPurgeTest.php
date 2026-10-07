<?php

namespace Tests\Feature\CvPanel;

use App\Models\RecruitmentContract;
use App\Models\Worker;
use App\Services\CvPanel\CvPurgeService;
use App\Services\CvPanel\CvReservationService;
use App\Services\CvPanel\CvUploadService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadAndPurgeTest extends CvPanelTestCase
{
    use RefreshDatabase;

    private CvUploadService $uploads;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('cv_private');
        $this->uploads = app(CvUploadService::class);
    }

    private function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 100, 'application/pdf');
    }

    public function test_uploading_creates_one_worker_per_file(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $result = $this->uploads->handle(
            files: [$this->pdf('فاطمة.pdf'), $this->pdf('أمينة.pdf')],
            nationalityId: $nationality->id,
            experience: '1-3',
            religion: 'muslim',
            profession: 'عاملة منزلية',
            actor: $coordinator,
        );

        $this->assertSame(2, $result['created']);
        $this->assertSame(2, Worker::count());

        $worker = Worker::where('original_cv_name', 'فاطمة.pdf')->first();

        // The name comes from the file name, without the extension.
        $this->assertSame('فاطمة', $worker->name);
        $this->assertSame('cv_private', $worker->cv_disk);
        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->status);
        $this->assertSame('female', $worker->gender);
        $this->assertSame($coordinator->id, $worker->admin_id);
        Storage::disk('cv_private')->assertExists($worker->cv_path);
    }

    public function test_a_duplicate_file_name_is_skipped(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $args = fn (array $files) => [
            'files' => $files,
            'nationalityId' => $nationality->id,
            'experience' => '1-3',
            'religion' => 'muslim',
            'profession' => null,
            'actor' => $coordinator,
        ];

        $this->uploads->handle(...$args([$this->pdf('فاطمة.pdf')]));

        $result = $this->uploads->handle(...$args([
            $this->pdf('فاطمة.pdf'),
            $this->pdf('جديدة.pdf'),
        ]));

        $this->assertSame(1, $result['created']);
        $this->assertSame(['فاطمة.pdf'], $result['duplicates']);
        $this->assertSame(2, Worker::count());
    }

    public function test_purge_removes_only_free_cvs(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);
        $reservations = app(CvReservationService::class);

        $free = $this->worker(['nationality_id' => $nationality->id]);

        $reserved = $this->worker(['nationality_id' => $nationality->id]);
        $reservations->reserve($reserved, $this->client(), $this->agent());

        $contracted = $this->worker(['nationality_id' => $nationality->id]);
        RecruitmentContract::create([
            'worker_id' => $contracted->id,
            'client_id' => $this->client('صاحب عقد')->id,
            'current_status' => RecruitmentContract::STATUS_ACTIVE,
        ]);

        $purged = app(CvPurgeService::class)->purge($nationality->id, $coordinator);

        $this->assertSame(1, $purged);
        $this->assertSoftDeleted('workers', ['id' => $free->id]);
        $this->assertNotSoftDeleted('workers', ['id' => $reserved->id]);
        $this->assertNotSoftDeleted('workers', ['id' => $contracted->id]);
    }

    public function test_purge_on_upload_runs_before_the_new_batch_is_stored(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $old = $this->worker(['nationality_id' => $nationality->id]);

        $result = $this->uploads->handle(
            files: [$this->pdf('جديدة.pdf')],
            nationalityId: $nationality->id,
            experience: 'none',
            religion: 'christian',
            profession: null,
            actor: $coordinator,
            purgeOld: true,
        );

        $this->assertSame(1, $result['created']);
        $this->assertSame(1, $result['purged']);
        $this->assertSoftDeleted('workers', ['id' => $old->id]);
        // The freshly uploaded CV survives the purge that ran before it.
        $this->assertSame(1, Worker::whereNull('deleted_at')->count());
    }

    public function test_soft_deleted_rows_keep_their_file(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $this->uploads->handle(
            files: [$this->pdf('فاطمة.pdf')],
            nationalityId: $nationality->id,
            experience: '5+',
            religion: 'muslim',
            profession: null,
            actor: $coordinator,
        );

        $worker = Worker::first();
        $path = $worker->cv_path;

        $worker->delete();

        $this->assertSoftDeleted('workers', ['id' => $worker->id]);
        Storage::disk('cv_private')->assertExists($path);
    }
}
