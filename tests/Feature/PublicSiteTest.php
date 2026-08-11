<?php

namespace Tests\Feature;

use App\Enums\AvailabilityStatus;
use Database\Seeders\ReferenceDataSeeder;
use Database\Seeders\SiteSettingSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PublicSiteTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->seed(SiteSettingSeeder::class);
        $this->seed(ReferenceDataSeeder::class);
    }

    public function test_homepage_loads(): void
    {
        $this->createCandidate(['featured' => true]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee('حلول موثوقة', false);
        $response->assertSee('السير الذاتية المتاحة', false);
    }

    public function test_homepage_shows_only_active_records(): void
    {
        $visible = $this->createCandidate(['name' => 'ظاهرة']);
        $hidden = $this->createCandidate(['name' => 'مخفية', 'is_active' => false]);

        $response = $this->get('/');

        $response->assertOk();
        $response->assertSee($visible->reference_number);
        $response->assertDontSee($hidden->reference_number);
    }

    public function test_candidates_page_loads(): void
    {
        $candidate = $this->createCandidate();

        $response = $this->get(route('candidates.index'));

        $response->assertOk();
        $response->assertSee($candidate->reference_number);
    }

    public function test_candidate_profile_loads(): void
    {
        $candidate = $this->createCandidate();

        $response = $this->get(route('candidates.show', $candidate));

        $response->assertOk();
        $response->assertSee($candidate->name);
        $response->assertSee($candidate->reference_number);
        $response->assertSee('طلب هذه العاملة', false);
    }

    public function test_inactive_candidate_profile_returns_404(): void
    {
        $candidate = $this->createCandidate(['is_active' => false]);

        $this->get(route('candidates.show', $candidate))->assertNotFound();
    }

    public function test_unavailable_candidate_profile_hides_request_cta(): void
    {
        $candidate = $this->createCandidate([
            'availability_status' => AvailabilityStatus::Reserved,
        ]);

        $response = $this->get(route('candidates.show', $candidate));

        $response->assertOk();
        $response->assertSee('هذه العاملة غير متاحة حالياً.', false);
        $response->assertDontSee('طلب هذه العاملة', false);
    }

    public function test_candidate_filter_by_nationality(): void
    {
        $philippines = $this->createNationality(['name_ar' => 'الفلبين', 'slug' => 'ph-filter']);
        $kenya = $this->createNationality(['name_ar' => 'كينيا', 'slug' => 'ke-filter']);

        $match = $this->createCandidate(['nationality_id' => $philippines->id]);
        $other = $this->createCandidate(['nationality_id' => $kenya->id]);

        $response = $this->get(route('candidates.index', ['nationality' => 'ph-filter']));

        $response->assertOk();
        $response->assertSee($match->reference_number);
        $response->assertDontSee($other->reference_number);
    }

    public function test_candidate_filter_by_experience_and_search(): void
    {
        $senior = $this->createCandidate(['years_of_experience' => 9]);
        $junior = $this->createCandidate(['years_of_experience' => 1]);

        $response = $this->get(route('candidates.index', ['experience' => 5]));
        $response->assertOk();
        $response->assertSee($senior->reference_number);
        $response->assertDontSee($junior->reference_number);

        // Search by reference number.
        $response = $this->get(route('candidates.index', ['q' => $junior->reference_number]));
        $response->assertOk();
        $response->assertSee($junior->reference_number);
        $response->assertDontSee($senior->reference_number);
    }

    public function test_candidate_filters_are_preserved_in_pagination_links(): void
    {
        $nationality = $this->createNationality(['slug' => 'pagination-nat']);

        // More than one page worth (12 per page).
        for ($i = 0; $i < 14; $i++) {
            $this->createCandidate([
                'nationality_id' => $nationality->id,
                'name' => 'عاملة ' . $i,
            ]);
        }

        $response = $this->get(route('candidates.index', ['nationality' => 'pagination-nat']));

        $response->assertOk();
        $response->assertSee('nationality=pagination-nat', false);
    }

    public function test_empty_state_is_shown_when_no_results(): void
    {
        $response = $this->get(route('candidates.index', ['q' => 'لا-يوجد-مطابق']));

        $response->assertOk();
        $response->assertSee('لا توجد سير ذاتية مطابقة لخيارات البحث.', false);
    }

    public function test_static_pages_load(): void
    {
        foreach (['services.index', 'nationalities.index', 'about', 'faq', 'contact.create', 'privacy-policy', 'terms'] as $route) {
            $this->get(route($route))->assertOk();
        }
    }

    public function test_sitemap_is_xml(): void
    {
        $this->createCandidate();

        $response = $this->get('/sitemap.xml');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'application/xml');
        $response->assertSee('<urlset', false);
    }
}
