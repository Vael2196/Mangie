<div
    id="sprint-loading-table-{{ $board->id }}"
    class="min-w-full overflow-x-auto rounded border
           bg-gray-100 p-3 hover:bg-gray-200
           dark:bg-gray-700 dark:hover:bg-gray-800"
>
    <div class="mb-2 flex justify-between">
        <h1 class="text-xl font-semibold dark:text-white">
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

    <table
        class="table-fixed min-w-full overflow-x-auto
               rounded border bg-white
               dark:bg-gray-900"
    >
        <tbody
            {{ $attributes->whereStartsWith('data-') }}
            class="min-h-[72px]"
        >
            {{ $slot }}

            <tr data-empty-drop-marker>
                <td
                    colspan="2"
                    class="pointer-events-none h-[58px]
                           border-2 border-dashed
                           border-gray-200 px-3 text-center
                           text-xs text-gray-400
                           dark:border-gray-700
                           dark:text-gray-500"
                >
                    Drop a task here
                </td>
            </tr>
        </tbody>
    </table>
</div>
