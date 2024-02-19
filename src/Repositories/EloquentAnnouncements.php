<?php

namespace Vanguard\Announcements\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\Created;
use Vanguard\Announcements\Events\Deleted;
use Vanguard\Announcements\Events\Updated;
use Vanguard\User;

class EloquentAnnouncements implements AnnouncementsRepository
{
    /**
     * Get latest announcements.
     *
     * @return Collection<Announcement>
     */
    public function latest(int $count = 5): Collection
    {
        return Announcement::latest()->take($count)->get();
    }

    /**
     * Paginate announcements in descending order.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator
    {
        return Announcement::latest()->paginate($perPage);
    }

    /**
     * Create an announcement for user.
     */
    public function createFor(User $user, string $title, string $body): Announcement
    {
        $announcement = Announcement::create([
            'title' => $title,
            'body' => $body,
            'user_id' => $user->id,
        ]);

        Created::dispatch($announcement);

        return $announcement;
    }

    /**
     * Find announcement by ID.
     */
    public function find($id): ?Announcement
    {
        return Announcement::find($id);
    }

    /**
     * Update announcement.
     */
    public function update(Announcement $announcement, string $title, string $body): Announcement
    {
        $announcement->update([
            'title' => $title,
            'body' => $body,
        ]);

        Updated::dispatch($announcement);

        return $announcement;
    }

    /**
     * Remove announcement from the system.
     *
     * @throws \Exception
     */
    public function delete(Announcement $announcement): bool
    {
        if ($announcement->delete()) {
            Deleted::dispatch($announcement);

            return true;
        }

        return false;
    }
}
