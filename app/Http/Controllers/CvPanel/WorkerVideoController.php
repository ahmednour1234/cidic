<?php

namespace App\Http\Controllers\CvPanel;

use App\Http\Controllers\Controller;
use App\Models\Worker;
use App\Support\CvPanel\CvPanelPermissions;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Illuminate\View\View;

/**
 * A worker's video: an external link (Google Drive, YouTube...) or a file
 * uploaded in small chunks, so large phone videos pass PHP's upload limits.
 */
class WorkerVideoController extends Controller
{
    public const MAX_BYTES = 300 * 1024 * 1024;

    public const CHUNK_BYTES = 1024 * 1024;

    private const EXTENSIONS = ['mp4', 'mov', 'webm', 'm4v', '3gp'];

    public function edit(Request $request, int $id): View
    {
        return view('cv-panel.cvs.video', ['worker' => $this->findEditable($request, $id)]);
    }

    public function saveLink(Request $request, int $id): RedirectResponse
    {
        $worker = $this->findEditable($request, $id);

        $data = $request->validate([
            'video_url' => ['required', 'url:http,https', 'max:2048'],
        ]);

        $this->deleteFile($worker);
        $worker->update(['video_url' => $data['video_url'], 'video_path' => null]);

        return back()->with('success', __('cvp-video.saved'));
    }

    /**
     * Receives one chunk. Chunks are appended in order to a temp file; the
     * last one moves it into place and replaces any earlier video or link.
     */
    public function chunk(Request $request, int $id): JsonResponse
    {
        $worker = $this->findEditable($request, $id);

        $data = $request->validate([
            'upload_id' => ['required', 'string', 'regex:/^[A-Za-z0-9_-]{8,64}$/'],
            'index' => ['required', 'integer', 'min:0'],
            'total' => ['required', 'integer', 'min:1', 'max:'.(int) ceil(self::MAX_BYTES / self::CHUNK_BYTES)],
            'name' => ['required', 'string', 'max:255'],
            'chunk' => ['required', 'file', 'max:'.(int) ceil(self::CHUNK_BYTES / 1024) + 64],
        ]);

        $ext = strtolower(pathinfo($data['name'], PATHINFO_EXTENSION));
        if (! in_array($ext, self::EXTENSIONS, true)) {
            return response()->json(['message' => __('cvp-video.bad_type')], 422);
        }

        $disk = Storage::disk('video_private');
        $disk->makeDirectory('chunks');
        $tmp = $disk->path('chunks/'.$worker->id.'-'.$data['upload_id'].'.part');

        // Chunk 0 starts fresh; later chunks must arrive in order.
        if ((int) $data['index'] === 0) {
            @unlink($tmp);
        } elseif (! is_file($tmp)) {
            return response()->json(['message' => __('cvp-video.failed')], 409);
        }

        $out = fopen($tmp, 'ab');
        $in = fopen($request->file('chunk')->getRealPath(), 'rb');
        stream_copy_to_stream($in, $out);
        fclose($in);
        fclose($out);

        if (filesize($tmp) > self::MAX_BYTES) {
            @unlink($tmp);

            return response()->json(['message' => __('cvp-video.too_big')], 422);
        }

        if ((int) $data['index'] < (int) $data['total'] - 1) {
            return response()->json(['done' => false]);
        }

        $path = $worker->id.'/'.Str::random(24).'.'.$ext;
        $disk->makeDirectory((string) $worker->id);
        rename($tmp, $disk->path($path));

        $this->deleteFile($worker);
        $worker->update(['video_path' => $path, 'video_url' => null]);

        return response()->json(['done' => true]);
    }

    /** Preview for staff, regardless of public visibility. */
    public function file(Request $request, int $id)
    {
        $worker = $this->findEditable($request, $id);
        $disk = Storage::disk('video_private');

        abort_unless(filled($worker->video_path) && $disk->exists($worker->video_path), 404);

        return response()->file($disk->path($worker->video_path));
    }

    public function destroy(Request $request, int $id): RedirectResponse
    {
        $worker = $this->findEditable($request, $id);

        $this->deleteFile($worker);
        $worker->update(['video_path' => null, 'video_url' => null]);

        return back()->with('success', __('cvp-video.deleted'));
    }

    private function deleteFile(Worker $worker): void
    {
        if (filled($worker->video_path)) {
            Storage::disk('video_private')->delete($worker->video_path);
        }
    }

    /** Same people who upload CVs for this nationality manage its videos. */
    private function findEditable(Request $request, int $id): Worker
    {
        $worker = Worker::findOrFail($id);

        abort_unless(CvPanelPermissions::canUploadToNationality($request->user(), $worker->nationality_id), 403);

        return $worker;
    }
}
