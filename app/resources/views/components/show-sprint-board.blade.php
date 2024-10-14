<div class="bg-white px-6 shadow-lg rounded-lg dark:bg-gray-700 p-4">
    <div class="flex justify-between">
        <h2 class="text-xl font-bold dark:text-white">{{ $board->name }}</h2>
        <form method="POST" action="{{ route('boards.destroy', $board->id)}}" onsubmit="return confirm('Are you sure you want to delete this board?')">
            @csrf
            @method('delete')
            <button class="bg-red-600 hover:bg-red-400 text-white text-sm py-1 px-2 rounded-full">
                Delete
            </button>
        </form>
    </div>
    <a href="{{ route('boards.show', $board->id) }}" class="text-blue-500 hover:underline">View</a>
    <div class="mt-auto flex items-end justify-end">
        <td class="py-2 border-b-2 w-20 flex justify-items-end">
            @if ($board->completed == 1)
                <span class="text-green-500">Completed</span>
            @elseif ($board->status == 1)
                <span class="text-green-500">Active</span>
            @else
                <span class="text-red-500">Inactive</span>
            @endif
        </td>
    </div>
</div>
