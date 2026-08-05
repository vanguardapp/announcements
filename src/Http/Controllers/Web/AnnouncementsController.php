<?php

namespace Vanguard\Announcements\Http\Controllers\Web;

use App\Http\Controllers\Controller;
use Illuminate\Http\RedirectResponse;
use Inertia\Inertia;
use Inertia\Response as InertiaResponse;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\EmailNotificationRequested;
use Vanguard\Announcements\Http\Requests\AnnouncementRequest;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Announcements\Support\AnnouncementPayload;

/**
 * Class AnnouncementsController
 */
class AnnouncementsController extends Controller
{
    public function __construct(private readonly AnnouncementsRepository $announcements)
    {
        $this->middleware('permission:announcements.manage')->except('show');
    }

    /**
     * Displays the plugin index page.
     */
    public function index(): InertiaResponse
    {
        $announcements = $this->announcements->paginate();
        $announcements->load('creator');

        return Inertia::render('announcements::Index', [
            'announcements' => AnnouncementPayload::rows($announcements),
        ]);
    }

    /**
     * Shows the create announcement form.
     */
    public function create(): InertiaResponse
    {
        return Inertia::render('announcements::Form', [
            'edit' => false,
            'announcement' => null,
        ]);
    }

    /**
     * Stores the announcement inside the database.
     */
    public function store(AnnouncementRequest $request): RedirectResponse
    {
        $announcement = $this->announcements->createFor(
            auth()->user(),
            $request->title,
            $request->body
        );

        if ($request->email_notification) {
            EmailNotificationRequested::dispatch($announcement);
        }

        return redirect()->route('announcements.index')
            ->withSuccess(__('Announcement created successfully.'));
    }

    /**
     * Renders "view announcement" page.
     */
    public function show(Announcement $announcement): InertiaResponse
    {
        return Inertia::render('announcements::Show', [
            'announcement' => AnnouncementPayload::card($announcement),
        ]);
    }

    /**
     * Renders the form for editing the announcement.
     */
    public function edit(Announcement $announcement): InertiaResponse
    {
        return Inertia::render('announcements::Form', [
            'edit' => true,
            'announcement' => [
                'id' => $announcement->id,
                'title' => $announcement->title,
                'body' => $announcement->body,
            ],
        ]);
    }

    /**
     * Updates announcement details.
     */
    public function update(Announcement $announcement, AnnouncementRequest $request): RedirectResponse
    {
        $this->announcements->update(
            $announcement,
            $request->title,
            $request->body
        );

        return redirect()->route('announcements.index')
            ->withSuccess(__('Announcement updated successfully.'));
    }

    /**
     * Removes announcement from the system.
     */
    public function destroy(Announcement $announcement): RedirectResponse
    {
        $this->announcements->delete($announcement);

        return redirect()->route('announcements.index')
            ->withSuccess(__('Announcement deleted successfully.'));
    }
}
