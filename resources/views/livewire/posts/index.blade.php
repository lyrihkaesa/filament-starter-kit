<section class="space-y-8">
    <div class="space-y-4">
        <p class="font-['Instrument_Sans'] text-xs font-semibold uppercase tracking-[0.24em] text-orange-600 dark:text-orange-300">
            Public Posts
        </p>
        <h1 class="max-w-3xl font-['Source_Serif_4'] text-4xl font-semibold leading-tight text-zinc-950 dark:text-zinc-50 sm:text-5xl">
            Artikel terbaru dengan rich text dan media Curator.
        </h1>
        <p class="max-w-3xl text-sm leading-7 text-zinc-600 dark:text-zinc-300 sm:text-base">
            Halaman ini hanya menampilkan post yang sudah dipublish. Klik artikel untuk melihat detail lengkap.
        </p>
    </div>

    @if ($posts->isEmpty())
        <div class="rounded-2xl border border-dashed border-zinc-900/20 bg-white/60 p-10 text-center text-zinc-600 dark:border-white/20 dark:bg-zinc-900/40 dark:text-zinc-300">
            Belum ada post yang dipublish.
        </div>
    @else
        <div class="grid grid-cols-1 gap-6 md:grid-cols-2 xl:grid-cols-3">
            @foreach ($posts as $post)
                @php
                    $excerpt = \Illuminate\Support\Str::of(strip_tags((string) $post->content))->squish()->limit(155);
                @endphp

                <article
                    wire:key="post-{{ $post->id }}"
                    class="group overflow-hidden rounded-2xl border border-zinc-900/10 bg-white/85 shadow-sm transition duration-200 hover:-translate-y-1 hover:shadow-lg dark:border-white/10 dark:bg-zinc-900/85"
                >
                    <a href="{{ route('posts.show', ['post' => $post->slug]) }}" class="block">
                        <div class="aspect-[16/10] overflow-hidden bg-zinc-100 dark:bg-zinc-800">
                            @if ($post->thumbnailCurator?->url)
                                <img
                                    src="{{ $post->thumbnailCurator->url }}"
                                    alt="{{ $post->title }}"
                                    class="h-full w-full object-cover transition duration-500 group-hover:scale-105"
                                >
                            @else
                                <div class="grid h-full place-content-center text-xs font-medium uppercase tracking-[0.2em] text-zinc-400 dark:text-zinc-500">
                                    No Thumbnail
                                </div>
                            @endif
                        </div>

                        <div class="space-y-4 p-5">
                            <div class="flex items-center justify-between gap-2 text-xs text-zinc-500 dark:text-zinc-400">
                                <span>{{ $post->author?->name ?? 'Unknown Author' }}</span>
                                <time datetime="{{ $post->created_at?->toDateString() }}">{{ $post->created_at?->format('d M Y') }}</time>
                            </div>

                            <h2 class="font-['Source_Serif_4'] text-2xl font-semibold leading-snug text-zinc-950 transition group-hover:text-orange-700 dark:text-zinc-100 dark:group-hover:text-orange-300">
                                {{ $post->title }}
                            </h2>

                            <p class="text-sm leading-7 text-zinc-600 dark:text-zinc-300">
                                {{ $excerpt }}
                            </p>

                            <div class="inline-flex items-center text-sm font-semibold text-orange-700 dark:text-orange-300">
                                Baca detail
                            </div>
                        </div>
                    </a>
                </article>
            @endforeach
        </div>

        <div>
            {{ $posts->links() }}
        </div>
    @endif
</section>
