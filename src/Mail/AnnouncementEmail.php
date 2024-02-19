<?php

namespace Vanguard\Announcements\Mail;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Vanguard\Announcements\Announcement;

class AnnouncementEmail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    /**
     * Create a new message instance.
     */
    public function __construct(public Announcement $announcement)
    {
    }

    /**
     * Build the message.
     */
    public function build(): self
    {
        $subject = sprintf('[%s] %s', __('Announcement'), $this->announcement->title);

        return $this->subject($subject)
            ->markdown('announcements::mail.notification');
    }
}
