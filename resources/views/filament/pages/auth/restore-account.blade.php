<x-filament-panels::layout.base>
    <div class="fi-simple-layout">
        <x-auth-split-layout>
            <div class="mb-10 flex flex-col items-center lg:items-start">
                <h1 class="text-3xl font-bold dark:text-white">{{ $this->getHeading() }}</h1>
                <p class="mt-2 text-gray-600 dark:text-gray-400">Harap tunggu sementara kami memulihkan akun Anda.</p>
            </div>
        </x-auth-split-layout>
    </div>
</x-filament-panels::layout.base>
