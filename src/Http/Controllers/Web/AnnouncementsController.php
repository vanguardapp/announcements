<?php

namespace Vanguard\Announcements\Http\Controllers\Web;

use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\EmailNotificationRequested;
use Vanguard\Announcements\Http\Requests\AnnouncementRequest;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Http\Controllers\Controller;

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
    public function index(): View
    {
        $announcements = $this->announcements->paginate();
        $announcements->load('creator');

        return view('announcements::index', compact('announcements'));
    }

    /**
     * Shows the create announcement form.
     */
    public function create(): View
    {
        return view('announcements::add-edit', ['edit' => false]);
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
    public function show(Announcement $announcement): View
    {
        return view('announcements::show', compact('announcement'));
    }

    /**
     * Renders the form for editing the announcement.
     */
    public function edit(Announcement $announcement): View
    {
        return view('announcements::add-edit', [
            'edit' => true,
            'announcement' => $announcement,
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
