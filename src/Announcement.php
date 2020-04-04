<?php

namespace Vanguard\Announcements;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\HtmlString;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment;
use League\CommonMark\Extension\Table\TableExtension;
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
        $environment = Environment::createCommonMarkEnvironment();

        $environment->addExtension(new TableExtension);

        $converter = new CommonMarkConverter([
            'html_input' => 'escape',
            'allow_unsafe_links' => false,
        ], $environment);

        return new HtmlString(
            $converter->convertToHtml($this->attributes['body'])
        );
    }
}
