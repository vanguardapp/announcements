<?php

namespace Vanguard\Announcements\Slots;

use Illuminate\Support\Facades\Auth;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Plugins\Contracts\Slot;

class NavbarItemsSlot implements Slot
{
    public function __construct(private readonly AnnouncementsRepository $announcements) {}

    /**
     * {@inheritdoc}
     */
    public function component(): string
    {
        return 'announcements::NavbarBell';
    }

    /**
     * {@inheritdoc}
     */
    public function data(array $context = []): array
    {
        $announcements = $this->announcements->latest(5);
        $announcements->load('creator');

        $user = Auth::user();

        return [
            'announcements' => $announcements->map(fn (Announcement $announcement) => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'url' => route('announcements.show', $announcement),
                'creator' => $announcement->creator->present()->name,
                'avatar' => $announcement->creator->present()->avatar,
                'createdAt' => $announcement->created_at->diffForHumans(null, true, true),
            ])->all(),
            'unread' => $announcements->isNotEmpty()
                && $user !== null
                && $announcements->first()->wasReadBy($user),
            'listUrl' => route('announcements.list'),
            'readUrl' => route('announcements.read'),
        ];
    }
}
