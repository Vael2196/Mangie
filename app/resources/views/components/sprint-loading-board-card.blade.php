<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100 dark:bg-gray-700"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl">{{$board->name}}</h1>
        <a href="{{ route('boards.show', $board->id) }}"><x-secondary-button>Start Sprint</x-secondary-button></a>
    </div>
    <div class="rounded w-full h-full bg-slate-100 p-3 m-3 grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 grid-flow-dense gap-2 justify-items-center items-start">
        {{$slot}}
    </div>
</div>
