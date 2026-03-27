@props([
    'image' => asset('images/background-login-register.png'),
])

<div class="flex min-h-screen">
    <!-- Left: Image Section -->
    <div class="hidden w-1/2 bg-cover bg-center lg:block" style="background-image: url('{{ $image }}')">
        <div class="flex h-full w-full items-center justify-center bg-black/20 backdrop-blur-xs">
            <div class="px-12 text-white">
                <h2 class="text-4xl font-bold tracking-tight">Welcome to {{ config('app.name') }}</h2>
                <p class="mt-4 text-lg">Experience the next generation of Filament Starter Kit with Tailwind CSS v4.</p>
            </div>
        </div>
    </div>

    <!-- Right: Form Section -->
    <div class="flex w-full flex-col justify-center bg-white px-6 py-12 dark:bg-gray-950 lg:w-1/2 lg:px-12">
        <div class="mx-auto w-full max-w-md">
            <!-- Logo for mobile -->
            <div class="mb-10 flex justify-center lg:hidden">
                <x-filament-panels::logo />
            </div>

            {{ $slot }}
        </div>
    </div>
</div>
