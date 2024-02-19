<?php

namespace Vanguard\Announcements\Repositories;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Database\Eloquent\Collection;
use Vanguard\Announcements\Announcement;
use Vanguard\User;

interface AnnouncementsRepository
{
    /**
     * Get latest announcements.
     *
     * @return Collection<Announcement>
     */
    public function latest(int $count = 5): Collection;

    /**
     * Paginate announcements in descending order.
     */
    public function paginate(int $perPage = 10): LengthAwarePaginator;

    /**
     * Create an announcement for user.
     */
    public function createFor(User $user, string $title, string $body): Announcement;

    /**
     * Find announcement by ID.
     */
    public function find(int $id): ?Announcement;

    /**
     * Update announcement.
     */
    public function update(Announcement $announcement, string $title, string $body): Announcement;

    /**
     * Remove announcement from the system.
     */
    public function delete(Announcement $announcement): bool;
}
