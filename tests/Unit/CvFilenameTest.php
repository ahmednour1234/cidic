<?php

namespace Tests\Unit;

use App\Support\CvFilename;
use PHPUnit\Framework\TestCase;

class CvFilenameTest extends TestCase
{
    /**
     * @dataProvider filenames
     */
    public function test_it_derives_a_display_name(string $filename, string $expected): void
    {
        $this->assertSame($expected, CvFilename::toName($filename));
    }

    public static function filenames(): array
    {
        return [
            'simple' => ['sara.pdf', 'Sara'],
            'cv prefix' => ['CV-Sara.pdf', 'Sara'],
            'underscores' => ['CV_sara_perera.pdf', 'Sara Perera'],
            'with country and year' => ['CV-Sara-Sri-Lanka-2024.pdf', 'Sara Sri Lanka'],
            'resume word' => ['resume maria santos.pdf', 'Maria Santos'],
            'duplicate counter' => ['maria (2).pdf', 'Maria'],
            'mixed noise' => ['final_CV_copy_grace_2023.pdf', 'Grace'],
            'dotted' => ['josephine.cruz.cv.pdf', 'Josephine Cruz'],
            'arabic' => ['سارة.pdf', 'سارة'],
            'arabic with prefix' => ['CV_أمينة_محمد.pdf', 'أمينة محمد'],
            'uppercase' => ['LINDA WANJIKU.pdf', 'LINDA WANJIKU'],
            'hex noise' => ['cv_a3f9c2e81b_nadia.pdf', 'Nadia'],
            'full path' => ['C:/uploads/CV-Rosita.pdf', 'Rosita'],
        ];
    }

    public function test_it_falls_back_when_nothing_usable_remains(): void
    {
        // Only noise and digits — must not produce an empty name.
        $this->assertNotSame('', CvFilename::toName('cv_2024.pdf'));
        $this->assertNotSame('', CvFilename::toName('12345.pdf'));
    }

    public function test_it_limits_absurdly_long_names(): void
    {
        $long = str_repeat('abcdefghij ', 40) . '.pdf';

        $this->assertLessThanOrEqual(140, mb_strlen(CvFilename::toName($long)));
    }
}
