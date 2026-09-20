<div
    class="min-w-full overflow-x-auto rounded border
           bg-gray-100 p-3 dark:bg-gray-700"
>
    <div class="mb-2 flex justify-between">
        <h1 class="text-xl font-semibold">
            {{ $board->name }}
        </h1>

        @if ($activeSprints >= 1)
            @if ($board->status == 1)
                <x-sprint-start-details :board="$board"/>
            @endif
        @elseif ($board->completed == 0)
            <x-sprint-start-details :board="$board"/>
        @endif
    </div>

    <div
        {{ $attributes->whereStartsWith('data-') }}
        class="m-3 grid min-h-[72px] h-full
               grid-flow-dense grid-cols-1
               items-start justify-items-center
               rounded bg-slate-100
               sm:grid-cols-1 md:grid-cols-2
               lg:grid-cols-3 2xl:grid-cols-4
               dark:bg-gray-700"
    >
        {{ $slot }}

        <div
            data-empty-drop-marker
            class="pointer-events-none flex min-h-[58px]
                   w-full items-center justify-center
                   rounded-xl border-2 border-dashed
                   border-gray-200 px-3 text-xs
                   text-gray-400 dark:border-gray-600
                   dark:text-gray-400"
        >
            Drop a task here
        </div>
    </div>
</div>
