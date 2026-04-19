<?php

declare(strict_types=1);

namespace App\Models;

use App\Actions\Media\SyncMediaUsageAction;
use Awcodes\Curator\Components\Forms\RichEditor\AttachCuratorMediaPlugin;
use Database\Factories\PostFactory;
use Filament\Forms\Components\RichEditor\Models\Concerns\InteractsWithRichContent;
use Filament\Forms\Components\RichEditor\Models\Contracts\HasRichContent;
use Illuminate\Database\Eloquent\Concerns\HasUuids;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

// @codeCoverageIgnoreStart
final class Post extends Model implements HasRichContent
{
    /** @use HasFactory<PostFactory> */
    use HasFactory;

    use HasUuids;
    use InteractsWithRichContent;

    /**
     * Indicates if the IDs are auto-incrementing.
     *
     * @var bool
     */
    public $incrementing = false;

    /**
     * The primary key type.
     *
     * @var string
     */
    protected $keyType = 'string';

    /**
     * The attributes that are guarded from mass assignment.
     *
     * @var list<string>
     */
    protected $guarded = ['id'];

    /**
     * Get the author of the post.
     *
     * @return BelongsTo<User, $this>
     */
    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    /**
     * @return BelongsTo<CuratorMedia, $this>
     */
    public function thumbnailCurator(): BelongsTo
    {
        return $this->belongsTo(CuratorMedia::class, 'thumbnail_curator_id');
    }

    protected static function booted(): void
    {
        self::creating(function (Post $post): void {
            if (! $post->author_id && auth()->check()) {
                $post->author_id = (string) auth()->id();
            }
        });

        self::saved(function (Post $post): void {
            if ($post->isDirty('thumbnail_curator_id')) {
                resolve(SyncMediaUsageAction::class)->handle(
                    $post,
                    'thumbnail_curator_id',
                    $post->thumbnail_curator_id,
                );
            }
        });

        self::deleted(function (Post $post): void {
            resolve(SyncMediaUsageAction::class)->handle(
                $post,
                'thumbnail_curator_id',
                null,
            );
        });
    }

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'published_at' => 'datetime',
        ];
    }

    protected function setUpRichContent(): void
    {
        $this->registerRichContent('content')
            ->fileAttachmentsDisk(config()->string('curator.default_disk'))
            ->fileAttachmentsVisibility(config()->string('curator.default_visibility'))
            ->plugins([
                AttachCuratorMediaPlugin::make(),
            ]);
    }
}
// @codeCoverageIgnoreEnd

