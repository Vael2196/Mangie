<div {{ $attributes->merge([ 'class' => 'rounded w-[15.5rem] min-h-20 max-h-40 bg-white dark:bg-gray-500 dark:text-white flex p-2 mb-3',
                                "id" => "task-list-item-$task->id"])}}>
    <div class = "flex flex-col grow space-y-2 px-2 overflow-hidden mr-2">
        <p>{{$task->title}}</p>
        <div class = "flex space-x-2">
            @if($task->labels != null)
                <x-status-icon name="{{$task->labels}}"/>
            @endif
            @if($task->column_id != 1)
                <x-status-icon name="{{$column->name}}"/>
            @endif
            @if($task->priority != null)
                <x-status-icon name="{{$task->priority}}"/>
            @endif
        </div>
    </div>
    <div class = "flex flex-col justify-between items-center">
        <div id="task-box-{{$task->id}}" class="hover:cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-700 bg-opacity-10 list-none">
            <i class="fa-solid fa-ellipsis px-2"></i>
        </div>

        <div class='hidden' id="task-list-detail-{{$task->id}}">
            <x-task-detail :task="$task"/>
        </div>
        <div class="text-red-500"><i class="fa-solid fa-circle-user fa-2x"></i></div>
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
                        "hover:bg-blue-100", "dark:hover:bg-blue-600", "bg-blue-300" // ON
                        ];
            for(toggleOption of toggleArr){
                taskListItem.classList.toggle(toggleOption);
            }
            return false;
        }, false);
    });
</script>
