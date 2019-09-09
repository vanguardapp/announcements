<?php

namespace Vanguard\Announcements;

use Illuminate\Database\Eloquent\Model;
use Vanguard\User;

class Announcement extends Model
{
    protected $table = 'announcements';

    protected $guarded = [];

    public function creator()
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wasReadBy(User $user)
    {
        return $user->announcements_last_read_at < $this->created_at;
    }

    public function getParsedBodyAttribute()
    {
        return \Illuminate\Mail\Markdown::parse($this->attributes['body']);
    }
}
