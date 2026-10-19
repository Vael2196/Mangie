<div
    id="sprint-loading-table-{{ $board->id }}"
    class="min-w-0 w-full rounded border
           bg-gray-100 p-3 hover:bg-gray-200
           dark:bg-gray-700 dark:hover:bg-gray-800"
>
    <div class="mb-2 flex flex-wrap items-center justify-between gap-3">
        <h1 class="min-w-0 break-words text-xl font-semibold dark:text-white">
            {{ $board->name }}
        </h1>

        @if (auth()->user()->can('update', $board))
            <x-sprint-start-details :board="$board"/>
        @endif
    </div>

    <table
        class="table-fixed min-w-0 w-full
               rounded border bg-white
               dark:bg-gray-900"
    >
        <tbody
            {{ $attributes->whereStartsWith('data-') }}
            data-can-write="{{ auth()->user()->can('update', $board) ? 'true' : 'false' }}"
            class="min-h-[72px]"
        >
            {{ $slot }}

            <tr data-empty-drop-marker>
                <td
                    colspan="3"
                    class="h-[58px]
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
