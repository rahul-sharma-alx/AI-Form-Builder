<!DOCTYPE html>
<html lang="en" x-data="{ dark: false }" x-init="dark = localStorage.theme === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches); $watch('dark', v => { document.documentElement.classList.toggle('dark', v); localStorage.theme = v ? 'dark' : 'light'; })" :class="dark ? 'dark' : ''">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>@yield('title', 'AI Form Builder') — AI Form Builder</title>

    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@300;400;500;600;700;800&display=swap" rel="stylesheet">

    @vite(['resources/css/app.css','resources/js/app.js'])

    @livewireStyles
</head>
<body class="min-h-screen">

<div class="flex min-h-screen" x-data="{ mobileOpen: false }" @keydown.escape.window="mobileOpen = false">

    {{-- Mobile backdrop --}}
    <div x-show="mobileOpen" x-cloak x-transition.opacity @click="mobileOpen = false" class="fixed inset-0 z-30 bg-black/40 md:hidden"></div>

    {{-- Mobile drawer --}}
    <aside
        x-show="mobileOpen"
        x-cloak
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="-translate-x-full"
        x-transition:enter-end="translate-x-0"
        x-transition:leave="transition ease-in duration-200"
        x-transition:leave-start="translate-x-0"
        x-transition:leave-end="-translate-x-full"
        class="fixed inset-y-0 left-0 z-40 flex w-64 flex-col border-r border-border bg-card md:hidden"
    >
        <div class="flex h-14 items-center gap-2 border-b border-border px-5">
            <span class="grid h-7 w-7 place-items-center rounded-md bg-primary text-sm font-bold text-primary-foreground">A</span>
            <span class="text-sm font-semibold tracking-tight">AI Form Builder</span>
        </div>
        <x-sidebar />
    </aside>

    {{-- Desktop sidebar --}}
    <aside class="hidden w-60 shrink-0 border-r border-border bg-card md:flex md:fixed md:inset-y-0 md:flex-col">
        <div class="flex h-14 items-center gap-2 border-b border-border px-5">
            <span class="grid h-7 w-7 place-items-center rounded-md bg-primary text-sm font-bold text-primary-foreground">A</span>
            <span class="text-sm font-semibold tracking-tight">AI Form Builder</span>
        </div>
        <x-sidebar />
    </aside>

    <div class="flex min-w-0 flex-1 flex-col md:pl-60">
        {{-- Topbar --}}
        <header class="sticky top-0 z-20 flex h-14 items-center gap-3 border-b border-border bg-card/90 px-4 backdrop-blur sm:px-6">
            <button type="button" class="btn btn-ghost btn-icon md:hidden" @click="mobileOpen = true" aria-label="Open menu">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M3.75 6.75h16.5M3.75 12h16.5m-16.5 5.25h16.5"/></svg>
            </button>

            <span class="truncate text-sm font-semibold md:hidden">@yield('title', 'Dashboard')</span>
            <span class="hidden text-sm text-muted-foreground md:block">@yield('title', 'Dashboard')</span>

            <div class="ml-auto flex items-center gap-2">
                <button type="button"
                        class="btn btn-ghost btn-icon"
                        @click="dark = !dark"
                        :aria-label="dark ? 'Switch to light mode' : 'Switch to dark mode'">
                    <svg x-show="!dark" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M21.752 15.002A9.72 9.72 0 0 1 18 15.75c-5.385 0-9.75-4.365-9.75-9.75 0-1.33.266-2.597.748-3.752A9.753 9.753 0 0 0 3 11.25C3 16.635 7.365 21 12.75 21a9.753 9.753 0 0 0 9.002-5.998Z"/></svg>
                    <svg x-show="dark" x-cloak xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="h-5 w-5"><path stroke-linecap="round" stroke-linejoin="round" d="M12 3v2.25m6.364.386-1.591 1.591M21 12h-2.25m-.386 6.364-1.591-1.591M12 18.75V21m-4.773-4.227-1.591 1.591M5.25 12H3m4.227-4.773L5.636 5.636M15.75 12a3.75 3.75 0 1 1-7.5 0 3.75 3.75 0 0 1 7.5 0Z"/></svg>
                </button>
            </div>
        </header>

        <main class="flex-1">
            @if (session('flash'))
                <div class="px-4 pt-4 sm:px-6">
                    <div class="rounded-md border border-border bg-card px-4 py-3 text-sm shadow-sm" role="alert">
                        {{ session('flash') }}
                    </div>
                </div>
            @endif

            <div class="mx-auto w-full max-w-6xl px-4 py-6 sm:px-6 lg:px-8">
                {{ $slot }}
            </div>
        </main>
    </div>
</div>

@livewireScripts

</body>
</html>
