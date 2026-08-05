<?php

namespace Vanguard\Announcements\Tests\Feature\Web;

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Mail;
use Tests\TestCase;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Database\Seeders\AnnouncementsDatabaseSeeder;
use Vanguard\Announcements\Mail\AnnouncementEmail;

class AnnouncementsTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        $this->artisan('db:seed', ['--class' => 'RolesSeeder']);
        $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
        $this->artisan('db:seed', ['--class' => 'CountriesSeeder']);
        $this->artisan('db:seed', ['--class' => AnnouncementsDatabaseSeeder::class]);
    }

    public function test_guests_cannot_view_announcement_index_page()
    {
        $this->get('/announcements')->assertRedirect('login');
    }

    public function test_users_without_appropriate_permission_cannot_view_announcement_list()
    {
        $user = UserFactory::user()->create();

        $this->actingAs($user)->get('/announcements')->assertForbidden();
    }

    public function test_users_with_appropriate_permission_can_view_the_announcement_list()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        Announcement::factory()->create([
            'user_id' => $user->id,
            'title' => 'Foo Announcement',
        ]);

        $response = $this->actingAs($user)->get('/announcements')->assertOk();

        $titles = collect($response->viewData('page')['props']['announcements']['data'])
            ->pluck('title');

        $this->assertTrue($titles->contains('Foo Announcement'));
    }

    public function test_guests_cannot_create_announcements()
    {
        $this->post('/announcements', $this->validParams())->assertRedirect('login');

        $this->assertEquals(0, Announcement::count());
    }

    public function test_users_without_appropriate_permission_cannot_create_announcements()
    {
        $user = UserFactory::user()->create();

        $this->actingAs($user)->post('/announcements', $this->validParams())->assertForbidden();

        $this->assertEquals(0, Announcement::count());
    }

    public function test_users_with_appropriate_permission_can_create_announcements()
    {
        Mail::fake();

        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams();

        $this->actingAs($user)->post('/announcements', $data)
            ->assertRedirect('/announcements');

        $announcement = Announcement::first();

        $this->assertEquals($data['title'], $announcement->title);
        $this->assertEquals($data['body'], $announcement->body);

        Mail::assertNothingSent();
    }

    public function test_title_field_is_required_when_creating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams(['title' => '']);

        $this->actingAs($user)
            ->from('/announcements/create')
            ->post('/announcements', $data)
            ->assertRedirect('/announcements/create')
            ->assertSessionHasErrors('title');
    }

    public function test_body_field_is_required_when_creating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams(['body' => '']);

        $this->actingAs($user)
            ->from('/announcements/create')
            ->post('/announcements', $data)
            ->assertRedirect('/announcements/create')
            ->assertSessionHasErrors('body');
    }

    public function test_an_email_notification_can_be_triggered_when_an_announcement_is_created()
    {
        Mail::fake();

        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $data = $this->validParams(['email_notification' => '1']);

        $this->actingAs($user)
            ->from('/announcements/create')
            ->post('/announcements', $data)
            ->assertRedirect('/announcements');

        $announcement = Announcement::first();

        Mail::assertQueued(AnnouncementEmail::class, function ($mail) use ($announcement) {
            return $mail->announcement->id === $announcement->id;
        });
    }

    public function test_guests_cannot_view_an_announcement()
    {
        $announcement = Announcement::factory()->create();

        $this->get('/announcements/'.$announcement->id)
            ->assertRedirect('/login');
    }

    public function test_authenticated_users_can_see_an_announcement()
    {
        $this->withoutExceptionHandling();

        $user = UserFactory::user()->create();
        $admin = UserFactory::admin()->create();

        $data = Arr::except($this->validParams(['user_id' => $admin->id]), 'email_notification');

        $announcement = Announcement::factory()->create($data);

        foreach ([$user, $admin] as $viewer) {
            $page = $this->actingAs($viewer)
                ->get('/announcements/'.$announcement->id)
                ->assertOk()
                ->viewData('page');

            $this->assertSame('announcements::Show', $page['component']);
            $this->assertSame($data['title'], $page['props']['announcement']['title']);
        }
    }

    public function test_guests_cannot_edit_announcements()
    {
        $announcement = Announcement::factory()->create();

        $this->get('/announcements/'.$announcement->id)->assertRedirect('login');
        $this->put('/announcements/'.$announcement->id, $this->validParams())->assertRedirect('login');

        $this->assertEquals($announcement->title, $announcement->fresh()->title);
        $this->assertEquals($announcement->body, $announcement->fresh()->body);
    }

    public function test_users_with_appropriate_permissions_can_edit_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $announcement = Announcement::factory()->create();

        $data = $this->validParams();

        $this->actingAs($user)
            ->from("/announcements/{$announcement->id}/edit")
            ->put('/announcements/'.$announcement->id, $data)
            ->assertRedirect('/announcements')
            ->assertSessionDoesntHaveErrors();

        $this->assertEquals($data['title'], $announcement->fresh()->title);
        $this->assertEquals($data['body'], $announcement->fresh()->body);
    }

    public function test_title_field_is_required_when_updating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $announcement = Announcement::factory()->create();

        $data = $this->validParams(['title' => '']);

        $this->actingAs($user)
            ->from("/announcements/{$announcement->id}/edit")
            ->put('/announcements/'.$announcement->id, $data)
            ->assertRedirect("/announcements/{$announcement->id}/edit")
            ->assertSessionHasErrors('title');
    }

    public function test_body_field_is_required_when_updating_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();

        $announcement = Announcement::factory()->create();

        $data = $this->validParams(['body' => '']);

        $this->actingAs($user)
            ->from("/announcements/{$announcement->id}/edit")
            ->put('/announcements/'.$announcement->id, $data)
            ->assertRedirect("/announcements/{$announcement->id}/edit")
            ->assertSessionHasErrors('body');
    }

    public function test_guests_cannot_delete_an_announcement()
    {
        $announcement = Announcement::factory()->create();

        $this->delete('/announcements/'.$announcement->id)->assertRedirect('login');

        $this->assertNotNull($announcement->fresh());
    }

    public function test_users_without_appropriate_permission_cannot_delete_an_announcement()
    {
        $user = UserFactory::user()->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($user)
            ->delete('/announcements/'.$announcement->id)
            ->assertForbidden();

        $this->assertNotNull($announcement->fresh());
    }

    public function test_users_with_appropriate_permission_can_delete_an_announcement()
    {
        $user = UserFactory::user()->withPermissions('announcements.manage')->create();
        $announcement = Announcement::factory()->create();

        $this->actingAs($user)
            ->delete('/announcements/'.$announcement->id)
            ->assertRedirect('/announcements');

        $this->assertNull($announcement->fresh());
    }

    public function test_guests_cannot_view_announcement_list()
    {
        $this->get('/announcements/list')->assertRedirect('login');
    }

    public function test_any_authenticated_user_can_view_announcement_list()
    {
        $user = UserFactory::user()->create();

        $announcementA = Announcement::factory()->create();
        $announcementB = Announcement::factory()->create();

        $cards = collect(
            $this->actingAs($user)
                ->get('/announcements/list')
                ->viewData('page')['props']['announcements']['data']
        );

        foreach ([$announcementA, $announcementB] as $announcement) {
            $card = $cards->firstWhere('id', $announcement->id);

            $this->assertNotNull($card);
            $this->assertSame($announcement->title, $card['title']);
            $this->assertSame((string) $announcement->parsed_body, $card['parsedBody']);
        }
    }

    public function test_authenticated_users_can_see_the_announcements_header_section()
    {
        $user = UserFactory::user()->create();

        $data = ['title' => 'some random announcement'];

        Announcement::factory()->create($data);

        // The bell is contributed to the host navbar as a slot, so what matters
        // is that the slot is registered and carries the announcements.
        $bell = $this->navbarBell($this->actingAs($user)->get('/'));

        $this->assertNotNull($bell);
        $this->assertContains($data['title'], collect($bell['props']['announcements'])->pluck('title'));
    }

    public function test_a_red_dot_indicator_is_displayed_if_user_has_unread_announcements()
    {
        Announcement::factory()->create([
            'created_at' => now()->subMinutes(3),
        ]);

        $userA = UserFactory::user()->create([
            'announcements_last_read_at' => now(),
        ]);

        $userB = UserFactory::user()->create([
            'announcements_last_read_at' => now()->subMinutes(5),
        ]);

        // `unread` drives the dot on the bell.
        $this->assertFalse($this->navbarBell($this->actingAs($userA)->get('/'))['props']['unread']);
        $this->assertTrue($this->navbarBell($this->actingAs($userB)->get('/'))['props']['unread']);
    }

    public function test_user_announcements_can_be_marked_as_read()
    {
        $user = UserFactory::user()->create([
            'announcements_last_read_at' => null,
        ]);

        Carbon::setTestNow(now());

        $this->actingAs($user)
            ->post('/announcements/read');

        $this->assertEquals(
            now()->format('Y-m-d H:i:s'),
            $user->fresh()->announcements_last_read_at
        );
    }

    /**
     * The bell slot this plugin contributes to the host navbar.
     *
     * @return array{component: string, props: array<string, mixed>}|null
     */
    private function navbarBell(TestResponse $response): ?array
    {
        return collect($response->viewData('page')['props']['vanguard']['slots']['navbar:items'])
            ->firstWhere('component', 'announcements::NavbarBell');
    }

    private function validParams(array $overrides = [])
    {
        return array_merge([
            'title' => 'Foo Announcement',
            'body' => 'This is the announcement body.',
            'email_notification' => '0',
        ], $overrides);
    }
}
