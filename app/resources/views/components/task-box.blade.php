@props([
    "desp", 
    "STATUS" => ["tasks"], 
    "ASSIGN" => ""
    ])


<div {{ $attributes->merge([ 'class' => 'rounded w-[15.5rem] min-h-20 max-h-40 bg-white dark:bg-gray-500 dark:text-white flex p-2'])}}>
    <div class = "flex flex-col grow space-y-2 px-2 overflow-hidden mr-2">
        <p>{{$desp}}</p>
        <div class = "flex space-x-2">
            @foreach($STATUS as $status)
                <x-status-icon name={{$status}}></x-status-icon>
            @endforeach
        </div>
    </div>
    <div class = "flex flex-col justify-between items-center">
        <details class="open">
            <summary class="hover:cursor-pointer hover:bg-gray-100 bg-opacity-10 list-none">
                <i class="fa-solid fa-ellipsis px-2"></i>
            </summary>
            <x-task-detail task=""/>
        </details>
        <h1>ICON</h1>
    </div>
</div>