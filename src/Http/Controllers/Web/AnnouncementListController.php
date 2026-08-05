<?php

namespace Vanguard\Announcements\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Announcements\Support\AnnouncementPayload;

class AnnouncementListController extends Controller
{
    public function __construct(private readonly AnnouncementsRepository $announcements) {}

    /**
     * Displays the plugin index page.
     */
    public function index(): InertiaResponse
    {
        $announcements = $this->announcements->paginate(7);
        $announcements->load('creator');

        return Inertia::render('announcements::List', [
            'announcements' => AnnouncementPayload::cards($announcements),
        ]);
    }
}
