<?php

namespace App\Services\CvPanel;

use App\Models\Nationality;
use App\Models\User;
use App\Models\Worker;
use App\Models\WorkerActivityLog;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Throwable;

final class CvUploadService
{
    public const DISK = 'cv_private';
    public const DIRECTORY = 'cvs';

    public function __construct(private readonly CvPurgeService $purge) {}

    /**
     * Store a batch of CV PDFs as workers.
     *
     * @param  array<int, UploadedFile>  $files
     * @return array{created:int, duplicates:array<int,string>, purged:int}
     */
    public function handle(
        array $files,
        int $nationalityId,
        string $experience,
        string $religion,
        ?string $profession,
        User $actor,
        bool $purgeOld = false,
    ): array {
        // The purge runs first, so a re-upload of the same batch is not
        // immediately removed by it.
        $purged = $purgeOld ? $this->purge->purge($nationalityId, $actor) : 0;

        $nationality = Nationality::find($nationalityId);
        $created = 0;
        $duplicates = [];

        foreach ($files as $file) {
            $originalName = $file->getClientOriginalName();

            if ($this->isDuplicate($originalName)) {
                $duplicates[] = $originalName;

                continue;
            }

            $path = $file->store(self::DIRECTORY, self::DISK);

            if ($path === false) {
                continue;
            }

            try {
                $worker = DB::transaction(fn () => Worker::create([
                    'name' => pathinfo($originalName, PATHINFO_FILENAME),
                    'nationality_id' => $nationalityId,
                    'experience' => $experience,
                    'religion' => $religion,
                    'profession' => $profession,
                    'gender' => 'female',
                    'cv_path' => $path,
                    'cv_disk' => self::DISK,
                    'original_cv_name' => $originalName,
                    'status' => Worker::STATUS_AVAILABLE,
                    'admin_id' => $actor->id,
                    'branch_id' => $actor->branch_id,
                ]));
            } catch (Throwable $e) {
                // The row failed, so the orphaned file must not linger.
                Storage::disk(self::DISK)->delete($path);

                throw $e;
            }

            $created++;

            WorkerActivityLogger::log(
                $worker,
                WorkerActivityLog::ACTION_CV_UPLOADED,
                sprintf(
                    'رُفعت السيرة الذاتية من لوحة إدارة CV — %s، %s، %s',
                    $nationality?->display_name ?? '—',
                    __('workers.experience.'.$experience),
                    __('workers.religion.'.$religion),
                ),
                $actor,
            );
        }

        if ($created > 0) {
            CvNotifier::cvUploaded($created, $nationalityId, $actor);
        }

        return [
            'created' => $created,
            'duplicates' => $duplicates,
            'purged' => $purged,
        ];
    }

    /** Duplicate detection is by the uploaded file's original name. */
    private function isDuplicate(string $originalName): bool
    {
        return Worker::query()->where('original_cv_name', $originalName)->exists();
    }
}
