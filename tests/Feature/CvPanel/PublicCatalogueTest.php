<?php

namespace Tests\Feature\CvPanel;

use App\Models\Worker;
use App\Services\CvPanel\CvReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PublicCatalogueTest extends CvPanelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('cv_private');
    }

    /** A worker whose CV file actually exists on the fake disk. */
    private function workerWithFile(array $attributes = []): Worker
    {
        $path = UploadedFile::fake()
            ->create('cv.pdf', 10, 'application/pdf')
            ->store('cvs', 'cv_private');

        return $this->worker(array_merge([
            'cv_path' => $path,
            'cv_disk' => 'cv_private',
        ], $attributes));
    }

    public function test_the_list_shows_an_available_cv(): void
    {
        $worker = $this->workerWithFile(['name' => 'عاملة متاحة']);

        // The card is the CV itself: it carries the id and a link to the PDF,
        // not the worker's name.
        $this->get(route('cvs.index'))
            ->assertOk()
            ->assertSee('#'.$worker->id)
            ->assertSee(route('cvs.pdf', $worker->id));
    }

    public function test_the_list_hides_a_reserved_cv(): void
    {
        $worker = $this->workerWithFile(['name' => 'عاملة محجوزة']);
        app(CvReservationService::class)->reserve($worker, $this->client(), $this->agent());

        $this->get(route('cvs.index'))
            ->assertOk()
            ->assertDontSee('عاملة محجوزة');
    }

    public function test_the_list_hides_a_withdrawn_but_available_cv(): void
    {
        $worker = $this->workerWithFile(['name' => 'عاملة مسحوبة']);
        $service = app(CvReservationService::class);

        $service->reserve($worker, $this->client(), $this->agent());
        $service->cancel($worker->fresh(), $this->superAdmin());

        // Available again, but the withdrawal is permanent.
        $this->assertSame(Worker::STATUS_AVAILABLE, $worker->fresh()->status);

        $this->get(route('cvs.index'))
            ->assertOk()
            ->assertDontSee('عاملة مسحوبة');
    }

    public function test_the_nationality_page_resolves_by_iso_code(): void
    {
        $nationality = $this->nationality('ethiopia', 'et');
        $worker = $this->workerWithFile(['nationality_id' => $nationality->id]);

        $this->get('/nationality/et')
            ->assertOk()
            ->assertSee($nationality->display_name)
            ->assertSee(route('cvs.pdf', $worker->id));
    }

    public function test_the_show_page_renders_labels_not_raw_keys(): void
    {
        $worker = $this->workerWithFile(['experience' => '3-5', 'religion' => 'christian']);

        $this->get(route('cvs.show', $worker->id))
            ->assertOk()
            ->assertSee(__('workers.experience.3-5'))
            ->assertSee(__('workers.religion.christian'))
            ->assertDontSee('workers.experience.');
    }

    public function test_the_show_page_404s_for_a_reserved_cv(): void
    {
        $worker = $this->workerWithFile();
        app(CvReservationService::class)->reserve($worker, $this->client(), $this->agent());

        $this->get(route('cvs.show', $worker->id))->assertNotFound();
    }

    public function test_the_pdf_route_streams_an_available_cv(): void
    {
        $worker = $this->workerWithFile();

        $response = $this->get(route('cvs.pdf', $worker->id));

        $response->assertOk();
        $this->assertSame('application/pdf', $response->headers->get('Content-Type'));
        // The stored name is an upload artefact and must never leak.
        $this->assertStringContainsString('cv-'.$worker->id.'.pdf',
            $response->headers->get('Content-Disposition'));
    }

    public function test_the_pdf_route_explains_a_reservation_instead_of_404ing(): void
    {
        $worker = $this->workerWithFile();
        app(CvReservationService::class)->reserve($worker, $this->client(), $this->agent());

        $this->get(route('cvs.pdf', $worker->id))
            ->assertStatus(410)
            ->assertSee(__('cv-panel.public.reserved_title'));
    }

    public function test_the_pdf_route_404s_for_an_unknown_id(): void
    {
        $this->get(route('cvs.pdf', 999999))->assertNotFound();
    }

    public function test_the_public_pages_never_expose_passport_or_phone(): void
    {
        $worker = $this->workerWithFile([
            'passport_number' => 'EP1234567',
            'phone' => '0590000000',
        ]);

        $this->get(route('cvs.show', $worker->id))
            ->assertOk()
            ->assertDontSee('EP1234567')
            ->assertDontSee('0590000000');
    }

    public function test_the_partial_response_returns_only_cards(): void
    {
        $worker = $this->workerWithFile();

        $response = $this->get(route('cvs.index', ['partial' => 1]));

        $response->assertOk()->assertSee('#'.$worker->id);
        // A partial must not carry the whole page chrome.
        $response->assertDontSee('<!DOCTYPE html>', false);
    }
}
