<x-app-layout>
    <x-top-bar title="Project Boards"/>

    <div class="container mx-auto mt-8">
        {{-- <h1 class="text-3xl px-6 font-bold dark:text-white mb-4">Project Boards</h1> --}}

        <!-- Success message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif

        <!-- Display boards in card format -->
        <div class="grid px-6 grid-cols-4 gap-4">
            <!-- Existing boards -->
            @foreach($boards as $board)
                <div class="bg-white px-6 shadow-lg rounded-lg dark:bg-gray-700 p-4">
                    <h2 class="text-xl font-bold dark:text-white">{{ $board->name }}</h2>
                    <a href="{{ route('boards.show', $board->id) }}" class="text-blue-500 hover:underline">View Board</a>
                </div>
            @endforeach

            <!-- Create new board card -->
            <div class="bg-gray-100 dark:bg-gray-700 shadow-lg rounded-lg p-4 flex items-center justify-center">
                <form id="new-board-form" action="{{ route('boards.store') }}" method="POST" onsubmit="createBoard(event)">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ 1 }}">
                    <input type="text" name="name" id="board-name" class="bg-white dark:bg-gray-400 dark:placeholder-gray-700 shadow-inner rounded-lg p-2 w-full" placeholder="Create new board" required autocomplete="off">
                </form>
            </div>
        </div>
    </div>

    <script>
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
                body: JSON.stringify({
                    name: boardName,
                    project_id: document.querySelector('input[name="project_id"]').value
                })
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
                        <a href="/boards/${data.board.id}" class="text-blue-500 hover:underline">View Board</a>
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
