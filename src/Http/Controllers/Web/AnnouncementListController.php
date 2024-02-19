<?php

namespace Vanguard\Announcements\Http\Controllers\Web;

use Illuminate\Contracts\View\View;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Http\Controllers\Controller;

class AnnouncementListController extends Controller
{
    public function __construct(private readonly AnnouncementsRepository $announcements)
    {
    }

    /**
     * Displays the plugin index page.
     */
    public function index(): View
    {
        $announcements = $this->announcements->paginate(7);
        $announcements->load('creator');

        return view('announcements::list', compact('announcements'));
    }
}
