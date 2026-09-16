<tr
    {{ $attributes->merge([
        'class' =>
            'group text-gray-700 transition
             hover:bg-gray-50
             dark:bg-gray-900
             dark:text-gray-300
             dark:hover:bg-gray-800',

        'id' => "task-list-item-$task->id"
    ]) }}
>
    <td
        class="border-b border-gray-200
               py-3 pl-5 pr-3
               text-sm font-medium
               dark:border-gray-700"
    >
        {{ $task->title }}
    </td>

    <td
        class="border-b border-gray-200
               py-3
               dark:border-gray-700"
    >
        <div
            class="flex justify-end gap-2
                   px-4"
        >
            @if($task->column_id != 1)
                <x-status-icon
                    name="{{ $column->name }}"
                />
            @endif

            @if($task->labels)
                <x-status-icon
                    name="{{ $task->labels }}"
                />
            @endif

            @if($task->priority)
                <x-status-icon
                    name="{{ $task->priority }}"
                />
            @endif
        </div>
    </td>

    <td
        class="w-14 border-b
               border-gray-200 py-2
               dark:border-gray-700"
    >
        <button
            type="button"
            id="task-list-menu-{{ $task->id }}"
            class="flex h-9 w-9
                   items-center justify-center
                   rounded-lg text-gray-400
                   transition
                   hover:bg-gray-100
                   hover:text-gray-700
                   dark:hover:bg-gray-700
                   dark:hover:text-white"
            aria-label="Task actions"
        >
            <span
                class="material-symbols-rounded
                       text-[20px]"
            >
                more_horiz
            </span>
        </button>


        <div
            id="task-list-detail-{{ $task->id }}"
            class="fixed inset-0 z-[150]
                   hidden overflow-y-auto"
            role="dialog"
            aria-modal="true"
        >
            <div
                data-task-backdrop
                class="fixed inset-0
                       bg-gray-950/55
                       backdrop-blur-[2px]"
            ></div>

            <div
                class="relative flex min-h-full
                       items-start justify-center
                       p-4
                       sm:items-center sm:p-6"
            >
                <x-task-detail :task="$task"/>
            </div>
        </div>
    </td>
</tr>

<script>
window.addEventListener(
    'DOMContentLoaded',
    function () {

        const taskId =
            {{ $task->id }};

        const row =
            document.getElementById(
                `task-list-item-${taskId}`
            );

        const modal =
            document.getElementById(
                `task-list-detail-${taskId}`
            );

        const backdrop =
            modal.querySelector(
                '[data-task-backdrop]'
            );

        document.body.appendChild(modal);


        function openTaskDetail() {

            if (
                row.dataset.wasDragged
                === '1'
            ) {
                return;
            }

            modal.classList.remove(
                'hidden'
            );

            document.body.classList.add(
                'overflow-hidden'
            );
        }


        function closeTaskDetail() {

            modal.classList.add(
                'hidden'
            );

            document.body.classList.remove(
                'overflow-hidden'
            );
        }


        row.addEventListener(
            'click',
            function (event) {

                if (
                    event.ctrlKey ||
                    event.target.closest(
                        'button, a, input, textarea, select, label'
                    )
                ) {
                    return;
                }

                openTaskDetail();
            }
        );


        backdrop.addEventListener(
            'click',
            closeTaskDetail
        );


        modal.addEventListener(
            'task-detail:close',
            closeTaskDetail
        );


        document.addEventListener(
            'keydown',
            function (event) {

                if (
                    event.key === 'Escape'
                    && !modal
                        .classList
                        .contains('hidden')
                ) {
                    closeTaskDetail();
                }
            }
        );

    }
);
</script>