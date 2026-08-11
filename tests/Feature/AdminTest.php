<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\RequestStatus;
use App\Enums\UserRole;
use App\Models\Candidate;
use App\Models\CandidateRequest;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
    }

    /* ------------------------------------------------------------------
     | Access control
     * --------------------------------------------------------------- */

    public function test_guest_cannot_access_admin_dashboard(): void
    {
        $this->get(route('admin.dashboard'))->assertRedirect(route('admin.login'));
    }

    public function test_guest_cannot_access_admin_candidate_pages(): void
    {
        $this->get(route('admin.candidates.index'))->assertRedirect(route('admin.login'));
        $this->get(route('admin.candidates.create'))->assertRedirect(route('admin.login'));
    }

    public function test_admin_can_log_in(): void
    {
        $admin = $this->createAdmin();

        $response = $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ]);

        $response->assertRedirect(route('admin.dashboard'));
        $this->assertAuthenticatedAs($admin);
    }

    public function test_login_fails_with_bad_credentials(): void
    {
        $admin = $this->createAdmin();

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'wrong-password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_deactivated_user_cannot_log_in(): void
    {
        $admin = $this->createAdmin();
        $admin->update(['is_active' => false]);

        $this->post(route('admin.login.store'), [
            'email' => $admin->email,
            'password' => 'password',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_admin_can_log_out(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->post(route('admin.logout'))
            ->assertRedirect(route('admin.login'));

        $this->assertGuest();
    }

    public function test_staff_cannot_access_settings_or_users(): void
    {
        $staff = $this->createAdmin(UserRole::Staff);

        $this->actingAs($staff)->get(route('admin.settings.edit'))->assertForbidden();
        $this->actingAs($staff)->get(route('admin.users.index'))->assertForbidden();
    }

    public function test_staff_can_access_candidates_and_requests(): void
    {
        $staff = $this->createAdmin(UserRole::Staff);

        $this->actingAs($staff)->get(route('admin.candidates.index'))->assertOk();
        $this->actingAs($staff)->get(route('admin.candidate-requests.index'))->assertOk();
    }

    /* ------------------------------------------------------------------
     | Dashboard
     * --------------------------------------------------------------- */

    public function test_dashboard_loads_with_statistics(): void
    {
        $this->createCandidate();

        $this->actingAs($this->createAdmin())
            ->get(route('admin.dashboard'))
            ->assertOk()
            ->assertSee('إجمالي السير الذاتية', false);
    }

    /* ------------------------------------------------------------------
     | Candidate management
     * --------------------------------------------------------------- */

    public function test_admin_can_create_candidate(): void
    {
        Storage::fake('public');

        $admin = $this->createAdmin();
        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $response = $this->actingAs($admin)->post(route('admin.candidates.store'), [
            'name' => 'مريم',
            'nationality_id' => $nationality->id,
            'candidate_category_id' => $category->id,
            'gender' => 'female',
            'age' => 30,
            'profession' => 'عاملة منزلية',
            'years_of_experience' => 5,
            'language_arabic' => 'good',
            'language_english' => 'none',
            'availability_status' => AvailabilityStatus::Available->value,
            'skills' => 'الطبخ، التنظيف',
            'is_active' => '1',
            'profile_image' => UploadedFile::fake()->image('photo.jpg'),
            'cv_file' => UploadedFile::fake()->create('cv.pdf', 100, 'application/pdf'),
        ]);

        $response->assertRedirect(route('admin.candidates.index'));

        $candidate = Candidate::where('name', 'مريم')->first();

        $this->assertNotNull($candidate);
        $this->assertMatchesRegularExpression('/^CV-\d{5}$/', $candidate->reference_number);
        $this->assertSame(['الطبخ', 'التنظيف'], $candidate->skills);

        // Uploaded files are stored with generated names, not the client names.
        $this->assertNotNull($candidate->profile_image);
        $this->assertStringNotContainsString('photo.jpg', $candidate->profile_image);
        Storage::disk('public')->assertExists($candidate->profile_image);
        Storage::disk('public')->assertExists($candidate->cv_file);
    }

    public function test_candidate_creation_validates_required_fields(): void
    {
        $this->actingAs($this->createAdmin())
            ->post(route('admin.candidates.store'), [])
            ->assertSessionHasErrors(['name', 'nationality_id', 'candidate_category_id', 'profession']);
    }

    public function test_candidate_upload_rejects_disallowed_file_type(): void
    {
        Storage::fake('public');

        $nationality = $this->createNationality();
        $category = $this->createCategory();

        $this->actingAs($this->createAdmin())
            ->post(route('admin.candidates.store'), [
                'name' => 'اختبار',
                'nationality_id' => $nationality->id,
                'candidate_category_id' => $category->id,
                'gender' => 'female',
                'profession' => 'عاملة منزلية',
                'years_of_experience' => 1,
                'language_arabic' => 'good',
                'language_english' => 'none',
                'availability_status' => AvailabilityStatus::Available->value,
                // An executable disguised as a CV must be rejected.
                'cv_file' => UploadedFile::fake()->create('payload.php', 10, 'application/x-php'),
            ])
            ->assertSessionHasErrors('cv_file');
    }

    public function test_admin_can_update_candidate(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate();

        $response = $this->actingAs($admin)->put(route('admin.candidates.update', $candidate), [
            'name' => 'اسم محدث',
            'nationality_id' => $candidate->nationality_id,
            'candidate_category_id' => $candidate->candidate_category_id,
            'gender' => 'female',
            'profession' => 'طباخة',
            'years_of_experience' => 7,
            'language_arabic' => 'fluent',
            'language_english' => 'good',
            'availability_status' => AvailabilityStatus::Available->value,
            'is_active' => '1',
        ]);

        $response->assertRedirect(route('admin.candidates.index'));

        $candidate->refresh();
        $this->assertSame('اسم محدث', $candidate->name);
        $this->assertSame(7, $candidate->years_of_experience);
        // The reference number must never change on update.
        $this->assertMatchesRegularExpression('/^CV-\d{5}$/', $candidate->reference_number);
    }

    public function test_admin_can_change_candidate_availability(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate();

        $this->actingAs($admin)
            ->patch(route('admin.candidates.availability', $candidate), [
                'availability_status' => AvailabilityStatus::Reserved->value,
            ])
            ->assertRedirect();

        $this->assertSame(AvailabilityStatus::Reserved, $candidate->refresh()->availability_status);
    }

    public function test_admin_can_toggle_featured_and_active(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate(['featured' => false]);

        $this->actingAs($admin)->patch(route('admin.candidates.toggle-featured', $candidate));
        $this->assertTrue($candidate->refresh()->featured);

        $this->actingAs($admin)->patch(route('admin.candidates.toggle-active', $candidate));
        $this->assertFalse($candidate->refresh()->is_active);
    }

    public function test_admin_can_soft_delete_candidate(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate();

        $this->actingAs($admin)
            ->delete(route('admin.candidates.destroy', $candidate))
            ->assertRedirect(route('admin.candidates.index'));

        $this->assertSoftDeleted('candidates', ['id' => $candidate->id]);
    }

    public function test_admin_candidate_search_finds_by_reference(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate();
        $other = $this->createCandidate(['name' => 'أخرى']);

        $this->actingAs($admin)
            ->get(route('admin.candidates.index', ['q' => $candidate->reference_number]))
            ->assertOk()
            ->assertSee($candidate->reference_number)
            ->assertDontSee($other->reference_number);
    }

    /* ------------------------------------------------------------------
     | Request management
     * --------------------------------------------------------------- */

    public function test_admin_can_update_request_status_and_history_is_recorded(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate();

        $this->post(route('candidate-requests.store', $candidate), [
            'customer_name' => 'عميل',
            'mobile' => '0512345678',
        ]);

        $request = CandidateRequest::first();

        $this->actingAs($admin)
            ->patch(route('admin.candidate-requests.status', $request), [
                'status' => RequestStatus::Contacted->value,
                'notes' => 'تم الاتصال بالعميل.',
            ])
            ->assertRedirect();

        $this->assertSame(RequestStatus::Contacted, $request->refresh()->status);

        $this->assertDatabaseHas('candidate_request_status_histories', [
            'candidate_request_id' => $request->id,
            'old_status' => RequestStatus::New->value,
            'new_status' => RequestStatus::Contacted->value,
            'changed_by' => $admin->id,
        ]);
    }

    public function test_admin_can_view_request_details(): void
    {
        $admin = $this->createAdmin();
        $candidate = $this->createCandidate();

        $this->post(route('candidate-requests.store', $candidate), [
            'customer_name' => 'عميل التفاصيل',
            'mobile' => '0512345678',
        ]);

        $request = CandidateRequest::first();

        $this->actingAs($admin)
            ->get(route('admin.candidate-requests.show', $request))
            ->assertOk()
            ->assertSee($request->request_number)
            ->assertSee('عميل التفاصيل');
    }

    /* ------------------------------------------------------------------
     | Content management
     * --------------------------------------------------------------- */

    public function test_admin_can_create_and_delete_faq(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.faqs.store'), [
            'question' => 'سؤال اختباري؟',
            'answer' => 'إجابة اختبارية.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseHas('faqs', ['question' => 'سؤال اختباري؟']);

        $faq = \App\Models\Faq::where('question', 'سؤال اختباري؟')->first();

        $this->actingAs($admin)
            ->delete(route('admin.faqs.destroy', $faq->id))
            ->assertRedirect(route('admin.faqs.index'));

        $this->assertDatabaseMissing('faqs', ['id' => $faq->id]);
    }

    public function test_admin_can_edit_how_it_works_step(): void
    {
        $admin = $this->createAdmin();
        $step = \App\Models\HowItWorks::first();

        $this->actingAs($admin)->get(route('admin.how-it-works.edit', $step->id))->assertOk();

        $this->actingAs($admin)->put(route('admin.how-it-works.update', $step->id), [
            'title' => 'خطوة محدثة',
            'description' => 'وصف محدث.',
            'is_active' => '1',
        ])->assertRedirect(route('admin.how-it-works.index'));

        $this->assertSame('خطوة محدثة', $step->refresh()->title);
    }

    public function test_admin_can_update_settings_and_cache_is_refreshed(): void
    {
        $admin = $this->createAdmin();

        // Brand spelling as it appears in the logo artwork.
        $this->assertSame('سدك للإستقدام', setting('company_name_ar'));

        $this->actingAs($admin)->put(route('admin.settings.update'), [
            'settings' => [
                'company_name_ar' => 'اسم جديد',
                'phone' => '0119998888',
            ],
        ])->assertRedirect();

        $this->assertDatabaseHas('site_settings', [
            'key' => 'company_name_ar',
            'value' => 'اسم جديد',
        ]);

        // The helper must not serve a stale cached value.
        app()->forgetInstance(\App\Services\SettingService::class);
        $this->assertSame('اسم جديد', setting('company_name_ar'));
    }

    public function test_admin_can_create_user(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)->post(route('admin.users.store'), [
            'name' => 'موظف جديد',
            'email' => 'new-staff@example.com',
            'password' => 'secret-password',
            'password_confirmation' => 'secret-password',
            'role' => UserRole::Staff->value,
            'is_active' => '1',
        ])->assertRedirect(route('admin.users.index'));

        $this->assertDatabaseHas('users', [
            'email' => 'new-staff@example.com',
            'role' => UserRole::Staff->value,
        ]);
    }

    public function test_admin_cannot_delete_own_account(): void
    {
        $admin = $this->createAdmin();

        $this->actingAs($admin)
            ->delete(route('admin.users.destroy', $admin))
            ->assertSessionHas('error');

        $this->assertDatabaseHas('users', ['id' => $admin->id, 'deleted_at' => null]);
    }
}
