<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl">{{$board->name}}</h1>
        <a href="{{ route('boards.show', $board->id) }}"><x-secondary-button>Start Sprint</x-secondary-button></a>
    </div>
    <table {{ $attributes->merge([ "class" => "table-fixed border min-w-full overflow-x-auto rounded bg-white"])}} id="sprint-loading-table-{{$board->id}}">
        <tbody>
            @if($slot->isNotEmpty())
                {{ $slot }}
            @else
                <p class="text-sm">Add tasks here or from the product backlog</p>
            @endif
        </tbody>
    </table>
</div>
