<x-filament-widgets::widget>
    <x-filament::section>
        <div class="flex flex-row justify-between">
            <div class="flex flex-col space-y-1">
                <div class="text-lg font-medium text-gray-900 dark:text-gray-100">Filament Starter Kit</div>
                <div class="text-xs text-gray-500">Laravel v{{ $laravelVersion }}</div>
            </div>
            <div class="flex flex-col space-y-1 text-right">
                <a class="text-gray-800 hover:underline dark:text-gray-200"
                    href="https://lyrihkaesa.github.io/filament-starter-kit" target="_blank">
                    📚 {{ __('Documentation') }}
                </a>
                <a class="text-gray-800 hover:underline dark:text-gray-200"
                    href="https://github.com/lyrihkaesa/filament-starter-kit/" target="_blank">
                    🐙 Github
                </a>
            </div>
        </div>
    </x-filament::section>
</x-filament-widgets::widget>
