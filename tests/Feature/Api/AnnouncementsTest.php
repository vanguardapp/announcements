<?php

use Carbon\Carbon;
use Facades\Tests\Setup\UserFactory;
use Tests\Feature\ApiTestCase;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Database\Seeders\AnnouncementsDatabaseSeeder;
use Vanguard\Announcements\Http\Resources\AnnouncementResource;
use Vanguard\Announcements\Mail\AnnouncementEmail;

uses(ApiTestCase::class);

beforeEach(function () {
    $this->artisan('db:seed', ['--class' => AnnouncementsDatabaseSeeder::class]);
});

test('guests cannot paginate announcements', function () {
    $this->getJson('/api/announcements')->assertStatus(401);
});

test('any authenticated users can paginate announcements', function () {
    $user = UserFactory::user()->create();
    $announcements = Announcement::factory()->times(11)->create();

    $response = $this->actingAs($user, self::API_GUARD)
        ->getJson('/api/announcements?per_page=10')
        ->assertOk();

    $transformed = AnnouncementResource::collection($announcements->take(10))->resolve();

    $this->assertEquals($response->json('data'), $transformed);
    $response->assertJson([
        'meta' => [
            'current_page' => 1,
            'from' => 1,
            'to' => 10,
            'last_page' => 2,
            'path' => url('api/announcements'),
            'total' => 11,
            'per_page' => 10,
        ],
    ]);
});

test('paginate announcements with more records per page than allowed', function () {
    $this->actingAs(validUser(), self::API_GUARD)
        ->getJson('/api/announcements?per_page=140')
        ->assertStatus(422);
});

test('guests cannot create announcements', function () {
    $this->postJson('/api/announcements', validApiParams())
        ->assertUnauthorized();

    $this->assertEquals(0, Announcement::count());
});

test('users without appropriate permission cannot create announcements', function () {
    $user = UserFactory::user()->create();

    $this->actingAs($user, self::API_GUARD)
        ->postJson('/api/announcements', validApiParams())
        ->assertForbidden();

    $this->assertEquals(0, Announcement::count());
});

test('users with appropriate permission can create announcements', function () {
    $user = validUser();
    $data = validApiParams();

    $response = $this->actingAs($user, self::API_GUARD)
        ->postJson('/api/announcements', $data)
        ->assertStatus(201);

    $announcement = Announcement::first();

    $response->assertExactJson([
        'data' => (new AnnouncementResource($announcement))->resolve(),
    ]);

    $this->assertEquals($user->id, $announcement->user_id);
    $this->assertEquals($data['title'], $announcement->title);
    $this->assertEquals($data['body'], $announcement->body);
});

test('an email notification can be triggered when an announcement is created', function () {
    Mail::fake();

    $data = validApiParams(['email_notification' => true]);

    $this->actingAs(validUser(), self::API_GUARD)
        ->postJson('/api/announcements', $data)
        ->assertStatus(201);

    $announcement = Announcement::first();

    Mail::assertQueued(AnnouncementEmail::class, function ($mail) use ($announcement) {
        return $mail->announcement->id === $announcement->id;
    });
});

test('title field is required when creating an announcement', function () {
    $data = validApiParams(['title' => '']);

    $this->actingAs(validUser(), self::API_GUARD)
        ->postJson('/api/announcements', $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors('title');

    $this->assertEquals(0, Announcement::count());
});

test('body field is required when creating an announcement', function () {
    $data = validApiParams(['body' => '']);

    $this->actingAs(validUser(), self::API_GUARD)
        ->postJson('/api/announcements', $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors('body');

    $this->assertEquals(0, Announcement::count());
});

test('guests cannot view an announcement', function () {
    $announcement = Announcement::factory()->create();

    $this->getJson("/api/announcements/{$announcement->id}")
        ->assertUnauthorized();
});

test('authenticated users can view any announcement', function () {
    $user = UserFactory::user()->create();
    $announcement = Announcement::factory()->create();

    $this->actingAs($user, self::API_GUARD)
        ->getJson("/api/announcements/{$announcement->id}")
        ->assertOk()
        ->assertExactJson([
            'data' => (new AnnouncementResource($announcement))->resolve(),
        ]);
});

test('guests cannot update an announcement', function () {
    $announcement = Announcement::factory()->create();

    $this->putJson("/api/announcements/{$announcement->id}", validApiParams())
        ->assertUnauthorized();
});

test('users without approprite permission cannot update an announcement', function () {
    $user = UserFactory::user()->create();
    $announcement = Announcement::factory()->create();

    $this->actingAs($user, self::API_GUARD)
        ->putJson("/api/announcements/{$announcement->id}", validApiParams())
        ->assertForbidden();
});

test('users with approprite permission can update an announcement', function () {
    $user = validUser();
    $announcement = Announcement::factory()->create();

    $this->actingAs($user, self::API_GUARD)
        ->putJson("/api/announcements/{$announcement->id}", validApiParams())
        ->assertOk()
        ->assertExactJson([
            'data' => (new AnnouncementResource($announcement->fresh()))->resolve(),
        ]);
});

test('title field is required when updating an announcement', function () {
    $data = validApiParams(['title' => '']);
    $announcement = Announcement::factory()->create();

    $this->actingAs(validUser(), self::API_GUARD)
        ->putJson("/api/announcements/{$announcement->id}", $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors('title');
});

test('body field is required when updating an announcement', function () {
    $data = validApiParams(['body' => '']);
    $announcement = Announcement::factory()->create();

    $this->actingAs(validUser(), self::API_GUARD)
        ->putJson("/api/announcements/{$announcement->id}", $data)
        ->assertStatus(422)
        ->assertJsonValidationErrors('body');
});

test('guests cannot delete an announcement', function () {
    $announcement = Announcement::factory()->create();

    $this->deleteJson("/api/announcements/{$announcement->id}")
        ->assertUnauthorized();
});

test('users without appropriate permission cannot delete an announcement', function () {
    $user = UserFactory::user()->create();
    $announcement = Announcement::factory()->create();

    $this->actingAs($user, self::API_GUARD)
        ->deleteJson("/api/announcements/{$announcement->id}")
        ->assertForbidden();
});

test('users with appropriate permission can delete an announcement', function () {
    $announcement = Announcement::factory()->create();

    $this->actingAs(validUser(), self::API_GUARD)
        ->deleteJson("/api/announcements/{$announcement->id}")
        ->assertOk();

    $this->assertNull($announcement->fresh());
});

test('user announcements can be marked as read', function () {
    $user = UserFactory::user()->create([
        'announcements_last_read_at' => null,
    ]);

    Carbon::setTestNow(now());

    $this->actingAs($user, self::API_GUARD)
        ->post('/api/announcements/read');

    $this->assertEquals(
        now()->format('Y-m-d H:i:s'),
        $user->fresh()->announcements_last_read_at
    );
});

/**
 * @return mixed
 */
function validUser()
{
    return UserFactory::user()->withPermissions('announcements.manage')->create();
}

/**
 * @return array
 */
function validApiParams(array $overrides = [])
{
    return array_merge([
        'title' => 'Foo Announcement',
        'body' => 'This is the announcement body.',
        'email_notification' => false,
    ], $overrides);
}
