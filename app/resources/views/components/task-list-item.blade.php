<tr {{ $attributes->merge(["class" => "px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded",
                            "id" => "task-list-item-$task->id"])}}>
    <td class="py-2 pl-5 border-b-2 min-w-20">{{$task->title}}</td>
    <td class="py-2 border-b-2 w-20"><x-status-icon name="Epic"/></td>
    <td class="py-2 border-b-2 w-20"><x-status-icon name="status"/></td>
    <td class="py-2 border-b-2 w-20">Priority</td>
    <td class="py-2 border-b-2 w-20">Assigned</td>
    <div class='hidden' id="task-list-detail-{{$task->id}}">
        <x-task-detail :task="$task"/>
    </div>
</tr>
<script>
    document.addEventListener('DOMContentLoaded', function () {
        var index = {!! json_encode($task->id, JSON_HEX_TAG) !!};
        task = document.getElementById(`task-list-item-${index}`);
        task.addEventListener('click', e => {
            const taskDetail = document.getElementById(`task-list-detail-${index}`);
            taskDetail.classList.toggle('hidden');
        });

        task.addEventListener('contextmenu', e => {
            const taskHighlight = document.getElementById(`task-list-item-${index}`);
            e.preventDefault();
            if (!e.ctrlKey){return false;};
            const origin = {
                left: e.pageX,
                top: e.pageY
            };
            toggleArr = ["hover:bg-gray-100","dark:hover:bg-gray-600",              // OFF
                        "hover:bg-blue-100", "dark:hover:bg-blue-600", "bg-blue-300", "taskItemON" // ON
                        ];
            for(toggleOption of toggleArr){
                taskHighlight.classList.toggle(toggleOption);
            }
            return false;
        }, false);
    });
</script>
