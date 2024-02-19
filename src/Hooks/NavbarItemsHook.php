<?php

namespace Vanguard\Announcements\Hooks;

use Illuminate\Contracts\View\View;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Plugins\Contracts\Hook;

class NavbarItemsHook implements Hook
{
    public function __construct(private readonly AnnouncementsRepository $announcements)
    {
    }

    /**
     * Execute the hook action.
     */
    public function handle(): View
    {
        $announcements = $this->announcements->latest(5);
        $announcements->load('creator');

        return view('announcements::partials.navbar.list', compact('announcements'));
    }
}
