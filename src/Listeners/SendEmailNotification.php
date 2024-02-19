<?php

namespace Vanguard\Announcements\Listeners;

use Mail;
use Vanguard\Announcements\Announcement;
use Vanguard\Announcements\Events\EmailNotificationRequested;
use Vanguard\Announcements\Mail\AnnouncementEmail;
use Vanguard\User;

class SendEmailNotification
{
    /**
     * Handle the event.
     */
    public function handle(EmailNotificationRequested $event): void
    {
        User::chunk(200, function ($users) use ($event) {
            foreach ($users as $user) {
                $this->sendEmailTo($user, $event->announcement);
            }
        });
    }

    private function sendEmailTo(User $user, Announcement $announcement): void
    {
        Mail::to($user)->send(new AnnouncementEmail($announcement));
    }
}
