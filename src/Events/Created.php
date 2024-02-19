<?php

namespace Vanguard\Announcements\Events;

use Illuminate\Foundation\Events\Dispatchable;
use Vanguard\Announcements\Announcement;

class Created
{
    use Dispatchable;

    public function __construct(public Announcement $announcement, public $sendEmailNotification = false)
    {
    }
}
