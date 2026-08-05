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
            'creatorUrl' => route('users.show', $announcement->creator),
            'createdAt' => $announcement->created_at->format(config('app.date_format')),
            'showUrl' => route('announcements.show', $announcement),
            'editUrl' => route('announcements.edit', $announcement),
            'deleteUrl' => route('announcements.destroy', $announcement),
        ];
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
