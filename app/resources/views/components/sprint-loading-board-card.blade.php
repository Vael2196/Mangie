<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100 dark:bg-gray-700"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl">{{$board->name}}</h1>
        <x-sprint-start-details :board="$board"/>
    </div>
    @if($slot->isNotEmpty())
        <div class="rounded h-full bg-slate-100 m-3 grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 grid-flow-dense justify-items-center items-start dark:bg-gray-700">
            {{$slot}}
        </div>
    @else
        <p id="sprint-loading-p-tag-{{$board->id}}" class="text-sm">Add tasks here or from the product backlog</p>
    @endif
</div>
