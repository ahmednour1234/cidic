<?php

namespace Database\Seeders;

use App\Enums\AvailabilityStatus;
use App\Models\Candidate;
use App\Models\CandidateCategory;
use App\Models\Nationality;
use App\Services\ReferenceNumberService;
use App\Support\ArabicSlug;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoCandidateSeeder extends Seeder
{
    public function run(): void
    {
        $nationalities = Nationality::pluck('id', 'slug');
        $categories = CandidateCategory::pluck('id', 'slug');

        if ($nationalities->isEmpty() || $categories->isEmpty()) {
            $this->command?->warn('Skipping demo candidates: reference data missing.');

            return;
        }

        $references = app(ReferenceNumberService::class);

        $rows = [
            ['سارة', 'sri-lanka', 'nanny', 28, 4, 'مربية أطفال', 'good', 'good', AvailabilityStatus::Available, true],
            ['ماريا', 'philippines', 'housemaid', 32, 6, 'عاملة منزلية', 'basic', 'fluent', AvailabilityStatus::Available, true],
            ['أمينة', 'ethiopia', 'housemaid', 26, 3, 'عاملة منزلية', 'good', 'none', AvailabilityStatus::Available, true],
            ['غريس', 'kenya', 'caregiver', 30, 5, 'مقدمة رعاية', 'basic', 'fluent', AvailabilityStatus::Available, true],
            ['روزيتا', 'philippines', 'cook', 35, 8, 'طباخة', 'basic', 'good', AvailabilityStatus::Reserved, false],
            ['نادية', 'bangladesh', 'cleaner', 24, 2, 'عاملة نظافة', 'good', 'none', AvailabilityStatus::Available, false],
            ['ليندا', 'kenya', 'elderly-care', 38, 10, 'رعاية كبار السن', 'good', 'fluent', AvailabilityStatus::UnderProcess, false],
            ['جوزفين', 'philippines', 'nanny', 29, 5, 'مربية أطفال', 'basic', 'fluent', AvailabilityStatus::Available, false],
        ];

        $skills = [
            ['تنظيف المنزل', 'الطبخ', 'كي الملابس'],
            ['العناية بالأطفال', 'المساعدة في الواجبات', 'الطبخ'],
            ['رعاية كبار السن', 'المساعدة الطبية الأولية', 'التنظيف'],
        ];

        foreach ($rows as $i => [$name, $natSlug, $catSlug, $age, $experience, $profession, $arabic, $english, $status, $featured]) {
            $nationalityId = $nationalities[$natSlug] ?? $nationalities->first();
            $categoryId = $categories[$catSlug] ?? $categories->first();

            $candidate = Candidate::updateOrCreate(
                ['slug' => Str::slug("{$name}-{$natSlug}-{$i}")],
                [
                    // Placeholder; replaced with the id-derived reference below.
                    // Kept short: reference_number is a 32-char column, and a
                    // full UUID would overflow it on strict engines like MySQL.
                    'reference_number' => 'TMP-' . bin2hex(random_bytes(8)),
                    'name' => $name,
                    'nationality_id' => $nationalityId,
                    'candidate_category_id' => $categoryId,
                    'age' => $age,
                    'date_of_birth' => now()->subYears($age)->startOfYear(),
                    'gender' => 'female',
                    'religion' => 'مسلمة',
                    'marital_status' => $i % 2 === 0 ? 'single' : 'married',
                    'children_count' => $i % 2 === 0 ? 0 : 2,
                    'education' => 'ثانوي',
                    'profession' => $profession,
                    'years_of_experience' => $experience,
                    'salary' => 1200 + ($i * 50),
                    'contract_price' => 15000 + ($i * 500),
                    'language_arabic' => $arabic,
                    'language_english' => $english,
                    'skills' => $skills[$i % count($skills)],
                    'previous_countries' => $i % 2 === 0 ? ['السعودية', 'الإمارات'] : ['قطر'],
                    'description' => "عاملة مدربة بخبرة {$experience} سنوات في مجال {$profession}، تتميز بالأمانة والالتزام وحسن التعامل مع أفراد الأسرة.",
                    'availability_status' => $status,
                    'featured' => $featured,
                    'is_active' => true,
                    'sort_order' => $i,
                ],
            );

            // Derive the public reference from the persisted id (never count()+1).
            if (! str_starts_with((string) $candidate->reference_number, 'CV-')) {
                $reference = $references->assignCandidateReference($candidate);
                $candidate->forceFill([
                    'slug' => ArabicSlug::forCandidate($reference, $name),
                ])->saveQuietly();
            }
        }
    }
}
