<div {{ $attributes->merge([ 'class' => 'rounded w-[15.5rem] min-h-20 max-h-40 bg-white dark:bg-gray-500 dark:text-white flex p-2 mb-3',
                                "id" => "task-list-item-$task->id"])}}>
    <div class = "flex flex-col grow space-y-2 px-2 overflow-hidden mr-2">
        <p>{{$task->title}}</p>
        <div class = "flex space-x-2">
            {{-- @foreach($task->status as $status) --}}
                <x-status-icon name='Status'></x-status-icon>
            {{-- @endforeach --}}
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
    document.addEventListener('DOMContentLoaded', function () {
        var index = {!! json_encode($task->id, JSON_HEX_TAG) !!};
        taskMenu = document.getElementById(`task-box-${index}`);
        taskMenu.addEventListener('click', e => {
            const taskDetail = document.getElementById(`task-list-detail-${index}`);
            taskDetail.classList.toggle('hidden');
        });
    });
</script>
