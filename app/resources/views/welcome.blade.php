<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Mangie — Agile Project Management</title>

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link
        href="https://fonts.bunny.net/css?family=figtree:400,500,600,700,800&display=swap"
        rel="stylesheet"
    />
    <link
        href="https://fonts.googleapis.com/css2?family=Material+Symbols+Rounded&icon_names=account_circle,arrow_forward,calendar_month,check_circle,groups,inventory_2,monitoring,more_horiz,task_alt,view_column,view_kanban&display=block"
        rel="stylesheet"
    />

    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>

<body class="font-sans antialiased bg-white text-gray-900 dark:bg-gray-900 dark:text-gray-100">

    <header
        class="sticky top-0 z-50 border-b border-gray-200/80
               bg-white/90 backdrop-blur-md
               dark:border-gray-700 dark:bg-gray-900/90"
    >
        <div class="mx-auto flex h-16 max-w-7xl items-center justify-between px-6 lg:px-8">

            <a href="{{ url('/') }}" class="flex items-center gap-3">
                <div
                class="flex h-9 w-9 items-center justify-center rounded-lg
                bg-indigo-600 text-white shadow-sm">
                    <span
                    class="material-symbols-rounded text-[22px] leading-none"
                    aria-hidden="true">
                    view_kanban
                    </span>
                </div>

                <span class="text-xl font-bold tracking-tight text-gray-900 dark:text-white">
                    Mangie
                </span>
            </a>

            @if (Route::has('login'))
                <nav class="flex items-center gap-2">
                    @auth
                        <a
                            href="{{ url('/dashboard') }}"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700
                                   transition hover:bg-gray-100 hover:text-indigo-600
                                   dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-indigo-400"
                        >
                            Dashboard
                        </a>

                        <a
                            href="{{ url('/home') }}"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700
                                   transition hover:bg-gray-100 hover:text-indigo-600
                                   dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-indigo-400"
                        >
                            Home
                        </a>
                    @else
                        <a
                            href="{{ route('login') }}"
                            class="rounded-lg px-4 py-2 text-sm font-medium text-gray-700
                                   transition hover:bg-gray-100 hover:text-indigo-600
                                   dark:text-gray-200 dark:hover:bg-gray-800 dark:hover:text-indigo-400"
                        >
                            Log in
                        </a>

                        @if (Route::has('register'))
                            <a
                                href="{{ route('register') }}"
                                class="rounded-lg bg-indigo-600 px-4 py-2 text-sm font-semibold
                                       text-white shadow-sm transition
                                       hover:bg-indigo-500"
                            >
                                Register
                            </a>
                        @endif
                    @endauth
                </nav>
            @endif
        </div>
    </header>


    <main>

        <section
            class="relative overflow-hidden
                   bg-gradient-to-br from-gray-50 via-indigo-50 to-blue-100
                   dark:from-gray-900 dark:via-gray-900 dark:to-indigo-950"
        >
            <div
                class="absolute -left-32 top-20 h-80 w-80 rounded-full
                       bg-indigo-300/30 blur-3xl
                       dark:bg-indigo-700/10"
            ></div>

            <div
                class="absolute -right-32 bottom-0 h-96 w-96 rounded-full
                       bg-blue-300/30 blur-3xl
                       dark:bg-blue-700/10"
            ></div>

            <div
                class="relative mx-auto grid max-w-7xl items-center gap-16
                       px-6 py-20
                       lg:grid-cols-2 lg:px-8 lg:py-28"
            >

                <div>
                    <div
                        class="mb-6 inline-flex items-center rounded-full
                               border border-indigo-200 bg-indigo-50
                               px-4 py-1.5 text-sm font-medium text-indigo-700
                               dark:border-indigo-800 dark:bg-indigo-950
                               dark:text-indigo-300"
                    >
                        Agile project management, without the clutter.
                    </div>

                    <h1
                        class="max-w-2xl text-5xl font-extrabold tracking-tight
                               text-gray-900
                               sm:text-6xl
                               dark:text-white"
                    >
                        Organise work.
                        <span class="text-indigo-600 dark:text-indigo-400">
                            Move projects forward.
                        </span>
                    </h1>

                    <p
                        class="mt-6 max-w-xl text-lg leading-8
                               text-gray-600 dark:text-gray-300"
                    >
                        Mangie gives teams a visual workspace for planning projects,
                        organising tasks, managing sprints and keeping track of the work
                        that still needs to get done.
                    </p>

                    <div class="mt-9 flex flex-wrap items-center gap-4">
                        @guest
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center gap-2 rounded-lg
                                           bg-indigo-600 px-6 py-3.5
                                           text-base font-semibold text-white
                                           shadow-lg shadow-indigo-600/20
                                           transition
                                           hover:-translate-y-0.5 hover:bg-indigo-500"
                                >
                                    Get started with Mangie

                                    <span
                                    class="material-symbols-rounded text-[20px] leading-none"
                                    aria-hidden="true"
                                    >
                                    arrow_forward
                                    </span>
                                </a>
                            @endif

                            <a
                                href="{{ route('login') }}"
                                class="rounded-lg border border-gray-300
                                       bg-white px-6 py-3.5
                                       text-base font-semibold text-gray-700
                                       shadow-sm transition
                                       hover:bg-gray-50
                                       dark:border-gray-700 dark:bg-gray-800
                                       dark:text-gray-200 dark:hover:bg-gray-700"
                            >
                                Log in
                            </a>
                        @else
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-flex items-center gap-2 rounded-lg
                                       bg-indigo-600 px-6 py-3.5
                                       text-base font-semibold text-white
                                       shadow-lg shadow-indigo-600/20
                                       transition hover:bg-indigo-500"
                            >
                                Open your dashboard

                                <span
                                class="material-symbols-rounded text-[20px] leading-none"
                                aria-hidden="true">
                                arrow_forward
                                </span>
                            </a>
                        @endguest
                    </div>

                    <p class="mt-5 text-sm text-gray-500 dark:text-gray-400">
                        Create an account and start organising your work in minutes.
                    </p>
                </div>

                <div class="relative">

                    <div
                        class="absolute inset-8 rounded-3xl
                               bg-indigo-500/20 blur-3xl"
                    ></div>

                    <div
                        class="relative overflow-hidden rounded-2xl
                               border border-white/60
                               bg-white shadow-2xl
                               dark:border-gray-700 dark:bg-gray-800"
                    >

                        <div
                            class="flex items-center justify-between
                                   border-b border-gray-200
                                   bg-gray-50 px-5 py-4
                                   dark:border-gray-700 dark:bg-gray-800"
                        >
                            <div class="flex items-center gap-2">
                                <span class="h-3 w-3 rounded-full bg-red-400"></span>
                                <span class="h-3 w-3 rounded-full bg-yellow-400"></span>
                                <span class="h-3 w-3 rounded-full bg-green-400"></span>
                            </div>
<!-- Next time Imma just take a screenshot instead. This dogshit was genuenly infuriating to make -->
                            <span class="text-sm font-semibold text-gray-600 dark:text-gray-300">
                                Product Sprint
                            </span>

                            <div class="flex -space-x-2">
                                <span
                                class="flex h-7 w-7 items-center justify-center
                                rounded-full border-2 border-white
                                bg-indigo-500 text-white
                                dark:border-gray-800"
                                >
                                    <span class="material-symbols-rounded text-[18px] leading-none">
                                        account_circle
                                    </span>
                                </span>

                                <span
                                    class="flex h-7 w-7 items-center justify-center
                                    rounded-full border-2 border-white
                                    bg-blue-500 text-white
                                    dark:border-gray-800"
                                >
                                    <span class="material-symbols-rounded text-[18px] leading-none">
                                        account_circle
                                    </span>
                                </span>
                            </div>
                        </div>


                        <div
                            class="grid gap-3 bg-gradient-to-br
                                   from-indigo-500 to-blue-600 p-5
                                   sm:grid-cols-3"
                        >

                            <div class="rounded-xl bg-gray-100 p-3 shadow-md dark:bg-gray-700">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-100">
                                        TO DO
                                    </h3>

                                    <span
                                        class="material-symbols-rounded text-[19px] leading-none text-gray-400"
                                        aria-hidden="true"
                                    >
                                        more_horiz
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                        <div class="mb-2 h-2 w-12 rounded bg-red-400"></div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-100">
                                            Design dashboard
                                        </p>

                                        <div class="mt-3 flex items-center justify-between">
                                            <span
                                                class="rounded bg-gray-100 px-2 py-1
                                                       text-xs text-gray-500
                                                       dark:bg-gray-700 dark:text-gray-300"
                                            >
                                                5 pts
                                            </span>
                                            <span
                                                class="h-6 w-6 rounded-full bg-indigo-400"
                                            ></span>
                                        </div>
                                    </div>

                                    <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                        <div class="mb-2 h-2 w-16 rounded bg-blue-400"></div>
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-100">
                                            Update backlog
                                        </p>
                                    </div>
                                </div>
                            </div>


                            <div class="rounded-xl bg-gray-100 p-3 shadow-md dark:bg-gray-700">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-100">
                                        DOING
                                    </h3>

                                    <span
                                        class="material-symbols-rounded text-[19px] leading-none text-gray-400"
                                        aria-hidden="true"
                                    >
                                        more_horiz
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                        <div class="mb-2 h-2 w-14 rounded bg-yellow-400"></div>

                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-100">
                                            Implement user roles
                                        </p>

                                        <div
                                            class="mt-3 h-1.5 overflow-hidden
                                                   rounded-full bg-gray-200
                                                   dark:bg-gray-600"
                                        >
                                            <div class="h-full w-2/3 rounded-full bg-indigo-500"></div>
                                        </div>
                                    </div>

                                    <div class="rounded-lg bg-white p-3 shadow-sm dark:bg-gray-800">
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-100">
                                            Sprint testing
                                        </p>
                                    </div>
                                </div>
                            </div>

                            <div class="rounded-xl bg-gray-100 p-3 shadow-md dark:bg-gray-700">
                                <div class="mb-3 flex items-center justify-between">
                                    <h3 class="text-sm font-bold text-gray-700 dark:text-gray-100">
                                        DONE
                                    </h3>

                                    <span
                                        class="material-symbols-rounded text-[19px] leading-none text-gray-400"
                                        aria-hidden="true"
                                    >
                                        more_horiz
                                    </span>
                                </div>

                                <div class="space-y-3">
                                    <div
                                        class="rounded-lg border-l-4 border-green-400
                                               bg-white p-3 shadow-sm
                                               dark:bg-gray-800"
                                    >
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-100">
                                            Create project
                                        </p>

                                        <p
                                            class="mt-2 flex items-center gap-1
                                                text-xs font-medium text-green-600
                                                dark:text-green-400"
                                        >
                                            <span class="material-symbols-rounded text-[16px] leading-none">
                                                check_circle
                                            </span>

                                            Complete
                                        </p>
                                    </div>

                                    <div
                                        class="rounded-lg border-l-4 border-green-400
                                               bg-white p-3 shadow-sm
                                               dark:bg-gray-800"
                                    >
                                        <p class="text-sm font-medium text-gray-700 dark:text-gray-100">
                                            Set sprint goal
                                        </p>
                                    </div>
                                </div>
                            </div>

                        </div>
                    </div>
                </div>
            </div>
        </section>


        <section class="bg-white py-24 dark:bg-gray-900">
            <div
                class="mx-auto grid max-w-7xl items-center gap-16
                       px-6 lg:grid-cols-2 lg:px-8"
            >
                <div>
                    <p
                        class="mb-3 text-sm font-bold uppercase
                               tracking-widest text-indigo-600
                               dark:text-indigo-400"
                    >
                        Meet Mangie
                    </p>

                    <h2
                        class="text-3xl font-bold tracking-tight
                               text-gray-900 sm:text-4xl
                               dark:text-white"
                    >
                        A simpler way to manage agile projects.
                    </h2>
                </div>

                <div class="space-y-5 text-lg leading-8 text-gray-600 dark:text-gray-300">
                    <p>
                        <strong class="font-semibold text-gray-900 dark:text-white">
                            Mangie
                        </strong>
                        is an agile project management application designed to help
                        teams break larger projects into manageable boards, sprints
                        and tasks.
                    </p>

                    <p>
                        The project was made by
                        <strong class="font-semibold text-indigo-600 dark:text-indigo-400">
                            Vadim Filyakin
                        </strong>
                        (yours truly) as a practical project exploring modern web development,
                        collaboration workflows and agile software development.
                    </p>
                </div>
            </div>
        </section>


        {{-- =====================================================
            TODO: Gotta change the icons for this shit
        ====================================================== --}}
        <section class="bg-gray-50 py-24 dark:bg-gray-950">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">

                <div class="mx-auto max-w-2xl text-center">
                    <p
                        class="text-sm font-bold uppercase tracking-widest
                               text-indigo-600 dark:text-indigo-400"
                    >
                        Everything in one workspace
                    </p>

                    <h2
                        class="mt-3 text-3xl font-bold tracking-tight
                               text-gray-900 sm:text-4xl
                               dark:text-white"
                    >
                        Built around the way agile teams work
                    </h2>

                    <p class="mt-5 text-lg text-gray-600 dark:text-gray-400">
                        Plan the work, organise it visually, collaborate with your
                        team and keep track of progress from one place.
                    </p>
                </div>


                <div class="mt-16 grid gap-6 md:grid-cols-2 lg:grid-cols-3">

                    <div
                        class="group rounded-2xl border border-gray-200
                               bg-white p-7 shadow-sm transition
                               hover:-translate-y-1 hover:shadow-lg
                               dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center
                                rounded-xl bg-indigo-100 text-indigo-600
                                dark:bg-indigo-950 dark:text-indigo-400"
                        >
                            <span
                                class="material-symbols-rounded text-[28px] leading-none"
                                aria-hidden="true"
                            >
                                view_column
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Visual Boards
                        </h3>

                        <p class="mt-3 leading-7 text-gray-600 dark:text-gray-400">
                            Organise work into clear columns and move tasks through
                            each stage of your workflow.
                        </p>
                    </div>


                    <div
                        class="group rounded-2xl border border-gray-200
                               bg-white p-7 shadow-sm transition
                               hover:-translate-y-1 hover:shadow-lg
                               dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center
                                rounded-xl bg-blue-100 text-blue-600
                                dark:bg-blue-950 dark:text-blue-400"
                        >
                            <span
                                class="material-symbols-rounded text-[28px] leading-none"
                                aria-hidden="true"
                            >
                                calendar_month
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Sprint Management
                        </h3>

                        <p class="mt-3 leading-7 text-gray-600 dark:text-gray-400">
                            Create sprints, define goals and keep the team's current
                            work separated from future planning.
                        </p>
                    </div>


                    <div
                        class="group rounded-2xl border border-gray-200
                               bg-white p-7 shadow-sm transition
                               hover:-translate-y-1 hover:shadow-lg
                               dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center
                                rounded-xl bg-purple-100 text-purple-600
                                dark:bg-purple-950 dark:text-purple-400"
                        >
                            <span
                                class="material-symbols-rounded text-[28px] leading-none"
                                aria-hidden="true"
                            >
                                inventory_2
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Product Backlog
                        </h3>

                        <p class="mt-3 leading-7 text-gray-600 dark:text-gray-400">
                            Keep upcoming work in one place and move tasks into a
                            sprint when the team is ready to tackle them.
                        </p>
                    </div>


                    <div
                        class="group rounded-2xl border border-gray-200
                               bg-white p-7 shadow-sm transition
                               hover:-translate-y-1 hover:shadow-lg
                               dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center
                                rounded-xl bg-amber-100 text-amber-600
                                dark:bg-amber-950 dark:text-amber-400"
                        >
                            <span
                                class="material-symbols-rounded text-[28px] leading-none"
                                aria-hidden="true"
                            >
                                task_alt
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Task Planning
                        </h3>

                        <p class="mt-3 leading-7 text-gray-600 dark:text-gray-400">
                            Add priorities, labels, story points and other task details
                            so the team knows what needs attention.
                        </p>
                    </div>


                    <div
                        class="group rounded-2xl border border-gray-200
                               bg-white p-7 shadow-sm transition
                               hover:-translate-y-1 hover:shadow-lg
                               dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center
                                rounded-xl bg-green-100 text-green-600
                                dark:bg-green-950 dark:text-green-400"
                        >
                            <span
                                class="material-symbols-rounded text-[28px] leading-none"
                                aria-hidden="true"
                            >
                                groups
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Team Collaboration
                        </h3>

                        <p class="mt-3 leading-7 text-gray-600 dark:text-gray-400">
                            Add users to boards and give everyone involved a shared
                            view of the work.
                        </p>
                    </div>


                    <div
                        class="group rounded-2xl border border-gray-200
                               bg-white p-7 shadow-sm transition
                               hover:-translate-y-1 hover:shadow-lg
                               dark:border-gray-800 dark:bg-gray-900"
                    >
                        <div
                            class="mb-5 flex h-12 w-12 items-center justify-center
                                rounded-xl bg-rose-100 text-rose-600
                                dark:bg-rose-950 dark:text-rose-400"
                        >
                            <span
                                class="material-symbols-rounded text-[28px] leading-none"
                                aria-hidden="true"
                            >
                                monitoring
                            </span>
                        </div>

                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">
                            Progress Tracking
                        </h3>

                        <p class="mt-3 leading-7 text-gray-600 dark:text-gray-400">
                            Follow completed work, sprint progress and project
                            activity without losing sight of the bigger picture.
                        </p>
                    </div>

                </div>
            </div>
        </section>


        <section class="bg-white py-24 dark:bg-gray-900">
            <div class="mx-auto max-w-7xl px-6 lg:px-8">

                <div
                    class="relative overflow-hidden rounded-3xl
                           bg-gradient-to-r from-indigo-700 to-blue-600
                           px-8 py-14 shadow-xl
                           sm:px-14 lg:flex lg:items-center lg:justify-between"
                >
                    <div
                        class="absolute -right-24 -top-24 h-72 w-72
                               rounded-full bg-white/10"
                    ></div>

                    <div
                        class="absolute -bottom-32 left-1/3 h-72 w-72
                               rounded-full bg-indigo-300/10"
                    ></div>

                    <div class="relative max-w-2xl">
                        <h2 class="text-3xl font-bold tracking-tight text-white">
                            Ready to get your work organised?
                        </h2>

                        <p class="mt-4 text-lg text-indigo-100">
                            Create a Mangie account and start building your workspace.
                        </p>
                    </div>

                    <div class="relative mt-8 lg:mt-0 lg:ml-8">
                        @guest
                            @if (Route::has('register'))
                                <a
                                    href="{{ route('register') }}"
                                    class="inline-flex items-center rounded-lg
                                           bg-white px-6 py-3.5
                                           text-base font-bold text-indigo-700
                                           shadow-md transition
                                           hover:-translate-y-0.5 hover:bg-gray-100"
                                >
                                    Create an account

                                    <span
                                        class="material-symbols-rounded ml-2 text-[20px] leading-none"
                                        aria-hidden="true"
                                    >
                                        arrow_forward
                                    </span>
                                </a>
                            @endif
                        @else
                            <a
                                href="{{ url('/dashboard') }}"
                                class="inline-flex items-center rounded-lg
                                       bg-white px-6 py-3.5
                                       text-base font-bold text-indigo-700
                                       shadow-md transition hover:bg-gray-100"
                            >
                                Go to dashboard

                                <span
                                    class="material-symbols-rounded ml-2 text-[20px] leading-none"
                                    aria-hidden="true"
                                >
                                    arrow_forward
                                </span>
                            </a>
                        @endguest
                    </div>
                </div>

            </div>
        </section>

    </main>


    <footer
        class="border-t border-gray-200 bg-gray-50
               dark:border-gray-800 dark:bg-gray-950"
    >
        <div
            class="mx-auto flex max-w-7xl flex-col gap-4
                   px-6 py-10
                   sm:flex-row sm:items-center sm:justify-between
                   lg:px-8"
        >
            <div class="flex items-center gap-3">
                <div
                    class="flex h-8 w-8 items-center justify-center
                        rounded-lg bg-indigo-600 text-white"
                >
                    <span
                        class="material-symbols-rounded text-[20px] leading-none"
                        aria-hidden="true"
                    >
                        view_kanban
                    </span>
                </div>

                <span class="font-semibold text-gray-900 dark:text-white">
                    Mangie
                </span>
            </div>

            <p class="text-sm text-gray-500 dark:text-gray-400">
                A project made by
                <span class="font-medium text-gray-700 dark:text-gray-300">
                    Vadim Filyakin (yep, still me)
                </span>
            </p>

            <p class="text-sm text-gray-400 dark:text-gray-500">
                ©  Please hire me. I need money, and I can do whatever u want me to do. Please Please Please
            </p>
        </div>
    </footer>

</body>
</html>