<?php

namespace App\Support;

use Illuminate\Support\Str;

/**
 * Slug generation for Arabic names.
 *
 * Str::slug() drops Arabic characters entirely (it ASCII-folds first), which
 * would turn "سارة" into an empty string. We transliterate to Latin characters
 * so candidate URLs stay readable and SEO friendly, e.g. cv-00001-sarah.
 */
class ArabicSlug
{
    /** @var array<string, string> */
    protected const MAP = [
        'ا' => 'a', 'أ' => 'a', 'إ' => 'i', 'آ' => 'aa', 'ٱ' => 'a',
        'ب' => 'b', 'ت' => 't', 'ث' => 'th', 'ج' => 'j', 'ح' => 'h',
        'خ' => 'kh', 'د' => 'd', 'ذ' => 'dh', 'ر' => 'r', 'ز' => 'z',
        'س' => 's', 'ش' => 'sh', 'ص' => 's', 'ض' => 'd', 'ط' => 't',
        'ظ' => 'z', 'ع' => 'a', 'غ' => 'gh', 'ف' => 'f', 'ق' => 'q',
        'ك' => 'k', 'ل' => 'l', 'م' => 'm', 'ن' => 'n', 'ه' => 'h',
        'و' => 'w', 'ي' => 'y', 'ى' => 'a', 'ئ' => 'e', 'ء' => 'a',
        'ؤ' => 'o', 'ة' => 'h', 'ﻻ' => 'la', 'َ' => '', 'ً' => '',
        'ُ' => '', 'ٌ' => '', 'ِ' => '', 'ٍ' => '', 'ْ' => '', 'ّ' => '',
        '٠' => '0', '١' => '1', '٢' => '2', '٣' => '3', '٤' => '4',
        '٥' => '5', '٦' => '6', '٧' => '7', '٨' => '8', '٩' => '9',
    ];

    public static function make(string $value): string
    {
        $transliterated = strtr($value, self::MAP);
        $slug = Str::slug($transliterated);

        // Fall back to a stable token if the input yielded nothing sluggable.
        return $slug !== '' ? $slug : Str::lower(Str::random(8));
    }

    /**
     * Build a candidate slug such as "cv-00001-sarah", guaranteed unique by the
     * reference number prefix.
     */
    public static function forCandidate(string $reference, string $name): string
    {
        return self::make($reference . '-' . $name);
    }
}
