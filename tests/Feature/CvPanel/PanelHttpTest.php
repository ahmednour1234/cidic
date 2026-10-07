<?php

namespace Tests\Feature\CvPanel;

use App\Enums\UserRole;
use App\Models\Worker;
use App\Services\CvPanel\CvReservationService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class PanelHttpTest extends CvPanelTestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('cv_private');
    }

    // ------------------------------------------------------------- access

    public function test_a_guest_is_sent_to_the_panel_login(): void
    {
        $this->get(route('cv-panel.dashboard'))
            ->assertRedirect(route('cv-panel.login'));
    }

    public function test_a_user_without_a_department_is_refused(): void
    {
        $outsider = $this->panelUser(null, UserRole::Staff);

        $this->actingAs($outsider)
            ->get(route('cv-panel.dashboard'))
            ->assertForbidden();
    }

    public function test_each_panel_role_reaches_the_dashboard(): void
    {
        foreach ([$this->coordinator(), $this->agent(), $this->manager(), $this->superAdmin()] as $user) {
            $this->actingAs($user)->get(route('cv-panel.dashboard'))->assertOk();
        }
    }

    public function test_login_refuses_a_non_panel_user(): void
    {
        $outsider = $this->panelUser(null, UserRole::Staff);

        $this->post(route('cv-panel.login.store'), [
            'email' => $outsider->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_login_refuses_an_inactive_user(): void
    {
        $inactive = $this->panelUser(\App\Enums\Department::Coordination, active: false);

        $this->post(route('cv-panel.login.store'), [
            'email' => $inactive->email,
            'password' => 'password123',
        ])->assertSessionHasErrors('email');

        $this->assertGuest();
    }

    public function test_a_panel_user_can_log_in(): void
    {
        $agent = $this->agent();

        $this->post(route('cv-panel.login.store'), [
            'email' => $agent->email,
            'password' => 'password123',
        ])->assertRedirect(route('cv-panel.dashboard'));

        $this->assertAuthenticatedAs($agent);
    }

    // --------------------------------------------------------------- list

    public function test_the_list_hides_assigned_rows(): void
    {
        $worker = $this->worker(['name' => 'عاملة معيّنة']);
        $reserver = $this->agent();
        app(CvReservationService::class)->reserve($worker, $this->client(), $reserver);
        app(\App\Services\CvPanel\ContractService::class)
            ->createFromReservation($worker->fresh(), $reserver);

        $this->actingAs($this->superAdmin())
            ->get(route('cv-panel.cvs.index'))
            ->assertOk()
            ->assertDontSee('عاملة معيّنة');
    }

    public function test_a_coordinator_only_sees_their_own_nationalities(): void
    {
        $mine = $this->nationality('ethiopia', 'et');
        $theirs = $this->nationality('kenya', 'ke');

        $this->worker(['nationality_id' => $mine->id, 'name' => 'ضمن نطاقي']);
        $this->worker(['nationality_id' => $theirs->id, 'name' => 'خارج نطاقي']);

        $this->actingAs($this->coordinator([$mine]))
            ->get(route('cv-panel.cvs.index'))
            ->assertOk()
            ->assertSee('ضمن نطاقي')
            ->assertDontSee('خارج نطاقي');
    }

    public function test_searching_by_id_finds_the_row(): void
    {
        $worker = $this->worker(['name' => 'عاملة بالبحث']);

        $this->actingAs($this->superAdmin())
            ->get(route('cv-panel.cvs.index', ['search' => $worker->id]))
            ->assertOk()
            ->assertSee('عاملة بالبحث');
    }

    // ------------------------------------------------------------- upload

    public function test_an_agent_cannot_open_the_upload_page(): void
    {
        $this->actingAs($this->agent())
            ->get(route('cv-panel.upload'))
            ->assertForbidden();
    }

    public function test_a_coordinator_cannot_upload_outside_their_nationalities(): void
    {
        $mine = $this->nationality('ethiopia', 'et');
        $theirs = $this->nationality('kenya', 'ke');

        $this->actingAs($this->coordinator([$mine]))
            ->post(route('cv-panel.upload.store'), [
                'nationality_id' => $theirs->id,
                'experience' => '1-3',
                'religion' => 'muslim',
                'cvs' => [UploadedFile::fake()->create('a.pdf', 10, 'application/pdf')],
            ])
            ->assertSessionHasErrors('nationality_id');
    }

    public function test_a_coordinator_uploads_into_their_own_nationality(): void
    {
        $mine = $this->nationality('ethiopia', 'et');

        $this->actingAs($this->coordinator([$mine]))
            ->post(route('cv-panel.upload.store'), [
                'nationality_id' => $mine->id,
                'experience' => '1-3',
                'religion' => 'muslim',
                'cvs' => [UploadedFile::fake()->create('سيرة.pdf', 10, 'application/pdf')],
            ])
            ->assertRedirect();

        $this->assertSame(1, Worker::count());
    }

    public function test_a_non_pdf_upload_is_rejected(): void
    {
        $mine = $this->nationality('ethiopia', 'et');

        $this->actingAs($this->coordinator([$mine]))
            ->post(route('cv-panel.upload.store'), [
                'nationality_id' => $mine->id,
                'experience' => '1-3',
                'religion' => 'muslim',
                'cvs' => [UploadedFile::fake()->create('virus.exe', 10)],
            ])
            ->assertSessionHasErrors('cvs.0');
    }

    // ------------------------------------------------------------ deletes

    public function test_bulk_delete_skips_booked_rows(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $free = $this->worker(['nationality_id' => $nationality->id]);
        $booked = $this->worker(['nationality_id' => $nationality->id]);
        app(CvReservationService::class)->reserve($booked, $this->client(), $this->agent());

        $this->actingAs($coordinator)
            ->delete(route('cv-panel.cvs.bulk-destroy'), ['ids' => [$free->id, $booked->id]])
            ->assertRedirect();

        $this->assertSoftDeleted('workers', ['id' => $free->id]);
        $this->assertNotSoftDeleted('workers', ['id' => $booked->id]);
    }

    public function test_an_agent_cannot_delete(): void
    {
        $worker = $this->worker();

        $this->actingAs($this->agent())
            ->delete(route('cv-panel.cvs.destroy', $worker->id))
            ->assertRedirect();

        $this->assertNotSoftDeleted('workers', ['id' => $worker->id]);
    }

    // -------------------------------------------------------- reservation

    public function test_a_coordinator_cannot_open_the_reserve_page(): void
    {
        $worker = $this->worker();

        $this->actingAs($this->coordinator())
            ->get(route('cv-panel.cvs.reserve', $worker->id))
            ->assertForbidden();
    }

    public function test_an_agent_reserves_with_an_inline_client(): void
    {
        $worker = $this->worker();

        $this->actingAs($this->agent())
            ->post(route('cv-panel.cvs.reserve.store', $worker->id), [
                'client_name' => 'عميل واتساب',
                'client_phone' => '0501234567',
            ])
            ->assertRedirect(route('cv-panel.cvs.index'));

        $worker = $worker->fresh();

        $this->assertSame(Worker::STATUS_RESERVED, $worker->status);
        $this->assertNotNull($worker->client_id);
    }

    public function test_client_search_needs_two_characters(): void
    {
        $this->client('عميل البحث');

        $this->actingAs($this->agent())
            ->getJson(route('cv-panel.clients.search', ['q' => 'ع']))
            ->assertOk()
            ->assertExactJson([]);
    }

    public function test_client_search_returns_matches(): void
    {
        $client = $this->client('عميل البحث');

        $this->actingAs($this->agent())
            ->getJson(route('cv-panel.clients.search', ['q' => 'عميل']))
            ->assertOk()
            ->assertJsonFragment(['id' => $client->id, 'name' => 'عميل البحث']);
    }

    public function test_a_coordinator_cannot_search_clients(): void
    {
        $this->actingAs($this->coordinator())
            ->getJson(route('cv-panel.clients.search', ['q' => 'عميل']))
            ->assertForbidden();
    }

    // ------------------------------------------------------- other pages

    public function test_the_guide_renders_for_every_role(): void
    {
        foreach ([$this->coordinator(), $this->agent(), $this->manager()] as $user) {
            $this->actingAs($user)->get(route('cv-panel.guide'))->assertOk();
        }
    }

    public function test_only_managers_reach_the_users_page(): void
    {
        $this->actingAs($this->manager())->get(route('cv-panel.users.index'))->assertOk();
        $this->actingAs($this->coordinator())->get(route('cv-panel.users.index'))->assertForbidden();
        $this->actingAs($this->agent())->get(route('cv-panel.users.index'))->assertForbidden();
    }

    public function test_a_user_cannot_disable_themselves(): void
    {
        $manager = $this->manager();

        $this->actingAs($manager)
            ->post(route('cv-panel.users.toggle', $manager))
            ->assertRedirect();

        $this->assertTrue($manager->fresh()->is_active);
    }

    public function test_changing_a_department_away_from_coordination_detaches_nationalities(): void
    {
        $nationality = $this->nationality();
        $coordinator = $this->coordinator([$nationality]);

        $this->actingAs($this->manager())
            ->put(route('cv-panel.users.update', $coordinator), [
                'name' => $coordinator->name,
                'email' => $coordinator->email,
                'department' => \App\Enums\Department::CustomerService->value,
            ])
            ->assertRedirect();

        $this->assertCount(0, $coordinator->fresh()->nationalities);
    }

    public function test_the_reserved_page_is_coordinator_only(): void
    {
        $this->actingAs($this->coordinator())->get(route('cv-panel.cvs.reserved'))->assertOk();
        $this->actingAs($this->agent())->get(route('cv-panel.cvs.reserved'))->assertForbidden();
    }

    public function test_the_notifications_page_renders(): void
    {
        $this->actingAs($this->agent())
            ->get(route('cv-panel.notifications.index'))
            ->assertOk();
    }
}
