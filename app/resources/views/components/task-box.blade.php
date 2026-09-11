<div
    {{ $attributes->merge([
        'class' =>
            'group flex min-h-20 w-full cursor-pointer
             rounded-xl border border-gray-200
             bg-white p-3
             text-gray-800 shadow-sm
             transition-all duration-200
             hover:-translate-y-0.5
             hover:border-indigo-200 hover:shadow-md
             dark:border-gray-700 dark:bg-gray-900
             dark:text-gray-100 dark:hover:border-indigo-800',

        'id' => "task-list-item-$task->id"
    ]) }}
>
    <div class="min-w-0 flex grow flex-col">
        <p
            data-task-title
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
                   transition
                   hover:bg-gray-100 hover:text-gray-700
                   dark:hover:bg-gray-800
                   dark:hover:text-white"
            aria-label="Open task details"
        >
            <span class="material-symbols-rounded text-[19px]">
                more_horiz
            </span>
        </button>

        <span
            class="material-symbols-rounded
                   text-[24px] text-indigo-400"
            title="Assigned user"
        >
            account_circle
        </span>
    </div>
</div>


<div
    id="task-list-detail-{{ $task->id }}"
    class="hidden fixed inset-0 z-[100] overflow-y-auto"
    role="dialog"
    aria-modal="true"
    aria-labelledby="task-title-{{ $task->id }}"
>
    <div
        data-task-backdrop
        class="fixed inset-0 bg-gray-950/55
               backdrop-blur-[2px]"
    ></div>

    <div
        class="relative flex min-h-full
               items-start justify-center
               p-4 sm:items-center sm:p-6"
    >
        <x-task-detail :task="$task"/>
    </div>
</div>


<script>
window.addEventListener('DOMContentLoaded', function () {

    const taskId = {!! json_encode($task->id, JSON_HEX_TAG) !!};

    const taskMenu =
        document.getElementById(`task-box-${taskId}`);

    const taskListItem =
        document.getElementById(`task-list-item-${taskId}`);

    const taskDetail =
        document.getElementById(`task-list-detail-${taskId}`);

    const backdrop =
        taskDetail.querySelector('[data-task-backdrop]');


    function openTaskDetail() {
        taskDetail.classList.remove('hidden');

        document.body.classList.add('overflow-hidden');
    }


    function closeTaskDetail() {
        taskDetail.classList.add('hidden');

        document.body.classList.remove('overflow-hidden');
    }


    taskListItem.addEventListener('click', function (event) {

        if (
            event.target.closest(
                'button, a, input, textarea, select, label'
            )
        ) {
            return;
        }

        openTaskDetail();
    });


    taskMenu.addEventListener('click', function (event) {

        event.stopPropagation();

        openTaskDetail();
    });


    backdrop.addEventListener('click', function () {
        closeTaskDetail();
    });


    taskDetail.addEventListener(
        'task-detail:close',
        closeTaskDetail
    );


    document.addEventListener('keydown', function (event) {

        if (
            event.key === 'Escape' &&
            !taskDetail.classList.contains('hidden')
        ) {
            closeTaskDetail();
        }
    });


    document.addEventListener('click', () => {

        taskListItem.classList.remove(
            'hover:bg-blue-100',
            'dark:hover:bg-blue-600',
            'bg-blue-300'
        );
    });


    document.addEventListener('contextmenu', () => {

        taskListItem.classList.remove(
            'hover:bg-blue-100',
            'dark:hover:bg-blue-600',
            'bg-blue-300'
        );
    });


    taskListItem.addEventListener(
        'contextmenu',
        function (event) {

            event.stopPropagation();
            event.preventDefault();

            if (!event.ctrlKey) {
                return false;
            }

            const toggleClasses = [
                'hover:bg-blue-100',
                'dark:hover:bg-blue-600',
                'bg-blue-300',
                'dark:bg-blue-400'
            ];

            toggleClasses.forEach(className => {
                taskListItem.classList.toggle(className);
            });

            return false;
        },
        false
    );

});
</script>