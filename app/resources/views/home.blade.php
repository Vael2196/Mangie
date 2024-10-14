<x-app-layout>
    <x-top-bar title="Project Boards" :user="$user"/>

    <div class="container mx-auto mt-8 invisible" id="wholePage">
        {{-- <h1 class="text-3xl px-6 font-bold dark:text-white mb-4">Project Boards</h1> --}}

        <!-- Success message -->
        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-gray-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div class="flex justify-end w-full px-7 py-4">
            <select id="sprintBoardSelect" class="border rounded dark:bg-gray-600 dark:text-white">
                <option value="list">List View</option>
                <option value="card">Card View</option>
            </select>
        </div>

        {{-- Card View --}}
        <div class="grid px-6 grid-cols-4 gap-4 card-view-sprint">
            <!-- Existing boards -->
            @foreach($boards as $board)
                {{-- Skip product backlog board --}}
                @if($board->id == 1)
                    @continue
                @endif
                {{-- Show sprint boards --}}
                @if (Str::contains(url()->current(), '/dashboard'))
                    <x-show-sprint-board :board="$board"/>
                @else
                    @if ($board->completed == 0)
                        <x-show-sprint-board :board="$board"/>
                    @endif
                @endif
            @endforeach

            <!-- Create new board card -->
            <div class="bg-gray-100 dark:bg-gray-700 shadow-lg rounded-lg p-4 flex items-center justify-center">
                <form id="new-board-form" method="POST">
                    @csrf
                    <input type="hidden" name="project_id" value="{{ 1 }}">
                    <input type="text" name="name" id="board-name" class="bg-white dark:bg-gray-400 dark:placeholder-gray-700 shadow-inner rounded-lg p-2 w-full" placeholder="Create new board" required autocomplete="off">
                </form>
            </div>
        </div>

        {{-- List View --}}
        <div class="hidden list-view-sprint">
            <table class="table-fixed border min-w-full overflow-x-auto">
                <tbody>
                    @foreach($boards as $board)
                        @if($board->id == 1)
                            @continue
                        @endif
                        <tr class="px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-400 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded"
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
                    @endforeach
                </tbody>
            </table>
            {{-- Link to Form --}}
            <div class="w-full text-start">
                <div id="create-sprint-link" class="block">
                    <div
                        class = 'px-4 py-2 leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                        <div class = "text-center"><i class="fa-solid fa-plus"></i></div>
                        <p class = "lg:block hidden text-sm">Create Sprint</p>
                    </div>
                </div>
                {{-- Form for submitting  --}}
                <input id="input-sprint-field" class="border hidden w-full dark:bg-gray-600 dark:text-white" type="text"
                    placeholder="Enter Sprint Name" class="w-full" />
            </div>
        </div>
    </div>

    <script>
        let cardView = document.querySelector('.card-view-sprint');
        let listView = document.querySelector('.list-view-sprint');
        let viewSelect = document.getElementById('sprintBoardSelect');

        // Function to toggle between list and card view
        function toggleView(viewOption) {
            // Show list view
            if (viewOption == 0) {
                cardView.classList.add('hidden');
                listView.classList.remove('hidden');
                viewSelect.value = 'list';
            // Show card view
            } else if (viewOption == 1) {
                listView.classList.add('hidden');
                cardView.classList.remove('hidden');
                viewSelect.value = 'card';
            }
        }

        // Load previously selected view
        document.addEventListener('DOMContentLoaded', e => {
            let view = localStorage.getItem('view');
            toggleView(view);
        })

        // Main function
        window.addEventListener('DOMContentLoaded', e => {
            // Hack to prevent flickering when reloading page
            const wholePage = document.getElementById('wholePage');
            wholePage.classList.remove('invisible');

            const createSprintButton = document.getElementById('create-sprint-link');
            const inputSprintField = document.getElementById('input-sprint-field');
            const sprintRows = document.querySelectorAll('[id*="sprintRow"]');
            const form = document.getElementById('new-board-form');

            viewSelect.addEventListener('change', e => {
                let viewItem = viewSelect.selectedIndex;
                localStorage.setItem('view', viewItem);
                toggleView(viewItem);
            });

            form.addEventListener('submit', createSprintCard);

            // Create task when clicking button ------------------------------------------
            createSprintButton.addEventListener('click', e => {
                inputSprintField.classList.remove('hidden');
                inputSprintField.focus();
                createSprintButton.classList.add("hidden");
            });

            inputSprintField.addEventListener('focusout', e => {
                createSprintButton.classList.remove("hidden");
                inputSprintField.classList.add('hidden');
            });


            // Add new task to the product backlog
            inputSprintField.addEventListener('keypress', function(e) {
                // Check if the key pressed is the Enter key
                if (e.key !== 'Enter') {
                    return;
                }

                // Check if the input field is not empty
                const sprintTitle = inputSprintField.value.trim();
                if (sprintTitle === '') {
                    return;
                }

                // Create the task
                createBoard(inputSprintField);
            });

        });

        function createSprintCard(event){
            event.preventDefault(); // Prevent form from reloading the page
            const boardNameInput = document.getElementById('board-name');
            const boardName = boardNameInput.value.trim();
            if (boardName === '') return; // Prevent empty submissions
            createBoard(boardNameInput);
        }

        // Function to handle the creation of a new board
        function createBoard(boardNameInput) {
            const boardName = boardNameInput.value.trim();

            // Submit the form via AJAX (using Fetch API)
            fetch('{{ route('boards.store') }}', {
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
                    // // Add the new board dynamically to the page
                    // const grid = document.querySelector('.grid');
                    // const newBoardCard = document.createElement('div');
                    // // newBoardCard.classList.add('bg-white', 'shadow-lg', 'rounded-lg', 'p-4');
                    // newBoardCard.innerHTML = `
                    //     <div class="bg-white px-6 shadow-lg rounded-lg dark:bg-gray-700 p-4">
                    //         <div class="flex justify-between">
                    //             <h2 class="text-xl font-bold dark:text-white">${data.board.name}</h2>
                    //             <form method="POST" action="/boards/${data.board.id}" onsubmit="return confirm('Are you sure you want to delete this board?')">
                    //                 @csrf
                    //                 @method('delete')
                    //                 <button class="bg-red-600 hover:bg-red-400 text-white text-sm py-1 px-2 rounded-full">
                    //                     Delete
                    //                 </button>
                    //             </form>
                    //         </div>
                    //         <a href="/boards/${data.board.id}" class="text-blue-500 hover:underline">View</a>
                    //     </div>
                    // `;
                    // grid.insertBefore(newBoardCard, form.closest('.bg-gray-100'));

                    // Clear the input field
                    boardNameInput.value = '';
                    location.reload();
                } else {
                    console.error('Error creating board:', data.message);
                }
            })
            .catch(error => console.error('Error:', error));
        }
    </script>

</x-app-layout>
