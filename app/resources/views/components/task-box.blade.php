@props([
    "DESP" => "", 
    "STATUS" => "", 
    "ASSIGN" => ""
    ])


<div {{ $attributes->merge([ 'class' => 'rounded w-full h-32 bg-white flex p-2'])}}>
    <p class = "pl-2 grow">Be able to filter tasks by status</p>
    <div class = "flex flex-col justify-between">
        <h1>MENU BAR</h1>
        <h1>ICON</h1>
    </div>
</div>