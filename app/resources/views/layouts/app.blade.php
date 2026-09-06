<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'Mangie') }}</title>

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
    <div class="flex min-h-screen">

        <aside
            class="sticky top-0 z-40 flex h-screen w-20 shrink-0 flex-col
                   border-r border-gray-200/80 bg-white/95 px-3 py-5
                   shadow-sm backdrop-blur
                   lg:w-64
                   dark:border-gray-800 dark:bg-gray-900/95"
        >

            <a
                href="/"
                class="mb-8 flex h-11 items-center justify-center gap-3
                       rounded-xl lg:justify-start lg:px-3"
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
                    class="hidden text-xl font-bold tracking-tight
                           text-gray-900 lg:block dark:text-white"
                >
                    Mangie
                </span>
            </a>

            <nav class="flex flex-1 flex-col gap-2">
                <x-side-bar-link
                    name="Home"
                    :link="route('home')"
                    icon="home"
                    :active="request()->routeIs('home')"
                />

                <x-side-bar-link
                    name="Product Backlog"
                    :link="route('backlog.show', 'list')"
                    icon="inventory_2"
                    :active="request()->routeIs('backlog.show')"
                />

                <x-side-bar-link
                    name="Sprint Dashboard"
                    :link="route('dashboard')"
                    icon="view_kanban"
                    :active="request()->routeIs('dashboard')"
                />
            </nav>

            <div
                class="hidden border-t border-gray-200 pt-5
                       text-xs leading-5 text-gray-400
                       lg:block dark:border-gray-800 dark:text-gray-500"
            >
                <p class="font-medium text-gray-500 dark:text-gray-400">
                    Mangie
                </p>
                <p>Agile project management</p>
            </div>
        </aside>

        <div class="min-w-0 flex-1">
            <main class="min-h-screen">
                {{ $slot }}
            </main>
        </div>

    </div>

    @stack('scripts')

    <script
        src="https://cdnjs.cloudflare.com/ajax/libs/Chart.js/2.7.1/Chart.min.js"
        charset="utf-8"
    ></script>
</body>
</html>