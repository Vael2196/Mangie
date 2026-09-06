<!-- <div class="bg-white px-6 shadow-lg rounded-lg dark:bg-gray-700 p-4">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold dark:text-white">{{ $board->name }}</h2>
        <form method="POST" action="{{ route('boards.destroy', $board->id)}}" onsubmit="return confirm('Are you sure you want to delete this board?')">
            @csrf
            @method('delete')
            <button class="bg-red-600 hover:bg-red-400 text-white text-sm py-1 px-2 rounded-full">
                Delete
            </button>
        </form>
    </div>
    <a href="{{ route('boards.show', $board->id) }}" class="text-blue-500 hover:underline">View</a>
    <div class="mt-auto flex items-end justify-end">
        <td class="py-2 border-b-2 w-20 flex justify-items-end">
            @if ($board->completed == 1)
                <span class="text-green-500">Completed</span>
            @elseif ($board->status == 1)
                <span class="text-green-500">Active</span>
            @else
                <span class="text-red-500">Inactive</span>
            @endif
        </td>
    </div>
</div> -->

<div
    class="group relative flex min-h-44 flex-col overflow-hidden
           rounded-2xl border border-gray-200
           bg-white p-5 shadow-sm
           transition duration-200
           hover:-translate-y-1 hover:border-indigo-200
           hover:shadow-lg
           dark:border-gray-800 dark:bg-gray-900
           dark:hover:border-indigo-800"
>

    <div
        class="absolute inset-x-0 top-0 h-1
               bg-gradient-to-r from-indigo-500 to-blue-500"
    ></div>


    <div class="flex items-start justify-between gap-4">

        <div class="min-w-0">
            <p
                class="text-xs font-semibold uppercase tracking-wider
                       text-indigo-500"
            >
                Sprint
            </p>

            <h2
                class="mt-1 truncate text-lg font-bold
                       text-gray-900 dark:text-white"
            >
                {{ $board->name }}
            </h2>
        </div>


        @if ($board->completed == 1)
            <span
                class="inline-flex items-center gap-1 rounded-full
                       bg-green-50 px-2.5 py-1
                       text-xs font-semibold text-green-700
                       dark:bg-green-950/40 dark:text-green-400"
            >
                <span class="material-symbols-rounded text-[15px]">
                    check_circle
                </span>
                Completed
            </span>

        @elseif ($board->status == 1)
            <span
                class="inline-flex items-center gap-1 rounded-full
                       bg-indigo-50 px-2.5 py-1
                       text-xs font-semibold text-indigo-700
                       dark:bg-indigo-950/50 dark:text-indigo-300"
            >
                <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                Active
            </span>

        @else
            <span
                class="rounded-full bg-gray-100 px-2.5 py-1
                       text-xs font-semibold text-gray-500
                       dark:bg-gray-800 dark:text-gray-400"
            >
                Not started
            </span>
        @endif
    </div>


    <p
        class="mt-4 text-sm leading-6
               text-gray-500 dark:text-gray-400"
    >
        Open this sprint to manage tasks, participants and progress.
    </p>


    <div
        class="mt-auto flex items-center justify-between
               border-t border-gray-100 pt-4
               dark:border-gray-800"
    >
        <a
            href="{{ route('boards.show', $board->id) }}"
            class="inline-flex items-center gap-1
                   text-sm font-semibold text-indigo-600
                   transition hover:text-indigo-500
                   dark:text-indigo-400"
        >
            Open sprint

            <span
                class="material-symbols-rounded text-[18px]
                       transition group-hover:translate-x-0.5"
            >
                arrow_forward
            </span>
        </a>


        <form
            method="POST"
            action="{{ route('boards.destroy', $board->id) }}"
            onsubmit="return confirm('Are you sure you want to delete this board?')"
        >
            @csrf
            @method('delete')

            <button
                type="submit"
                title="Delete sprint"
                class="flex h-8 w-8 items-center justify-center
                       rounded-lg text-gray-400 transition
                       hover:bg-red-50 hover:text-red-600
                       dark:hover:bg-red-950/30 dark:hover:text-red-400"
            >
                <span class="material-symbols-rounded text-[19px]">
                    delete
                </span>
            </button>
        </form>
    </div>
</div>