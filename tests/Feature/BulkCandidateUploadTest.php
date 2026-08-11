<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class BulkCandidateUploadTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Storage::fake('public');
    }

    /** A fake PDF whose header passes the mimetypes check. */
    protected function pdf(string $name): UploadedFile
    {
        return UploadedFile::fake()->create($name, 120, 'application/pdf');
    }

    public function test_guest_cannot_access_bulk_upload(): void
    {
        $this->get(route('admin.candidates.bulk'))->assertRedirect(route('admin.login'));
    }

    public function test_bulk_upload_page_loads(): void
    {
        $this->actingAs($this->createAdmin())
            ->get(route('admin.candidates.bulk'))
            ->assertOk()
            ->assertSee('رفع سير ذاتية متعددة', false)
            ->assertSee('cv_files', false);
    }

    public function test_admin_can_upload_multiple_cvs_at_once(): void
    {
        $admin = $this->createAdmin();
        $nationality = $this->createNationality(['name_ar' => 'سريلانكا']);
        $category = $this->createCategory(['name_ar' => 'مربية أطفال']);

        $response = $this->actingAs($admin)->post(route('admin.candidates.bulk.store'), [
            'cv_files' => [
                $this->pdf('CV-Sara.pdf'),
                $this->pdf('resume_maria_santos.pdf'),
                $this->pdf('grace (2).pdf'),
            ],
            'nationality_id' => $nationality->id,
            'candidate_category_id' => $category->id,
            'availability_status' => AvailabilityStatus::Available->value,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.candidates.index'));
        $response->assertSessionHas('success');

        $this->assertSame(3, Candidate::count());

        // Names come from the filenames.
        $this->assertDatabaseHas('candidates', ['name' => 'Sara']);
        $this->assertDatabaseHas('candidates', ['name' => 'Maria Santos']);
        $this->assertDatabaseHas('candidates', ['name' => 'Grace']);

        // Shared fields applied to every row.
        foreach (Candidate::all() as $candidate) {
            $this->assertSame($nationality->id, $candidate->nationality_id);
            $this->assertSame($category->id, $candidate->candidate_category_id);
            $this->assertSame('مربية أطفال', $candidate->profession);
            $this->assertTrue($candidate->is_active);
            $this->assertNotNull($candidate->cv_file);
            Storage::disk('public')->assertExists($candidate->cv_file);
        }
    }

    public function test_each_candidate_gets_a_unique_reference_and_slug(): void
    {
        $admin = $this->createAdmin();
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($admin)->post(route('admin.candidates.bulk.store'), [
            'cv_files' => [
                $this->pdf('sara.pdf'),
                // Same derived name twice: slugs must still be unique.
                $this->pdf('CV_sara.pdf'),
                $this->pdf('sara-final.pdf'),
            ],
            'nationality_id' => $nationality->id,
            'candidate_category_id' => $category->id,
        ]);

        $references = Candidate::pluck('reference_number')->all();
        $slugs = Candidate::pluck('slug')->all();

        $this->assertCount(3, $references);
        $this->assertCount(3, array_unique($references));
        $this->assertCount(3, array_unique($slugs));

        foreach ($references as $reference) {
            $this->assertMatchesRegularExpression('/^CV-\d{5}$/', $reference);
        }
    }

    public function test_cv_file_is_required(): void
    {
        $admin = $this->createAdmin();
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($admin)
            ->post(route('admin.candidates.bulk.store'), [
                'nationality_id' => $nationality->id,
                'candidate_category_id' => $category->id,
            ])
            ->assertSessionHasErrors('cv_files');

        $this->assertSame(0, Candidate::count());
    }

    public function test_nationality_and_category_are_required(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.candidates.bulk.store'), [
                'cv_files' => [$this->pdf('sara.pdf')],
            ])
            ->assertSessionHasErrors(['nationality_id', 'candidate_category_id']);

        $this->assertSame(0, Candidate::count());
    }

    public function test_non_pdf_files_are_rejected(): void
    {
        $admin = $this->createAdmin();
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($admin)
            ->post(route('admin.candidates.bulk.store'), [
                'cv_files' => [
                    $this->pdf('sara.pdf'),
                    UploadedFile::fake()->create('payload.php', 10, 'application/x-php'),
                ],
                'nationality_id' => $nationality->id,
                'candidate_category_id' => $category->id,
            ])
            ->assertSessionHasErrors('cv_files.1');

        // Nothing is created when the batch fails validation.
        $this->assertSame(0, Candidate::count());
    }

    public function test_uploaded_files_are_stored_with_generated_names(): void
    {
        $admin = $this->createAdmin();
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($admin)->post(route('admin.candidates.bulk.store'), [
            'cv_files' => [$this->pdf('CV-Sara.pdf')],
            'nationality_id' => $nationality->id,
            'candidate_category_id' => $category->id,
        ]);

        $candidate = Candidate::firstOrFail();

        // The client filename must never become the stored path.
        $this->assertStringNotContainsString('CV-Sara', $candidate->cv_file);
        $this->assertStringStartsWith('candidates/cvs/', $candidate->cv_file);
        $this->assertStringEndsWith('.pdf', $candidate->cv_file);
    }

    public function test_staff_role_can_bulk_upload(): void
    {
        $staff = $this->createAdmin(UserRole::Staff);
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($staff)
            ->post(route('admin.candidates.bulk.store'), [
                'cv_files' => [$this->pdf('sara.pdf')],
                'nationality_id' => $nationality->id,
                'candidate_category_id' => $category->id,
            ])
            ->assertRedirect(route('admin.candidates.index'));

        $this->assertSame(1, Candidate::count());
    }

    public function test_bulk_uploaded_candidates_appear_on_the_public_site(): void
    {
        $admin = $this->createAdmin();
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($admin)->post(route('admin.candidates.bulk.store'), [
            'cv_files' => [$this->pdf('CV-Sara.pdf')],
            'nationality_id' => $nationality->id,
            'candidate_category_id' => $category->id,
            'availability_status' => AvailabilityStatus::Available->value,
            'is_active' => '1',
        ]);

        $candidate = Candidate::firstOrFail();

        $this->get(route('candidates.index'))
            ->assertOk()
            ->assertSee($candidate->reference_number);

        $this->get(route('candidates.show', $candidate))
            ->assertOk()
            ->assertSee('Sara');
    }
}
