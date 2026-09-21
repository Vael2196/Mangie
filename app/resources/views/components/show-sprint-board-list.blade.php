<tr data-board-id="{{ $board->id }}" class="px-4 py-2 text-start leading-5 dark:bg-gray-700 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-400 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded"
    id="sprintRow{{$board->id}}"
    onClick="location.href='{{ route('boards.show', $board->id) }}'">
    <td class="py-2 pl-5 border-b-2 min-w-20 overflow-hidden">{{ $board->name }}</td>
    <td class="py-2 border-b-2 w-20">
        @if ($board->completed == 1)
            <span class="text-green-500">Completed</span>
        @elseif ($board->status == 1)
            <span class="text-blue-500">Active</span>
        @else
            <span class="text-red-500">Not Started</span>
        @endif
    </td>
    <td class="py-2 border-b-2 w-20">
        <form method="POST" action="{{ route('boards.destroy', $board->id) }}" onsubmit="return confirm('Are you sure you want to delete this board?')">
            @csrf
            @method('delete')
            <button class="bg-red-600 hover:bg-red-400 text-white text-sm py-1 px-2 rounded-full">
                Delete
            </button>
        </form>
    </td>
</tr>
