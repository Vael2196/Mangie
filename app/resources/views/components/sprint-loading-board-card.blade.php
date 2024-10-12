<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100 dark:bg-gray-700"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl">{{$board->name}}</h1>
        <a href="{{ route('boards.show', $board->id) }}"><x-secondary-button>Start Sprint</x-secondary-button></a>
    </div>
    {{$slot}}
</div>
