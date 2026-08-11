<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Derives a candidate display name from an uploaded CV filename.
 *
 * Bulk uploads carry no structured data, so the filename is the only signal for
 * the candidate's name. Real-world filenames are messy ("CV_sara_01.pdf",
 * "resume-maria (2).pdf"), so noise words, separators and counters are stripped.
 * The admin can always correct the result afterwards.
 */
class CvFilename
{
    /** Tokens that describe the document rather than the person. */
    protected const NOISE = [
        'cv', 'cvs', 'resume', 'resumes', 'curriculum', 'vitae',
        'copy', 'final', 'new', 'scan', 'scanned', 'doc', 'document',
        'file', 'profile', 'bio', 'data', 'form', 'sheet',
    ];

    public static function toName(string $filename): string
    {
        // Drop any directory component and the extension.
        $base = pathinfo(str_replace('\\', '/', $filename), PATHINFO_FILENAME);

        // Separators -> spaces.
        $name = preg_replace('/[_\-\.\+]+/u', ' ', $base) ?? $base;

        // Drop bracketed counters such as "(2)" or "[1]".
        $name = preg_replace('/[\(\[\{]\s*\d+\s*[\)\]\}]/u', ' ', $name) ?? $name;

        // Split into tokens, keeping Arabic and Latin letters and digits.
        $tokens = preg_split('/\s+/u', $name, -1, PREG_SPLIT_NO_EMPTY) ?: [];

        $kept = [];

        foreach ($tokens as $token) {
            $clean = trim($token);

            if ($clean === '') {
                continue;
            }

            // Drop noise words (case-insensitive, Latin only).
            if (in_array(Str::lower($clean), self::NOISE, true)) {
                continue;
            }

            // Drop pure numbers (ids, years, counters).
            if (preg_match('/^\d+$/u', $clean)) {
                continue;
            }

            // Drop long hex/uuid-looking fragments.
            if (preg_match('/^[0-9a-f]{8,}$/i', $clean)) {
                continue;
            }

            $kept[] = $clean;
        }

        // Nothing usable left: fall back to the raw basename so the row is still
        // identifiable, rather than silently creating an unnamed candidate.
        if ($kept === []) {
            $fallback = trim(preg_replace('/\s+/u', ' ', $name) ?? '');

            return $fallback !== '' ? Str::limit($fallback, 140, '') : 'سيرة ذاتية';
        }

        $result = implode(' ', $kept);

        // Title-case Latin words; Arabic is unaffected by ucwords.
        $result = preg_replace_callback(
            '/\b[a-z][a-z\']*/u',
            fn (array $m) => Str::ucfirst($m[0]),
            $result,
        ) ?? $result;

        return Str::limit(trim($result), 140, '');
    }
}
