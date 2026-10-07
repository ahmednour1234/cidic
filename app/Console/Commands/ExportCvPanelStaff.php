<?php

namespace App\Console\Commands;

use App\Enums\Department;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;
use ZipArchive;

/**
 * Writes the CV-panel staff list to a real .xlsx file.
 *
 * An xlsx is just a zip of XML parts, so this is built by hand rather than
 * pulling in a spreadsheet dependency for one table. Passwords are never
 * stored, so the column is only filled when the seeder passes them in via
 * --passwords; otherwise the sheet carries the account list alone.
 */
class ExportCvPanelStaff extends Command
{
    protected $signature = 'cv-panel:export-staff
                            {--path= : Where to write the file (default: storage/app/cv-panel-staff.xlsx)}
                            {--passwords= : email=password pairs, comma separated, for a fresh handover sheet}
                            {--all : Include the @example.com demo accounts too}';

    protected $description = 'Export CV panel staff accounts to an Excel file';

    public function handle(): int
    {
        $path = $this->option('path')
            ?: storage_path('app/cv-panel-staff.xlsx');

        $passwords = $this->parsePasswords((string) $this->option('passwords'));

        $rows = [['الاسم', 'البريد الإلكتروني', 'القسم', 'كلمة المرور', 'الحالة']];

        $staff = User::query()
            ->whereNotNull('department')
            // The seeded demo logins are local-only and have no place in a
            // handover sheet; --all brings them back for debugging.
            ->unless($this->option('all'), fn ($q) => $q->where('email', 'not like', '%@example.com'))
            ->orderByRaw("CASE department
                WHEN ? THEN 1 WHEN ? THEN 2 WHEN ? THEN 3 ELSE 4 END", [
                Department::Coordination->value,
                Department::CustomerService->value,
                Department::BranchManager->value,
            ])
            ->orderBy('name')
            ->get();

        foreach ($staff as $user) {
            $rows[] = [
                $user->name,
                $user->email,
                Department::tryFrom((string) $user->department)?->label() ?? '—',
                $passwords[mb_strtolower($user->email)] ?? '—',
                $user->is_active ? 'مفعّل' : 'موقوف',
            ];
        }

        File::ensureDirectoryExists(dirname($path));
        $this->write($path, $rows);

        $this->info('Written: '.$path);
        $this->line(($staff->count()).' account(s).');

        if ($passwords === []) {
            $this->warn('No passwords included - they are hashed and cannot be read back. '
                .'Pass --passwords="email=secret,email2=secret2" to produce a handover sheet.');
        }

        return self::SUCCESS;
    }

    /** @return array<string, string> */
    private function parsePasswords(string $raw): array
    {
        $out = [];

        foreach (array_filter(explode(',', $raw)) as $pair) {
            [$email, $password] = array_pad(explode('=', trim($pair), 2), 2, null);

            if ($email && $password) {
                $out[mb_strtolower(trim($email))] = trim($password);
            }
        }

        return $out;
    }

    /** @param list<list<string>> $rows */
    private function write(string $path, array $rows): void
    {
        @unlink($path);

        $zip = new ZipArchive();
        $zip->open($path, ZipArchive::CREATE);

        $zip->addFromString('[Content_Types].xml', <<<'XML'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <Types xmlns="http://schemas.openxmlformats.org/package/2006/content-types">
              <Default Extension="rels" ContentType="application/vnd.openxmlformats-package.relationships+xml"/>
              <Default Extension="xml" ContentType="application/xml"/>
              <Override PartName="/xl/workbook.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.sheet.main+xml"/>
              <Override PartName="/xl/worksheets/sheet1.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.worksheet+xml"/>
              <Override PartName="/xl/styles.xml" ContentType="application/vnd.openxmlformats-officedocument.spreadsheetml.styles+xml"/>
            </Types>
            XML);

        $zip->addFromString('_rels/.rels', <<<'XML'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
              <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/officeDocument" Target="xl/workbook.xml"/>
            </Relationships>
            XML);

        // rightToLeft so the sheet opens in Arabic reading order.
        $zip->addFromString('xl/workbook.xml', <<<'XML'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <workbook xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main"
                      xmlns:r="http://schemas.openxmlformats.org/officeDocument/2006/relationships">
              <sheets><sheet name="المستخدمون" sheetId="1" r:id="rId1"/></sheets>
            </workbook>
            XML);

        $zip->addFromString('xl/_rels/workbook.xml.rels', <<<'XML'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <Relationships xmlns="http://schemas.openxmlformats.org/package/2006/relationships">
              <Relationship Id="rId1" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/worksheet" Target="worksheets/sheet1.xml"/>
              <Relationship Id="rId2" Type="http://schemas.openxmlformats.org/officeDocument/2006/relationships/styles" Target="styles.xml"/>
            </Relationships>
            XML);

        // Two formats: a bold header on a brand-blue fill, and a plain body.
        $zip->addFromString('xl/styles.xml', <<<'XML'
            <?xml version="1.0" encoding="UTF-8" standalone="yes"?>
            <styleSheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">
              <fonts count="2">
                <font><sz val="11"/><name val="Calibri"/></font>
                <font><b/><sz val="11"/><color rgb="FFFFFFFF"/><name val="Calibri"/></font>
              </fonts>
              <fills count="3">
                <fill><patternFill patternType="none"/></fill>
                <fill><patternFill patternType="gray125"/></fill>
                <fill><patternFill patternType="solid"><fgColor rgb="FF0060A8"/><bgColor indexed="64"/></patternFill></fill>
              </fills>
              <borders count="1"><border><left/><right/><top/><bottom/><diagonal/></border></borders>
              <cellStyleXfs count="1"><xf numFmtId="0" fontId="0" fillId="0" borderId="0"/></cellStyleXfs>
              <cellXfs count="2">
                <xf numFmtId="0" fontId="0" fillId="0" borderId="0" xfId="0"/>
                <xf numFmtId="0" fontId="1" fillId="2" borderId="0" xfId="0" applyFont="1" applyFill="1"/>
              </cellXfs>
            </styleSheet>
            XML);

        $zip->addFromString('xl/worksheets/sheet1.xml', $this->sheet($rows));
        $zip->close();
    }

    /** @param list<list<string>> $rows */
    private function sheet(array $rows): string
    {
        $xml = '<?xml version="1.0" encoding="UTF-8" standalone="yes"?>'
            .'<worksheet xmlns="http://schemas.openxmlformats.org/spreadsheetml/2006/main">'
            .'<sheetViews><sheetView rightToLeft="1" workbookViewId="0"/></sheetViews>'
            .'<cols>'
            .'<col min="1" max="1" width="18" customWidth="1"/>'
            .'<col min="2" max="2" width="30" customWidth="1"/>'
            .'<col min="3" max="3" width="18" customWidth="1"/>'
            .'<col min="4" max="4" width="24" customWidth="1"/>'
            .'<col min="5" max="5" width="12" customWidth="1"/>'
            .'</cols><sheetData>';

        foreach ($rows as $r => $row) {
            $style = $r === 0 ? ' s="1"' : '';
            $xml .= '<row r="'.($r + 1).'">';

            foreach ($row as $c => $value) {
                $ref = $this->column($c).($r + 1);

                // Inline strings keep every value as text, so a password made
                // only of digits is never reformatted as a number.
                $xml .= '<c r="'.$ref.'" t="inlineStr"'.$style.'>'
                    .'<is><t xml:space="preserve">'
                    .htmlspecialchars((string) $value, ENT_XML1 | ENT_QUOTES, 'UTF-8')
                    .'</t></is></c>';
            }

            $xml .= '</row>';
        }

        return $xml.'</sheetData></worksheet>';
    }

    private function column(int $index): string
    {
        $name = '';

        for ($i = $index; $i >= 0; $i = intdiv($i, 26) - 1) {
            $name = chr(65 + $i % 26).$name;
        }

        return $name;
    }
}
