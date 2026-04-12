<?php

declare(strict_types=1);

namespace App\Http\Resources\Api\V1;

use App\Models\Post;
use Carbon\CarbonInterface;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;

/**
 * @mixin Post
 *
 * @property-read Post $resource
 */
final class PostResource extends JsonResource
{
    /**
     * @var array<string, bool>
     */
    private array $capabilities = [
        'view' => false,
        'update' => false,
        'delete' => false,
    ];

    /**
     * @param  array<string, bool>  $capabilities
     */
    public function withCapabilities(array $capabilities): self
    {
        $this->capabilities = $capabilities;

        return $this;
    }

    /**
     * @return array<string, mixed>
     */
    public function toArray(Request $request): array
    {
        $post = $this->resource;
        $routeKey = $post->getRouteKey();

        return [
            'id' => is_scalar($routeKey) ? (string) $routeKey : '',
            'title' => is_scalar($post->title) ? (string) $post->title : '',
            'slug' => is_scalar($post->slug) ? (string) $post->slug : '',
            'content' => is_scalar($post->content) ? (string) $post->content : '',
            'author_id' => is_scalar($post->author_id) ? (string) $post->author_id : '',
            'thumbnail_curator_id' => is_scalar($post->thumbnail_curator_id) ? (string) $post->thumbnail_curator_id : null,
            'thumbnail_url' => $post->thumbnailCurator?->url,
            'published_at' => $post->published_at instanceof CarbonInterface ? $post->published_at->toISOString() : null,
            'created_at' => $post->created_at instanceof CarbonInterface ? $post->created_at->toISOString() : null,
            'updated_at' => $post->updated_at instanceof CarbonInterface ? $post->updated_at->toISOString() : null,
            'can' => $this->capabilities,
        ];
    }
}
