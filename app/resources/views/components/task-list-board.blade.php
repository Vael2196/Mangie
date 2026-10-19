<table class="backlog-task-table table-fixed w-full border">
    <tbody
        {{ $attributes->whereStartsWith('data-') }}
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
