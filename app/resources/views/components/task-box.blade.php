<div
    {{ $attributes->merge([
        'class' =>
            'group flex min-h-20 w-full
             rounded-xl border border-gray-200
             bg-white p-3
             text-gray-800 shadow-sm
             transition
             hover:border-indigo-200 hover:shadow-md
             dark:border-gray-700 dark:bg-gray-900
             dark:text-gray-100 dark:hover:border-indigo-800',

        'id' => "task-list-item-$task->id"
    ]) }}
>
    <div class="min-w-0 flex grow flex-col">

        <p
            class="pr-2 text-sm font-semibold leading-5
                   text-gray-800 dark:text-gray-100"
        >
            {{ $task->title }}
        </p>

        <div class="mt-3 flex flex-wrap gap-1.5">
            @if($task->labels != null)
                <x-status-icon name="{{ $task->labels }}"/>
            @endif

            @if($task->column_id != 1)
                <x-status-icon name="{{ $column->name }}"/>
            @endif

            @if($task->priority != null)
                <x-status-icon name="{{ $task->priority }}"/>
            @endif
        </div>

    </div>


    <div
        class="ml-2 flex shrink-0 flex-col
               items-center justify-between"
    >
        <button
            type="button"
            id="task-box-{{ $task->id }}"
            class="flex h-8 w-8 items-center justify-center
                   rounded-lg text-gray-400
                   transition hover:bg-gray-100
                   hover:text-gray-700
                   dark:hover:bg-gray-800 dark:hover:text-white"
        >
            <span class="material-symbols-rounded text-[19px]">
                more_horiz
            </span>
        </button>

        <div
            class="hidden"
            id="task-list-detail-{{ $task->id }}"
        >
            <x-task-detail :task="$task"/>
        </div>

        <span
            class="material-symbols-rounded text-[24px]
                   text-indigo-400"
            title="Assigned user"
        >
            account_circle
        </span>
    </div>
</div>

<script>
    window.addEventListener('DOMContentLoaded', function () {
        var index = {!! json_encode($task->id, JSON_HEX_TAG) !!};
        let taskMenu = document.getElementById(`task-box-${index}`);
        let taskListItem = document.getElementById(`task-list-item-${index}`);

        // Reset everything when clicking outside
        document.addEventListener('click', e => {
            const taskItem = document.getElementById(`task-list-item-${index}`);
            taskItem.classList.remove("hover:bg-blue-100", "dark:hover:bg-blue-600", "bg-blue-300");
        })

        document.addEventListener('contextmenu', e => {
            const taskItem = document.getElementById(`task-list-item-${index}`);
            taskItem.classList.remove("hover:bg-blue-100", "dark:hover:bg-blue-600", "bg-blue-300");
        })

        taskMenu.addEventListener('click', e => {
            const taskDetail = document.getElementById(`task-list-detail-${index}`);
            taskDetail.classList.toggle('hidden');
        });

        taskListItem.addEventListener('contextmenu', e => {
            e.stopPropagation();
            e.preventDefault();

            // Do nothing when right clicking
            if (!e.ctrlKey){return false;};

            // Highlight task when ctrl-right-clicking
            let toggleArr = [          // OFF
                        "hover:bg-blue-100", "dark:hover:bg-blue-600", "bg-blue-300", "dark:bg-blue-400" // ON
                        ];
            for(toggleOption of toggleArr){
                taskListItem.classList.toggle(toggleOption);
            }
            return false;
        }, false);
    });
</script>
