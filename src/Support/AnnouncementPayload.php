<?php

namespace Vanguard\Announcements\Support;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;
use Illuminate\Support\Str;
use Vanguard\Announcements\Announcement;

class AnnouncementPayload
{
    /**
     * A card, as rendered on the list and detail pages.
     *
     * @return array<string, mixed>
     */
    public static function card(Announcement $announcement): array
    {
        return [
            'id' => $announcement->id,
            'title' => $announcement->title,
            // Markdown is rendered to HTML by the model accessor, as it was for
            // the Blade card.
            'parsedBody' => (string) $announcement->parsed_body,
            'creator' => $announcement->creator->present()->name,
            'avatar' => $announcement->creator->present()->avatar,
            'shortDate' => $announcement->created_at->format('M d'),
            'editUrl' => route('announcements.edit', $announcement),
        ];
    }

    /**
     * A row of the management table.
     *
     * @return array<string, mixed>
     */
    public static function row(Announcement $announcement): array
    {
        return [
            'id' => $announcement->id,
            'title' => Str::limit($announcement->title, 50),
            'creator' => $announcement->creator->username ?: __('N/A'),
            'creatorAvatar' => $announcement->creator->present()->avatar,
            'creatorUrl' => self::creatorUrl($announcement),
            'createdAt' => $announcement->created_at->format(config('app.date_format')),
            'showUrl' => route('announcements.show', $announcement),
            'editUrl' => route('announcements.edit', $announcement),
            'deleteUrl' => route('announcements.destroy', $announcement),
        ];
    }

    /**
     * The creator's profile, or null when the viewer may not open it.
     *
     * This table is reached with announcements.manage, which says nothing about
     * users.manage, and that is what guards every route under users/{user}. A
     * moderator without it would otherwise be offered a link to a 403.
     */
    private static function creatorUrl(Announcement $announcement): ?string
    {
        return auth()->user()?->hasPermission('users.manage')
            ? route('users.show', $announcement->creator)
            : null;
    }

    public static function rows(LengthAwarePaginator $announcements): LengthAwarePaginator
    {
        return $announcements->through(fn (Announcement $a) => self::row($a));
    }

    public static function cards(LengthAwarePaginator $announcements): LengthAwarePaginator
    {
        return $announcements->through(fn (Announcement $a) => self::card($a));
    }
}
