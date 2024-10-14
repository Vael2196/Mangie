<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100 dark:bg-gray-700"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl">{{$board->name}}</h1>

        @if ($activeSprints >= 1)
            @if ($board->status == 1)
                <x-sprint-start-details :board="$board"/>
            @endif
        @else
            <x-sprint-start-details :board="$board"/>
        @endif
    </div>
    <table {{ $attributes->merge([ "class" => "table-fixed border min-w-full overflow-x-auto rounded bg-white"])}} id="sprint-loading-table-{{$board->id}}">
        <tbody>
            @if($slot->isNotEmpty())
                {{ $slot }}
            @else
                <p id="sprint-loading-p-tag-{{$board->id}}" class="text-sm">Add tasks here or from the product backlog</p>
            @endif
        </tbody>
    </table>
</div>
