<article class="mx-auto max-w-4xl space-y-8">
    <div class="space-y-4">
        <a
            href="{{ route('posts.index') }}"
            class="inline-flex items-center gap-2 text-sm font-semibold text-zinc-600 transition hover:text-orange-700 dark:text-zinc-300 dark:hover:text-orange-300"
        >
            <span aria-hidden="true">&larr;</span>
            Kembali ke daftar post
        </a>

        <div class="space-y-3">
            <h1 class="font-['Source_Serif_4'] text-4xl font-semibold leading-tight text-zinc-950 dark:text-zinc-50 sm:text-5xl">
                {{ $post->title }}
            </h1>

            <div class="flex flex-wrap items-center gap-x-4 gap-y-1 text-sm text-zinc-500 dark:text-zinc-400">
                <span>{{ $post->author?->name ?? 'Unknown Author' }}</span>
                <span aria-hidden="true">•</span>
                <time datetime="{{ $post->created_at?->toDateString() }}">
                    {{ $post->created_at?->format('d F Y') }}
                </time>
            </div>
        </div>
    </div>

    @if ($post->thumbnailCurator?->url)
        <figure class="overflow-hidden rounded-3xl border border-zinc-900/10 bg-zinc-100 dark:border-white/10 dark:bg-zinc-900">
            <img
                src="{{ $post->thumbnailCurator->url }}"
                alt="{{ $post->title }}"
                class="h-auto w-full object-cover"
            >
        </figure>
    @endif

    <div
        class="font-['Source_Serif_4'] max-w-none text-lg leading-8 text-zinc-700 dark:text-zinc-300
            [&_h2]:mt-10 [&_h2]:font-['Instrument_Sans'] [&_h2]:text-2xl [&_h2]:font-semibold [&_h2]:text-zinc-950 dark:[&_h2]:text-zinc-100
            [&_h3]:mt-8 [&_h3]:font-['Instrument_Sans'] [&_h3]:text-xl [&_h3]:font-semibold [&_h3]:text-zinc-900 dark:[&_h3]:text-zinc-100
            [&_p]:mb-5 [&_p]:text-lg [&_p]:leading-8
            [&_ul]:mb-5 [&_ul]:list-disc [&_ul]:space-y-2 [&_ul]:pl-6
            [&_ol]:mb-5 [&_ol]:list-decimal [&_ol]:space-y-2 [&_ol]:pl-6
            [&_blockquote]:my-8 [&_blockquote]:rounded-r-xl [&_blockquote]:border-l-4 [&_blockquote]:border-orange-500 [&_blockquote]:bg-orange-100/50 [&_blockquote]:px-5 [&_blockquote]:py-4 [&_blockquote]:italic dark:[&_blockquote]:bg-orange-500/10
            [&_a]:font-semibold [&_a]:text-orange-700 [&_a]:underline [&_a]:decoration-orange-300 [&_a]:underline-offset-4 hover:[&_a]:text-orange-600 dark:[&_a]:text-orange-300 dark:[&_a]:decoration-orange-500"
    >
        {!! $post->renderRichContent('content') !!}
    </div>
</article>

