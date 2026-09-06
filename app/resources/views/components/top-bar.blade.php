@props([
    'title',
    'user' => null,
])

<header
    x-data="{ profileOpen: false }"
    class="sticky top-0 z-30 border-b border-gray-200/80
           bg-white/90 backdrop-blur-md
           dark:border-gray-800 dark:bg-gray-900/90"
>
    <div
        class="flex min-h-20 items-center justify-between
               gap-6 px-6 lg:px-8"
    >

        <div>
            <p
                class="text-xs font-semibold uppercase tracking-[0.16em]
                       text-indigo-600 dark:text-indigo-400"
            >
                Workspace
            </p>

            <h1
                class="mt-1 text-2xl font-bold tracking-tight
                       text-gray-900 sm:text-3xl dark:text-white"
            >
                {{ $title }}
            </h1>
        </div>


        @if($user)
            <div class="relative">

                <button
                    type="button"
                    @click="profileOpen = !profileOpen"
                    class="flex items-center gap-2 rounded-xl
                           border border-gray-200 bg-white
                           px-2.5 py-2 text-sm font-medium text-gray-700
                           shadow-sm transition
                           hover:border-indigo-200 hover:bg-indigo-50
                           dark:border-gray-700 dark:bg-gray-800
                           dark:text-gray-200 dark:hover:border-indigo-800
                           dark:hover:bg-indigo-950/50"
                >
                    <span
                        class="flex h-9 w-9 items-center justify-center
                               rounded-lg bg-indigo-100 text-indigo-600
                               dark:bg-indigo-950 dark:text-indigo-400"
                    >
                        <span class="material-symbols-rounded text-[22px]">
                            account_circle
                        </span>
                    </span>

                    <span class="hidden sm:block">
                        {{ $user->name }}
                    </span>

                    <span
                        class="material-symbols-rounded hidden text-[19px]
                               text-gray-400 sm:block"
                    >
                        expand_more
                    </span>
                </button>


                <div
                    x-cloak
                    x-show="profileOpen"
                    @click.outside="profileOpen = false"
                    x-transition
                    class="absolute right-0 mt-3 w-64 overflow-hidden
                           rounded-2xl border border-gray-200
                           bg-white shadow-xl
                           dark:border-gray-700 dark:bg-gray-800"
                >
                    <div
                        class="border-b border-gray-100 px-4 py-4
                               dark:border-gray-700"
                    >
                        <p
                            class="font-semibold text-gray-900
                                   dark:text-white"
                        >
                            {{ $user->name }}
                        </p>

                        <p
                            class="mt-0.5 truncate text-sm
                                   text-gray-500 dark:text-gray-400"
                        >
                            {{ $user->email }}
                        </p>
                    </div>

                    <div class="p-2">
                        <a
                            href="{{ route('profile.edit') }}"
                            class="flex items-center gap-3 rounded-lg
                                   px-3 py-2.5 text-sm font-medium
                                   text-gray-700 transition hover:bg-gray-100
                                   dark:text-gray-200 dark:hover:bg-gray-700"
                        >
                            <span
                                class="material-symbols-rounded
                                       text-[20px] text-gray-400"
                            >
                                person
                            </span>

                            Edit profile
                        </a>

                        <form
                            method="POST"
                            action="{{ route('logout') }}"
                        >
                            @csrf

                            <button
                                type="submit"
                                class="flex w-full items-center gap-3
                                       rounded-lg px-3 py-2.5
                                       text-sm font-medium text-red-600
                                       transition hover:bg-red-50
                                       dark:text-red-400
                                       dark:hover:bg-red-950/30"
                            >
                                <span
                                    class="material-symbols-rounded text-[20px]"
                                >
                                    logout
                                </span>

                                Log out
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        @endif

    </div>
</header>
