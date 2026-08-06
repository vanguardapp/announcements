<?php

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Arr;
use Illuminate\Testing\TestResponse;
use Tests\TestCase;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Database\Seeders\AnnouncementsDatabaseSeeder;
use Vanguard\Announcements\Mail\AnnouncementEmail;

uses(TestCase::class);
uses(RefreshDatabase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => 'RolesSeeder']);
    $this->artisan('db:seed', ['--class' => 'PermissionsSeeder']);
    $this->artisan('db:seed', ['--class' => 'CountriesSeeder']);
    $this->artisan('db:seed', ['--class' => AnnouncementsDatabaseSeeder::class]);
});

test('guests cannot view announcement index page', function () {
    $this->get('/announcements')->assertRedirect('login');
});

test('users without appropriate permission cannot view announcement list', function () {
    $user = UserFactory::user()->create();

    $this->actingAs($user)->get('/announcements')->assertForbidden();
});

test('users with appropriate permission can view the announcement list', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    Announcement::factory()->create([
        'user_id' => $user->id,
        'title' => 'Foo Announcement',
    ]);

    $response = $this->actingAs($user)->get('/announcements')->assertOk();

    $titles = collect($response->viewData('page')['props']['announcements']['data'])
        ->pluck('title');

    $this->assertTrue($titles->contains('Foo Announcement'));
});

test('guests cannot create announcements', function () {
    $this->post('/announcements', validWebParams())->assertRedirect('login');

    $this->assertEquals(0, Announcement::count());
});

test('users without appropriate permission cannot create announcements', function () {
    $user = UserFactory::user()->create();

    $this->actingAs($user)->post('/announcements', validWebParams())->assertForbidden();

    $this->assertEquals(0, Announcement::count());
});

test('users with appropriate permission can create announcements', function () {
    Mail::fake();

    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $data = validWebParams();

    $this->actingAs($user)->post('/announcements', $data)
        ->assertRedirect('/announcements');

    $announcement = Announcement::first();

    $this->assertEquals($data['title'], $announcement->title);
    $this->assertEquals($data['body'], $announcement->body);

    Mail::assertNothingSent();
});

test('title field is required when creating an announcement', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $data = validWebParams(['title' => '']);

    $this->actingAs($user)
        ->from('/announcements/create')
        ->post('/announcements', $data)
        ->assertRedirect('/announcements/create')
        ->assertSessionHasErrors('title');
});

test('body field is required when creating an announcement', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $data = validWebParams(['body' => '']);

    $this->actingAs($user)
        ->from('/announcements/create')
        ->post('/announcements', $data)
        ->assertRedirect('/announcements/create')
        ->assertSessionHasErrors('body');
});

test('an email notification can be triggered when an announcement is created', function () {
    Mail::fake();

    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $data = validWebParams(['email_notification' => '1']);

    $this->actingAs($user)
        ->from('/announcements/create')
        ->post('/announcements', $data)
        ->assertRedirect('/announcements');

    $announcement = Announcement::first();

    Mail::assertQueued(AnnouncementEmail::class, function ($mail) use ($announcement) {
        return $mail->announcement->id === $announcement->id;
    });
});

test('guests cannot view an announcement', function () {
    $announcement = Announcement::factory()->create();

    $this->get('/announcements/'.$announcement->id)
        ->assertRedirect('/login');
});

test('authenticated users can see an announcement', function () {
    $this->withoutExceptionHandling();

    $user = UserFactory::user()->create();
    $admin = UserFactory::admin()->create();

    $data = Arr::except(validWebParams(['user_id' => $admin->id]), 'email_notification');

    $announcement = Announcement::factory()->create($data);

    foreach ([$user, $admin] as $viewer) {
        $page = $this->actingAs($viewer)
            ->get('/announcements/'.$announcement->id)
            ->assertOk()
            ->viewData('page');

        $this->assertSame('announcements::Show', $page['component']);
        $this->assertSame($data['title'], $page['props']['announcement']['title']);
    }
});

test('guests cannot edit announcements', function () {
    $announcement = Announcement::factory()->create();

    $this->get('/announcements/'.$announcement->id)->assertRedirect('login');
    $this->put('/announcements/'.$announcement->id, validWebParams())->assertRedirect('login');

    $this->assertEquals($announcement->title, $announcement->fresh()->title);
    $this->assertEquals($announcement->body, $announcement->fresh()->body);
});

test('users with appropriate permissions can edit an announcement', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $announcement = Announcement::factory()->create();

    $data = validWebParams();

    $this->actingAs($user)
        ->from("/announcements/{$announcement->id}/edit")
        ->put('/announcements/'.$announcement->id, $data)
        ->assertRedirect('/announcements')
        ->assertSessionDoesntHaveErrors();

    $this->assertEquals($data['title'], $announcement->fresh()->title);
    $this->assertEquals($data['body'], $announcement->fresh()->body);
});

test('title field is required when updating an announcement', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $announcement = Announcement::factory()->create();

    $data = validWebParams(['title' => '']);

    $this->actingAs($user)
        ->from("/announcements/{$announcement->id}/edit")
        ->put('/announcements/'.$announcement->id, $data)
        ->assertRedirect("/announcements/{$announcement->id}/edit")
        ->assertSessionHasErrors('title');
});

test('body field is required when updating an announcement', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();

    $announcement = Announcement::factory()->create();

    $data = validWebParams(['body' => '']);

    $this->actingAs($user)
        ->from("/announcements/{$announcement->id}/edit")
        ->put('/announcements/'.$announcement->id, $data)
        ->assertRedirect("/announcements/{$announcement->id}/edit")
        ->assertSessionHasErrors('body');
});

test('guests cannot delete an announcement', function () {
    $announcement = Announcement::factory()->create();

    $this->delete('/announcements/'.$announcement->id)->assertRedirect('login');

    $this->assertNotNull($announcement->fresh());
});

test('users without appropriate permission cannot delete an announcement', function () {
    $user = UserFactory::user()->create();
    $announcement = Announcement::factory()->create();

    $this->actingAs($user)
        ->delete('/announcements/'.$announcement->id)
        ->assertForbidden();

    $this->assertNotNull($announcement->fresh());
});

test('users with appropriate permission can delete an announcement', function () {
    $user = UserFactory::user()->withPermissions('announcements.manage')->create();
    $announcement = Announcement::factory()->create();

    $this->actingAs($user)
        ->delete('/announcements/'.$announcement->id)
        ->assertRedirect('/announcements');

    $this->assertNull($announcement->fresh());
});

test('guests cannot view announcement list', function () {
    $this->get('/announcements/list')->assertRedirect('login');
});

test('any authenticated user can view announcement list', function () {
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
});

test('authenticated users can see the announcements header section', function () {
    $user = UserFactory::user()->create();

    $data = ['title' => 'some random announcement'];

    Announcement::factory()->create($data);

    // The bell is contributed to the host navbar as a slot, so what matters
    // is that the slot is registered and carries the announcements.
    $bell = navbarBell($this->actingAs($user)->get('/'));

    $this->assertNotNull($bell);
    $this->assertContains($data['title'], collect($bell['props']['announcements'])->pluck('title'));
});

test('a red dot indicator is displayed if user has unread announcements', function () {
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
    $this->assertFalse(navbarBell($this->actingAs($userA)->get('/'))['props']['unread']);
    $this->assertTrue(navbarBell($this->actingAs($userB)->get('/'))['props']['unread']);
});

test('user announcements can be marked as read', function () {
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
});

/**
 * The bell slot this plugin contributes to the host navbar.
 *
 * @return array{component: string, props: array<string, mixed>}|null
 */
function navbarBell(TestResponse $response): ?array
{
    return collect($response->viewData('page')['props']['vanguard']['slots']['navbar:items'])
        ->firstWhere('component', 'announcements::NavbarBell');
}

function validWebParams(array $overrides = [])
{
    return array_merge([
        'title' => 'Foo Announcement',
        'body' => 'This is the announcement body.',
        'email_notification' => '0',
    ], $overrides);
}
