<div {{ $attributes->merge(["id" => "sprint-loading-table-{$board->id}", "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100 dark:bg-gray-700 hover:bg-gray-200 dark:hover:bg-gray-800"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl dark:text-white">{{$board->name}}</h1>

        @if ($activeSprints >= 1)
            @if ($board->status == 1)
                <x-sprint-start-details :board="$board"/>
            @endif
        @elseif ($board->completed == 0)
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


<script>
    // when double clicking on the board, it will open the boards.show page
    document.getElementById('sprint-loading-table-{{$board->id}}').addEventListener('dblclick', function() {
        window.location.href = "{{ route('boards.show', $board->id) }}";
    });
</script>
