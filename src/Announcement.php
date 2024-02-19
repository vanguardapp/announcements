<?php

namespace Vanguard\Announcements;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Carbon;
use Illuminate\Support\HtmlString;
use League\CommonMark\CommonMarkConverter;
use League\CommonMark\Environment\Environment;
use League\CommonMark\Extension\Table\TableExtension;
use Vanguard\Announcements\Database\Factories\AnnouncementFactory;
use Vanguard\User;

/**
 * @property int $id
 * @property string $title
 * @property string $body
 * @property Carbon $created_at
 * @property Carbon $deleted_at
 */
class Announcement extends Model
{
    use HasFactory;

    protected $table = 'announcements';

    protected $guarded = [];

    public function creator(): \Illuminate\Database\Eloquent\Relations\BelongsTo
    {
        return $this->belongsTo(User::class, 'user_id');
    }

    public function wasReadBy(User $user): bool
    {
        return $user->announcements_last_read_at < $this->created_at;
    }

    public function getParsedBodyAttribute(): HtmlString
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

    /**
     * {@inheritDoc}
     */
    protected static function newFactory(): AnnouncementFactory
    {
        return new AnnouncementFactory;
    }
}
