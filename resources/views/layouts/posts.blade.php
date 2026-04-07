<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>{{ $title ?? config('app.name') }}</title>

        <script>
            (() => {
                const THEME_KEY = 'theme';
                const DEFAULT_THEME_MODE = 'system';
                const mediaQuery = window.matchMedia('(prefers-color-scheme: dark)');
                const validThemeModes = new Set(['light', 'dark', 'system']);

                const normalizeThemeMode = (themeMode) => validThemeModes.has(themeMode) ? themeMode : DEFAULT_THEME_MODE;

                const readThemeMode = () => normalizeThemeMode(localStorage.getItem(THEME_KEY) ?? DEFAULT_THEME_MODE);

                const resolveTheme = (themeMode) => {
                    const normalizedThemeMode = normalizeThemeMode(themeMode);

                    if (normalizedThemeMode === 'system') {
                        return mediaQuery.matches ? 'dark' : 'light';
                    }

                    return normalizedThemeMode;
                };

                const syncThemeButtons = (themeMode) => {
                    document.querySelectorAll('[data-theme-mode]').forEach((button) => {
                        const isActive = button.dataset.themeMode === themeMode;

                        button.dataset.active = isActive ? 'true' : 'false';
                        button.setAttribute('aria-pressed', isActive ? 'true' : 'false');
                    });
                };

                const applyTheme = (themeMode) => {
                    const normalizedThemeMode = normalizeThemeMode(themeMode);
                    const resolvedTheme = resolveTheme(normalizedThemeMode);

                    document.documentElement.classList.toggle('dark', resolvedTheme === 'dark');
                    document.documentElement.style.colorScheme = resolvedTheme;
                    document.documentElement.dataset.themeMode = normalizedThemeMode;

                    syncThemeButtons(normalizedThemeMode);
                };

                const persistThemeMode = (themeMode) => {
                    const normalizedThemeMode = normalizeThemeMode(themeMode);

                    localStorage.setItem(THEME_KEY, normalizedThemeMode);
                    applyTheme(normalizedThemeMode);
                };

                window.__setTheme = persistThemeMode;

                window.addEventListener('theme-changed', (event) => {
                    const themeMode = typeof event.detail === 'string'
                        ? event.detail
                        : (typeof event.detail?.theme === 'string' ? event.detail.theme : readThemeMode());

                    persistThemeMode(themeMode);
                });

                window.addEventListener('storage', (event) => {
                    if (event.key === THEME_KEY) {
                        applyTheme(readThemeMode());
                    }
                });

                mediaQuery.addEventListener('change', () => {
                    if (readThemeMode() === 'system') {
                        applyTheme('system');
                    }
                });

                applyTheme(readThemeMode());
                window.addEventListener('DOMContentLoaded', () => applyTheme(readThemeMode()));
            })();
        </script>

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=instrument-sans:400,500,600,700|source-serif-4:400,600,700" rel="stylesheet" />

        @livewireStyles

        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="min-h-screen bg-zinc-50 text-zinc-900 antialiased transition-colors duration-300 dark:bg-zinc-950 dark:text-zinc-100">
        <div class="relative isolate">
            <div class="pointer-events-none absolute inset-0 -z-10 overflow-hidden">
                <div class="absolute -top-32 left-1/2 h-80 w-80 -translate-x-1/2 rounded-full bg-orange-200/55 blur-3xl dark:bg-orange-500/20"></div>
                <div class="absolute bottom-0 right-0 h-72 w-72 rounded-full bg-cyan-200/40 blur-3xl dark:bg-cyan-500/15"></div>
            </div>

            <header class="border-b border-zinc-900/10 bg-white/70 backdrop-blur-sm dark:border-white/10 dark:bg-zinc-950/70">
                <div class="mx-auto flex w-full max-w-6xl items-center justify-between gap-4 px-4 py-4 sm:px-6 lg:px-8">
                    <a href="{{ route('posts.index') }}" class="inline-flex items-center gap-2">
                        <span class="inline-flex size-8 items-center justify-center rounded-full bg-orange-500 text-xs font-bold tracking-wide text-white">PS</span>
                        <span class="font-['Instrument_Sans'] text-sm font-semibold uppercase tracking-[0.18em] text-zinc-900 dark:text-zinc-100">
                            Post Stream
                        </span>
                    </a>

                    <div class="inline-flex items-center rounded-full border border-zinc-900/10 bg-white/80 p-1 text-xs dark:border-white/10 dark:bg-zinc-900/80">
                        <button
                            type="button"
                            data-theme-mode="light"
                            data-active="false"
                            aria-pressed="false"
                            onclick="window.__setTheme('light')"
                            class="rounded-full px-3 py-1 font-medium text-zinc-600 transition data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:text-zinc-300 dark:data-[active=true]:bg-zinc-100 dark:data-[active=true]:text-zinc-900"
                        >
                            Light
                        </button>
                        <button
                            type="button"
                            data-theme-mode="dark"
                            data-active="false"
                            aria-pressed="false"
                            onclick="window.__setTheme('dark')"
                            class="rounded-full px-3 py-1 font-medium text-zinc-600 transition data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:text-zinc-300 dark:data-[active=true]:bg-zinc-100 dark:data-[active=true]:text-zinc-900"
                        >
                            Dark
                        </button>
                        <button
                            type="button"
                            data-theme-mode="system"
                            data-active="false"
                            aria-pressed="false"
                            onclick="window.__setTheme('system')"
                            class="rounded-full px-3 py-1 font-medium text-zinc-600 transition data-[active=true]:bg-zinc-900 data-[active=true]:text-white dark:text-zinc-300 dark:data-[active=true]:bg-zinc-100 dark:data-[active=true]:text-zinc-900"
                        >
                            System
                        </button>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-6xl px-4 py-10 sm:px-6 lg:px-8">
                {{ $slot }}
            </main>
        </div>

        @livewireScripts
    </body>
</html>
