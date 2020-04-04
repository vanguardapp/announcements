<?php

namespace Vanguard\Announcements\Http\Controllers\Api;

use Illuminate\Http\Request;
use Spatie\QueryBuilder\AllowedFilter;
use Spatie\QueryBuilder\AllowedInclude;
use Spatie\QueryBuilder\QueryBuilder;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\EmailNotificationRequested;
use Vanguard\Announcements\Http\Requests\AnnouncementRequest;
use Vanguard\Announcements\Http\Resources\AnnouncementResource;
use Vanguard\Announcements\Repositories\AnnouncementsRepository;
use Vanguard\Http\Controllers\Api\ApiController;

/**
 * Class AnnouncementsController
 * @package Vanguard\Announcements\Http\Controllers\Web
 */
class AnnouncementsController extends ApiController
{
    /**
     * @var AnnouncementsRepository
     */
    private $announcements;

    /**
     * AnnouncementsController constructor.
     * @param AnnouncementsRepository $announcements
     */
    public function __construct(AnnouncementsRepository $announcements)
    {
        $this->announcements = $announcements;

        $this->middleware('permission:announcements.manage')->except('index', 'show');
    }

    /**
     * Returns a paginated list of announcements.
     *
     * @param Request $request
     * @return \Illuminate\Http\Resources\Json\AnonymousResourceCollection
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request)
    {
        $this->validate($request, ['per_page' => 'numeric|max:50']);

        $announcements = QueryBuilder::for(Announcement::class)
            ->allowedIncludes([
                AllowedInclude::relationship('user', 'creator')
            ])
            ->allowedFilters([
                AllowedFilter::partial('title'),
                AllowedFilter::partial('body'),
                AllowedFilter::exact('user', 'user_id'),
            ])
            ->allowedSorts('title', 'created_at')
            ->defaultSort('-created_at')
            ->paginate($request->per_page);

        return AnnouncementResource::collection($announcements);
    }

    /**
     * Stores the announcement inside the database.
     *
     * @param AnnouncementRequest $request
     * @return mixed
     */
    public function store(AnnouncementRequest $request)
    {
        $announcement = $this->announcements->createFor(
            auth()->user(),
            $request->title,
            $request->body
        );

        if ($request->email_notification) {
            EmailNotificationRequested::dispatch($announcement);
        }

        return new AnnouncementResource($announcement);
    }

    /**
     * Returns a single announcement resource.
     *
     * @param $announcementId
     * @return AnnouncementResource
     */
    public function show($announcementId)
    {
        $announcement = QueryBuilder::for(Announcement::where('id', $announcementId))
            ->allowedIncludes([
                AllowedInclude::relationship('user', 'creator')
            ])
            ->first();

        return new AnnouncementResource($announcement);
    }

    /**
     * Updates announcement details.
     *
     * @param Announcement $announcement
     * @param AnnouncementRequest $request
     * @return mixed
     */
    public function update(Announcement $announcement, AnnouncementRequest $request)
    {
        $announcement = $this->announcements->update(
            $announcement,
            $request->title,
            $request->body
        );

        return new AnnouncementResource($announcement);
    }

    /**
     * Removes announcement from the system.
     *
     * @param Announcement $announcement
     * @return mixed
     */
    public function destroy(Announcement $announcement)
    {
        $this->announcements->delete($announcement);

        return $this->respondWithSuccess();
    }
}
