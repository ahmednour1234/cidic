<?php

namespace App\Services;

use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class FileUploadService
{
    public function __construct(protected string $disk = 'public') {}

    /**
     * Store an upload under $directory using a generated name.
     *
     * The original filename is never used for the stored name: only the
     * extension is taken, and it is derived from the detected MIME type where
     * possible rather than the client-supplied extension.
     */
    public function store(UploadedFile $file, string $directory): string
    {
        $extension = $this->safeExtension($file);
        $name = Str::uuid()->toString() . ($extension ? '.' . $extension : '');

        return $file->storeAs(trim($directory, '/'), $name, $this->disk);
    }

    /**
     * Store a new file and remove the previous one once the write succeeded.
     */
    public function replace(UploadedFile $file, string $directory, ?string $oldPath = null): string
    {
        $path = $this->store($file, $directory);

        if ($oldPath && $oldPath !== $path) {
            $this->delete($oldPath);
        }

        return $path;
    }

    public function delete(?string $path): void
    {
        if (blank($path)) {
            return;
        }

        $disk = Storage::disk($this->disk);

        if ($disk->exists($path)) {
            $disk->delete($path);
        }
    }

    /**
     * Prefer an extension guessed from file content; fall back to a sanitised
     * client extension so we never place arbitrary user text in the path.
     */
    protected function safeExtension(UploadedFile $file): string
    {
        $guessed = $file->guessExtension();

        if (filled($guessed)) {
            return strtolower($guessed);
        }

        $client = strtolower($file->getClientOriginalExtension());

        return preg_replace('/[^a-z0-9]/', '', $client) ?: 'bin';
    }
}
