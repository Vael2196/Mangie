<x-app-layout>

    <!-- Meta tag for CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <div class="container mx-auto mt-8">
        <div class="flex flex-col">
            <x-top-bar :title="$board->name"/>

            <div class="flex pr-10 items-start">
                <p class="text-sm px-6 min-h-20 grow">This is a description of the board.</p>
                <div class="flex space-x-8 items-center">
                    <p>Some info here</p>
                    <x-secondary-button>Complete Board</x-secondary-button>
                    <a class="hover:cursor-pointer"><i class="fa-solid fa-ellipsis"></i></a>
                </div>
            </div>

            <ul class="flex space-x-2 px-6">
                <!-- Display users here -->
                <li class="text-orange-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
                <li class="text-purple-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
                <li class="text-red-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
            </ul>
        </div>

        <div class="flex flex-nowrap space-x-5 h-4/6 p-5 overflow-auto max-w-[80vw] max-h-[70vh]" id="columns-container">
            <!-- Display columns and tasks -->
            @foreach($board->columns as $column)
                <div class="bg-gray-100 shadow-lg rounded-lg p-4 w-64">
                    <h2 class="text-xl font-bold">{{ $column->name }}</h2>
                    @foreach($column->tasks as $task)
                        <div class="bg-white p-2 my-2 rounded-lg shadow">
                            {{ $task->title }}
                        </div>
                    @endforeach
                </div>
            @endforeach

            <!-- Option to add new columns -->
            <div id="add-column-section" class="flex items-center space-x-4">
                <a id="add-column-btn" class="hover:cursor-pointer">
                    <i class="fa-regular fa-square-plus fa-2x"></i>
                </a>

                <!-- Hidden input field to add a new column -->
                <input id="new-column-input" type="text" class="hidden bg-white shadow-inner rounded-lg p-2 w-full" placeholder="Enter new column name" />
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addColumnBtn = document.getElementById('add-column-btn');
            const inputField = document.getElementById('new-column-input');
            const columnsContainer = document.getElementById('columns-container');

            addColumnBtn.addEventListener('click', function () {
                // Show the input field when the button is clicked
                inputField.classList.remove('hidden');
                inputField.focus();

                // Move the "Add" button further to the right
                addColumnBtn.style.marginLeft = '20px';
            });

            // Handle the "Enter" key event when entering a new column name
            inputField.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const columnName = inputField.value.trim();
                    if (columnName !== '') {
                        // Make an AJAX call to store the new column
                        fetch('{{ route('columns.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                name: columnName,
                                board_id: {{ $board->id }}
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Append the new column to the page dynamically
                                const newColumn = document.createElement('div');
                                newColumn.classList.add('bg-gray-100', 'shadow-lg', 'rounded-lg', 'p-4', 'w-64');
                                newColumn.innerHTML = `
                                    <h2 class="text-xl font-bold">${data.column.name}</h2>
                                `;
                                columnsContainer.insertBefore(newColumn, document.getElementById('add-column-section'));

                                // Reset the input field
                                inputField.value = '';
                                inputField.classList.add('hidden');
                                addColumnBtn.style.marginLeft = '0';
                            } else {
                                console.error('Error adding column:', data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                }
            });
        });
    </script>

</x-app-layout>