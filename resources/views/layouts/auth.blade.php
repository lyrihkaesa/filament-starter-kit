<x-filament-panels::layout.base :livewire="$livewire">
    <div class="fi-simple-layout">
        <x-auth-split-layout>
            <div class="mb-10 flex flex-col items-center lg:items-start">
                <h1 class="text-3xl font-bold dark:text-white">{{ $livewire->getHeading() }}</h1>
                @if ($subheading = $livewire->getSubheading())
                    <p class="mt-2 text-gray-600 dark:text-gray-400">{!! $subheading !!}</p>
                @endif
            </div>

            {{ $slot }}
        </x-auth-split-layout>
    </div>
</x-filament-panels::layout.base>
