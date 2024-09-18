@props([
    "DESP", 
    "STATUS" => ["tasks"], 
    "ASSIGN" => ""
    ])


<div {{ $attributes->merge([ 'class' => 'rounded w-full min-h-20 bg-white flex p-2'])}}>
    <div class = "flex flex-col grow space-y-2 pl-2">
        <p>{{$DESP}}</p>
        <div class = "flex space-x-2">
            @foreach($STATUS as $status)
                <x-status-icon name={{$status}}></x-status-icon>
            @endforeach
        </div>
    </div>
    <div class = "flex flex-col justify-between">
        <h1>MENU BAR</h1>
        <h1>ICON</h1>
    </div>
</div>