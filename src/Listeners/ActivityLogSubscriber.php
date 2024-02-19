<?php

namespace Vanguard\Announcements\Listeners;

use Illuminate\Events\Dispatcher;
use Illuminate\Support\Str;
use Vanguard\Announcements\Events\Created;
use Vanguard\Announcements\Events\Deleted;
use Vanguard\Announcements\Events\Updated;
use Vanguard\UserActivity\Logger;

class ActivityLogSubscriber
{
    public function __construct(private Logger $logger)
    {
    }

    public function onCreate(Created $event): void
    {
        $this->logger->log(__('announcements::log.created_announcement', [
            'id' => $event->announcement->id,
            'title' => Str::limit($event->announcement->title, 50),
        ]));
    }

    public function onUpdate(Updated $event): void
    {
        $this->logger->log(__('announcements::log.created_announcement', [
            'id' => $event->announcement->id,
        ]));
    }

    public function onDelete(Deleted $event): void
    {
        $this->logger->log(__('announcements::log.deleted_announcement', [
            'id' => $event->announcement->id,
        ]));
    }

    /**
     * Register the listeners for the subscriber.
     */
    public function subscribe(Dispatcher $events): void
    {
        $class = self::class;

        $events->listen(Created::class, "{$class}@onCreate");
        $events->listen(Updated::class, "{$class}@onUpdate");
        $events->listen(Deleted::class, "{$class}@onDelete");
    }
}
