<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
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
</head>

<body
    class="font-sans text-gray-900 antialiased
           dark:text-gray-100"
>
    <main
        class="relative flex min-h-screen items-center justify-center
               overflow-hidden bg-gradient-to-br
               from-gray-50 via-indigo-50 to-blue-100
               px-6 py-12
               dark:from-gray-950 dark:via-gray-950 dark:to-indigo-950"
    >
        <div
            class="absolute -left-40 top-16 h-96 w-96
                   rounded-full bg-indigo-300/30 blur-3xl
                   dark:bg-indigo-700/10"
        ></div>

        <div
            class="absolute -right-40 bottom-0 h-96 w-96
                   rounded-full bg-blue-300/30 blur-3xl
                   dark:bg-blue-700/10"
        ></div>


        <div class="relative w-full max-w-md">

            <a
                href="/"
                class="mb-8 flex items-center justify-center gap-3"
            >
                <span
                    class="flex h-11 w-11 items-center justify-center
                           rounded-xl bg-indigo-600 text-white shadow-md"
                >
                    <span
                        class="material-symbols-rounded text-[26px]"
                    >
                        view_kanban
                    </span>
                </span>

                <span
                    class="text-2xl font-bold tracking-tight
                           text-gray-900 dark:text-white"
                >
                    Mangie
                </span>
            </a>


            <div
                class="rounded-3xl border border-white/70
                       bg-white/95 p-7 shadow-xl backdrop-blur
                       sm:p-9
                       dark:border-gray-800
                       dark:bg-gray-900/95"
            >
                {{ $slot }}
            </div>

            <p
                class="mt-6 text-center text-sm
                       text-gray-500 dark:text-gray-400"
            >
                Agile project management, without the clutter.
            </p>

        </div>
    </main>
</body>
</html>