<?php

declare(strict_types=1);

namespace App\Livewire\Posts;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Illuminate\Http\Response;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.posts')]
final class Show extends Component
{
    public Post $post;

    public function mount(Post $post): void
    {
        abort_if(! $post->is_published, Response::HTTP_NOT_FOUND);

        $this->post = $post->loadMissing(['author', 'thumbnailCurator']);
    }

    public function render(): View
    {
        return view('livewire.posts.show')
            ->title($this->post->title);
    }
}
