<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

/**
 * Link public/storage without shelling out.
 *
 * Laravel's storage:link falls back to exec('ln -s ...') when the symlink()
 * function is unavailable, and many shared hosts disable exec() outright —
 * which makes the built-in command fatal there. This creates the link with
 * PHP only, and copies the files when the host forbids symlinks altogether.
 */
class StorageLinkSafe extends Command
{
    protected $signature = 'storage:link-safe {--force : Replace an existing link or directory}';

    protected $description = 'Create the public/storage link without requiring exec()';

    public function handle(): int
    {
        $target = storage_path('app/public');
        $link = public_path('storage');

        if (! is_dir($target)) {
            File::makeDirectory($target, 0755, true);
            $this->line("Created missing target: {$target}");
        }

        if (file_exists($link) || is_link($link)) {
            if (! $this->option('force')) {
                $this->warn("public/storage already exists. Re-run with --force to replace it.");

                return self::SUCCESS;
            }

            is_link($link) ? unlink($link) : File::deleteDirectory($link);
        }

        // Preferred: a real symlink, so uploads appear without duplication.
        if (function_exists('symlink') && @symlink($target, $link)) {
            $this->info('Linked public/storage -> storage/app/public');

            return self::SUCCESS;
        }

        // Fallback: copy. Uploads made after this run need the command again,
        // so it is reported clearly rather than silently succeeding.
        $this->warn('symlink() unavailable or refused; copying files instead.');

        File::copyDirectory($target, $link);

        $this->info('Copied storage/app/public -> public/storage');
        $this->line('Re-run this command after new uploads, as copies do not update themselves.');

        return self::SUCCESS;
    }
}
