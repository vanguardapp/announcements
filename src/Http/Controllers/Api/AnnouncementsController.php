<?php

namespace Vanguard\Announcements\Http\Controllers\Api;

use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\AnonymousResourceCollection;
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
 */
class AnnouncementsController extends ApiController
{
    public function __construct(private readonly AnnouncementsRepository $announcements)
    {
        $this->middleware('permission:announcements.manage')->except('index', 'show');
    }

    /**
     * Returns a paginated list of announcements.
     *
     * @throws \Illuminate\Validation\ValidationException
     */
    public function index(Request $request): AnonymousResourceCollection
    {
        $this->validate($request, ['per_page' => 'numeric|max:50']);

        $announcements = QueryBuilder::for(Announcement::class)
            ->allowedIncludes([
                AllowedInclude::relationship('user', 'creator'),
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
     */
    public function store(AnnouncementRequest $request): AnnouncementResource
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
     */
    public function show($announcementId): AnnouncementResource
    {
        $announcement = QueryBuilder::for(Announcement::where('id', $announcementId))
            ->allowedIncludes([
                AllowedInclude::relationship('user', 'creator'),
            ])
            ->first();

        return new AnnouncementResource($announcement);
    }

    /**
     * Updates announcement details.
     */
    public function update(Announcement $announcement, AnnouncementRequest $request): AnnouncementResource
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
     */
    public function destroy(Announcement $announcement): JsonResponse
    {
        $this->announcements->delete($announcement);

        return $this->respondWithSuccess();
    }
}
