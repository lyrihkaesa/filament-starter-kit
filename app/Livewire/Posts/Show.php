<?php

declare(strict_types=1);

namespace App\Livewire\Posts;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Illuminate\Support\Str;
use Laravel\Head\Enums\OgType;
use Laravel\Head\Facades\Head;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.posts')]
final class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        abort_if($post->published_at === null, Response::HTTP_NOT_FOUND);

        $this->post = $post->loadMissing(['author', 'thumbnailCurator']);

        $description = Str::limit(strip_tags($this->post->content), 160);

        Head::title($this->post->title)
            ->description($description)
            ->canonical(route('posts.show', $this->post))
            ->og(
                type: OgType::Article,
                title: $this->post->title,
                description: $description,
                url: route('posts.show', $this->post),
                image: $this->post->thumbnailCurator?->url,
            );
    }

    public function render(): View
    {
        return view('livewire.posts.show');
    }
}
