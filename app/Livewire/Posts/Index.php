<?php

declare(strict_types=1);

namespace App\Livewire\Posts;

use App\Models\Post;
use Illuminate\Contracts\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.posts')]
#[Title('Posts')]
final class Index extends Component
{
    use WithPagination;

    public function render(): View
    {
        return view('livewire.posts.index', [
            'posts' => Post::query()
                ->with(['author', 'thumbnailCurator'])
                ->where('is_published', true)
                ->latest()
                ->paginate(9),
        ]);
    }
}
