<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use App\Enums\RequestStatus;
use App\Models\CandidateRequest;
use App\Models\RecruitmentRequest;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CandidateRequestTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
    }

    /** @return array<string, string> */
    protected function validPayload(array $overrides = []): array
    {
        return array_merge([
            'customer_name' => 'أحمد العتيبي',
            'mobile' => '0512345678',
            'whatsapp' => '0512345678',
            'email' => 'ahmed@example.com',
            'city' => 'الرياض',
            'notes' => 'أرغب بالتواصل في الفترة المسائية.',
        ], $overrides);
    }

    public function test_available_candidate_can_receive_request(): void
    {
        $candidate = $this->createCandidate();

        $response = $this->post(route('candidate-requests.store', $candidate), $this->validPayload());

        $request = CandidateRequest::first();

        $this->assertNotNull($request);
        $response->assertRedirect(route('requests.success', $request->request_number));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('candidate_requests', [
            'candidate_id' => $candidate->id,
            'customer_name' => 'أحمد العتيبي',
            'mobile' => '0512345678',
            'status' => RequestStatus::New->value,
        ]);
    }

    public function test_request_number_is_generated_in_expected_format(): void
    {
        $candidate = $this->createCandidate();

        $this->post(route('candidate-requests.store', $candidate), $this->validPayload());

        $request = CandidateRequest::first();

        $this->assertMatchesRegularExpression('/^REQ-\d{4}-\d{6}$/', $request->request_number);
    }

    public function test_request_numbers_are_unique_across_many_submissions(): void
    {
        $numbers = [];

        for ($i = 0; $i < 5; $i++) {
            $candidate = $this->createCandidate();

            // Distinct mobiles avoid the duplicate-submission guard.
            $this->post(route('candidate-requests.store', $candidate), $this->validPayload([
                'mobile' => '05123456' . str_pad((string) $i, 2, '0', STR_PAD_LEFT),
            ]));
        }

        $numbers = CandidateRequest::pluck('request_number')->all();

        $this->assertCount(5, $numbers);
        $this->assertCount(5, array_unique($numbers));
    }

    public function test_status_history_is_recorded_on_creation(): void
    {
        $candidate = $this->createCandidate();

        $this->post(route('candidate-requests.store', $candidate), $this->validPayload());

        $request = CandidateRequest::first();

        $this->assertDatabaseHas('candidate_request_status_histories', [
            'candidate_request_id' => $request->id,
            'new_status' => RequestStatus::New->value,
        ]);
    }

    public function test_unavailable_candidate_cannot_receive_request(): void
    {
        $candidate = $this->createCandidate([
            'availability_status' => AvailabilityStatus::Hired,
        ]);

        $response = $this->post(route('candidate-requests.store', $candidate), $this->validPayload());

        $response->assertRedirect(route('candidates.show', $candidate));
        $response->assertSessionHas('error');
        $this->assertDatabaseCount('candidate_requests', 0);
    }

    public function test_request_form_for_unavailable_candidate_redirects(): void
    {
        $candidate = $this->createCandidate([
            'availability_status' => AvailabilityStatus::Reserved,
        ]);

        $this->get(route('candidate-requests.create', $candidate))
            ->assertRedirect(route('candidates.show', $candidate))
            ->assertSessionHas('error');
    }

    public function test_request_validation_rejects_missing_and_invalid_fields(): void
    {
        $candidate = $this->createCandidate();

        $this->post(route('candidate-requests.store', $candidate), [])
            ->assertSessionHasErrors(['customer_name', 'mobile']);

        $this->post(route('candidate-requests.store', $candidate), $this->validPayload([
            'mobile' => '12345',
        ]))->assertSessionHasErrors('mobile');

        $this->post(route('candidate-requests.store', $candidate), $this->validPayload([
            'email' => 'not-an-email',
        ]))->assertSessionHasErrors('email');

        $this->assertDatabaseCount('candidate_requests', 0);
    }

    public function test_duplicate_submissions_within_window_do_not_create_second_request(): void
    {
        $candidate = $this->createCandidate();
        $payload = $this->validPayload();

        $this->post(route('candidate-requests.store', $candidate), $payload);
        $this->post(route('candidate-requests.store', $candidate), $payload);

        $this->assertDatabaseCount('candidate_requests', 1);
    }

    public function test_success_page_shows_request_number(): void
    {
        $candidate = $this->createCandidate();

        $this->post(route('candidate-requests.store', $candidate), $this->validPayload());
        $request = CandidateRequest::first();

        $this->get(route('requests.success', $request->request_number))
            ->assertOk()
            ->assertSee($request->request_number)
            ->assertSee('تم استلام طلبك بنجاح', false);
    }

    public function test_general_recruitment_request_can_be_submitted(): void
    {
        $response = $this->post(route('recruitment-requests.store'), [
            'name' => 'نورة السالم',
            'mobile' => '0509876543',
            'city' => 'جدة',
        ]);

        $request = RecruitmentRequest::first();

        $this->assertNotNull($request);
        $response->assertRedirect(route('requests.success', $request->request_number));
        $this->assertMatchesRegularExpression('/^REQ-\d{4}-\d{6}$/', $request->request_number);
    }

    public function test_general_request_validation(): void
    {
        $this->post(route('recruitment-requests.store'), [])
            ->assertSessionHasErrors(['name', 'mobile']);

        $this->assertDatabaseCount('recruitment_requests', 0);
    }

    public function test_contact_form_stores_message(): void
    {
        $response = $this->post(route('contact.store'), [
            'name' => 'خالد الدوسري',
            'mobile' => '0501112222',
            'email' => 'khaled@example.com',
            'subject' => 'استفسار',
            'message' => 'أرغب بالاستفسار عن خدمات الاستقدام والأسعار.',
        ]);

        $response->assertRedirect(route('contact.create'));
        $response->assertSessionHas('success');

        $this->assertDatabaseHas('contact_messages', [
            'name' => 'خالد الدوسري',
            'mobile' => '0501112222',
            'status' => 'new',
        ]);
    }

    public function test_contact_form_validation(): void
    {
        $this->post(route('contact.store'), [])
            ->assertSessionHasErrors(['name', 'mobile', 'message']);

        $this->assertDatabaseCount('contact_messages', 0);
    }
}
