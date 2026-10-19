<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="current-user-id" content="{{ auth()->id() }}">

    <title>{{ config('app.name', 'Mangie') }}</title>

    <link rel="icon" href="{{ asset('favicon.ico') }}" sizes="any">
    <link rel="icon" href="{{ asset('favicon.svg') }}" type="image/svg+xml">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap"
        rel="stylesheet"
    >

    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded:opsz,wght,FILL,GRAD@20..48,400,0,0"
        rel="stylesheet"
    >

    @vite(['resources/css/app.css', 'resources/js/app.js'])

    <link
        rel="stylesheet"
        href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0-beta3/css/all.min.css"
    >
</head>

<body
    class="min-h-screen bg-gray-50 font-sans text-gray-900 antialiased
           dark:bg-gray-950 dark:text-gray-100"
>
    @php
        $usesOverlayNavigation = request()->routeIs('boards.show');
    @endphp

    <div
        class="flex min-h-screen"
        x-data="{
            boardNavOpen: false,
            boardNavTimer: null,
            openBoardNav() {
                window.clearTimeout(this.boardNavTimer);
                this.boardNavOpen = true;
            },
            closeBoardNav() {
                window.clearTimeout(this.boardNavTimer);
                this.boardNavOpen = false;
            },
            scheduleBoardNavClose() {
                window.clearTimeout(this.boardNavTimer);
                this.boardNavTimer = window.setTimeout(() => {
                    this.boardNavOpen = false;
                }, 1600);
            },
        }"
        @keydown.escape.window="closeBoardNav()"
    >

        <button type="button" @click.stop="openBoardNav()" @if($usesOverlayNavigation) @mouseenter="openBoardNav()" @mouseleave="scheduleBoardNavClose()" @endif class="fixed left-4 top-4 z-[95] flex h-11 w-11 items-center justify-center rounded-xl border border-gray-200 bg-white/95 text-gray-700 shadow-lg backdrop-blur dark:border-gray-700 dark:bg-gray-900/95 dark:text-gray-200 {{ $usesOverlayNavigation ? '' : 'lg:hidden' }}" aria-label="Open navigation" aria-controls="workspace-navigation" :aria-expanded="boardNavOpen">
            <span class="material-symbols-rounded text-[23px]">menu_open</span>
        </button>

        <aside
            id="workspace-navigation"
            @unless($usesOverlayNavigation)
                data-mobile-navigation
                :data-mobile-nav-open="boardNavOpen"
                :class="boardNavOpen ? 'flex' : 'hidden'"
                @click.outside="closeBoardNav()"
            @endunless
            @if ($usesOverlayNavigation)
                x-cloak
                x-show="boardNavOpen"
                x-transition:enter="transition ease-out duration-200"
                x-transition:enter-start="-translate-x-full opacity-0"
                x-transition:enter-end="translate-x-0 opacity-100"
                x-transition:leave="transition ease-in duration-200"
                x-transition:leave-start="translate-x-0 opacity-100"
                x-transition:leave-end="-translate-x-full opacity-0"
                @mouseenter="openBoardNav()"
                @mouseleave="scheduleBoardNavClose()"
            @endif
            @class([
                'flex h-screen shrink-0 flex-col border-r border-gray-200/80 bg-white/95 px-3 py-5 shadow-xl backdrop-blur dark:border-gray-800 dark:bg-gray-900/95',
                'fixed inset-y-0 left-0 z-[100] w-64' => $usesOverlayNavigation,
                'fixed inset-y-0 left-0 z-[100] w-64 lg:sticky lg:top-0 lg:z-40 lg:flex lg:shadow-sm' => !$usesOverlayNavigation,
            ])
        >

            <div class="mb-8 flex items-center gap-2">
                <a
                    href="/"
                    @class([
                        'flex h-11 min-w-0 flex-1 items-center gap-3 rounded-xl lg:justify-start lg:px-3',
                        'justify-start px-3',
                    ])
                >
                    <span
                        class="flex h-9 w-9 shrink-0 items-center justify-center
                            rounded-lg bg-indigo-600 text-white shadow-sm"
                    >
                        <span
                            class="material-symbols-rounded text-[22px]"
                            aria-hidden="true"
                        >
                            view_kanban
                        </span>
                    </span>

                    <span
                        @class([
                            'text-xl font-bold tracking-tight text-gray-900 dark:text-white',
                            'block',
                        ])
                    >
                        Mangie
                    </span>
                </a>

                <div class="{{ $usesOverlayNavigation ? '' : 'lg:hidden' }}">
                    <button
                        type="button"
                        @click="closeBoardNav()"
                        class="flex h-9 w-9 shrink-0 items-center justify-center
                            rounded-lg text-gray-400 transition hover:bg-gray-100
                            hover:text-gray-700 dark:hover:bg-gray-800
                            dark:hover:text-white"
                        aria-label="Close navigation"
                    >
                        <span class="material-symbols-rounded text-[21px]">
                            close
                        </span>
                    </button>
                </div>
            </div>

            <nav class="flex flex-1 flex-col gap-2">
                <x-side-bar-link
                    name="Home"
                    :link="route('home')"
                    icon="home"
                    :active="request()->routeIs('home')"
                    :expanded="true"
                />

                <x-side-bar-link
                    name="Product Backlog"
                    :link="route('backlog.show', 'list')"
                    icon="inventory_2"
                    :active="request()->routeIs('backlog.show')"
                    :expanded="true"
                />

                <x-side-bar-link
                    name="Sprint Dashboard"
                    :link="route('dashboard')"
                    icon="view_kanban"
                    :active="request()->routeIs('dashboard')"
                    :expanded="true"
                />
            </nav>

            <div
                @class([
                    'border-t border-gray-200 pt-5 text-xs leading-5 text-gray-400 dark:border-gray-800 dark:text-gray-500',
                    'block',
                ])
            >
                <p class="font-medium text-gray-500 dark:text-gray-400">
                    Mangie
                </p>
                <p>Agile project management</p>
            </div>
        </aside>

        <div class="min-w-0 flex-1 {{ $usesOverlayNavigation ? 'w-full' : '' }}">
            <main class="min-h-screen">
                {{ $slot }}
            </main>
        </div>

    </div>

    <div
        id="task-modal"
        class="fixed inset-0 z-[150] hidden overflow-y-auto"
        role="dialog"
        aria-modal="true"
        aria-label="Task details"
    >
        <div
            data-task-modal-backdrop
            class="fixed inset-0 bg-gray-950/55 backdrop-blur-[2px]"
        ></div>
        <div
            data-task-modal-shell
            class="relative flex min-h-full items-start justify-center p-4
                   sm:items-center sm:p-6"
        >
            <div data-task-modal-content class="contents"></div>
        </div>
    </div>

    @stack('scripts')

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"
        charset="utf-8"
    ></script>
</body>
</html>
