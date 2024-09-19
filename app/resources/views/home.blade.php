{{-- @extends('dashboard')

@section('content')
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Insert Project Management Content -->
                    <div class="container mx-auto">
                        <h1 class="text-3xl font-black text-center my-8">Dashboard</h1>

                        @foreach($projects as $project)
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold">{{ $project->name }}</h2>
                                
                                @foreach($project->boards as $board)
                                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 my-4">
                                        <h3 class="text-xl font-semibold">{{ $board->name }}</h3>
                                        
                                        <div class="flex space-x-4">
                                            @foreach($board->columns as $column)
                                                <div class="bg-gray-200 dark:bg-gray-600 rounded-lg p-4 w-1/4">
                                                    <h4 class="font-bold mb-2">{{ $column->name }}</h4>
                                                    <ul>
                                                        @foreach($column->tasks as $task)
                                                            <li class="bg-white dark:bg-gray-900 p-2 my-2 rounded-lg shadow">
                                                                {{ $task->title }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <!-- End of Project Management Content -->
                </div>
            </div>
        </div>
    </div>
@endsection --}}



<x-app-layout>

    <div class="container mx-auto mt-8">
        <h1 class="text-3xl font-bold mb-4">Project Boards</h1>
    
        <!-- Success message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif
    
        <!-- Display boards in card format -->
        <div class="grid grid-cols-4 gap-4">
            <!-- Existing boards -->
            @foreach($boards as $board)
                <div class="bg-white shadow-lg rounded-lg p-4">
                    <h2 class="text-xl font-bold">{{ $board->name }}</h2>
                    <a href="#" class="text-blue-500 hover:underline">View Board</a>
                </div>
            @endforeach
    
            <!-- Create new board card -->
            <div class="bg-gray-100 shadow-lg rounded-lg p-4 flex items-center justify-center">
                <form id="new-board-form" action="{{ route('boards.store') }}" method="POST" onsubmit="createBoard(event)">
                    @csrf
                    <input type="text" name="name" id="board-name" class="bg-white shadow-inner rounded-lg p-2 w-full" placeholder="Create new board" required autocomplete="off">
                </form>
            </div>
        </div>
    </div>
    
    <script>
        // Function to handle the creation of a new board
        // Function to handle the creation of a new board
        function createBoard(event) {
            event.preventDefault(); // Prevent form from reloading the page

            const form = document.getElementById('new-board-form');
            const boardNameInput = document.getElementById('board-name');
            const boardName = boardNameInput.value.trim();

            if (boardName === '') return; // Prevent empty submissions

            // Submit the form via AJAX (using Fetch API)
            fetch(form.action, {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': document.querySelector('input[name="_token"]').value,
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ name: boardName })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    // Add the new board dynamically to the page
                    const grid = document.querySelector('.grid');
                    const newBoardCard = document.createElement('div');
                    newBoardCard.classList.add('bg-white', 'shadow-lg', 'rounded-lg', 'p-4');
                    newBoardCard.innerHTML = `
                        <h2 class="text-xl font-bold">${data.board.name}</h2>
                        <a href="#" class="text-blue-500 hover:underline">View Board</a>
                    `;
                    grid.insertBefore(newBoardCard, form.closest('.bg-gray-100'));

                    // Clear the input field
                    boardNameInput.value = '';
                } else {
                    console.error('Error creating board:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

</x-app-layout>