<?php

namespace App\Support\CvPanel;

use App\Models\Worker;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\StreamedResponse;

/**
 * Streams a worker's CV inline.
 *
 * The response is always named cv-{id}.pdf: the stored file name is an upload
 * artefact and is never exposed.
 */
final class CvStreamer
{
    public static function inline(Worker $worker, Request $request, string $cacheControl): Response
    {
        $disk = Storage::disk($worker->cvDisk());
        $filename = 'cv-'.$worker->id.'.pdf';

        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.$filename.'"',
            'Cache-Control' => $cacheControl,
            // Range support lets viewers fetch a single page at a time.
            'Accept-Ranges' => 'bytes',
            'X-Content-Type-Options' => 'nosniff',
        ];

        $lastModified = $disk->lastModified($worker->cv_path);
        $size = $disk->size($worker->cv_path);

        // Weak-ish validator built from the facts we have without reading the
        // file: path, size and mtime together change whenever the bytes do.
        $etag = '"'.md5($worker->cv_path.'|'.$size.'|'.$lastModified).'"';

        $headers['ETag'] = $etag;
        $headers['Last-Modified'] = gmdate('D, d M Y H:i:s', $lastModified).' GMT';

        if (self::isFresh($request, $etag, $lastModified)) {
            return response('', 304, $headers);
        }

        // A local disk can serve the file directly, which gives Range handling
        // and sendfile for free.
        $local = self::localPath($worker);

        if ($local !== null) {
            $response = new BinaryFileResponse($local, 200, $headers);
            $response->setContentDisposition('inline', $filename);
            $response->prepare($request);

            return $response;
        }

        return new StreamedResponse(function () use ($disk, $worker) {
            $stream = $disk->readStream($worker->cv_path);

            if ($stream === null) {
                return;
            }

            fpassthru($stream);
            fclose($stream);
        }, 200, $headers + ['Content-Length' => (string) $size]);
    }

    private static function isFresh(Request $request, string $etag, int $lastModified): bool
    {
        $ifNoneMatch = $request->headers->get('If-None-Match');

        if ($ifNoneMatch !== null && trim($ifNoneMatch) === $etag) {
            return true;
        }

        $ifModifiedSince = $request->headers->get('If-Modified-Since');

        return $ifModifiedSince !== null
            && ($since = strtotime($ifModifiedSince)) !== false
            && $lastModified <= $since;
    }

    private static function localPath(Worker $worker): ?string
    {
        $disk = Storage::disk($worker->cvDisk());

        try {
            $path = $disk->path($worker->cv_path);
        } catch (\Throwable) {
            // Remote disks have no local path.
            return null;
        }

        return is_file($path) ? $path : null;
    }
}
