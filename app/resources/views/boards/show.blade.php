<x-app-layout>

    <!-- Meta tag for CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-top-bar :title="$board->name"/>

    <div class="container mx-auto mt-8">
        <div class="flex flex-col">

            <div class="flex pr-10 items-start dark:text-white">
                <p class="text-sm px-6 min-h-20 max-h-40 overflow-y-auto max-w-[65vw] grow">This is a description of the board.</p>
                <div class="flex space-x-8 items-center">
                    <p class="lg:block hidden">X-days-left</p>
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
                <x-task-column title="{{ $column->name }}">

                    {{-- Show tasks for each column --}}
                    <div class="task-list" id="task-list-{{ $column->id }}">
                        @foreach($column->tasks as $task)
                            <x-task-box :task="$task"/>
                        @endforeach
                    </div>

                    {{-- Add task button --}}
                    @if ($loop->first)
                        <!-- Input field for adding a new task in the first column -->
                        <div class="mt-2">
                            <input type="text" id="new-task-input" class="bg-white shadow-inner rounded-lg p-2 w-full dark:bg-gray-500 dark:text-white" placeholder="Enter new task" />
                        </div>
                    @endif
                </x-task-column>
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

        {{-- Context Menu --}}
        <div class="hidden absolute z-30" id="task-context-menu">
            <div class="2xl:flex 2xl:items-start">
                {{-- "Move To" Button --}}
                <div
                    class='bg-white border px-4 py-2 items-center leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                    <div class = "text-center"
                        onmouseover="document.getElementById('contextBoardMenu').classList.toggle('hidden')">Move To
                    </div>
                    <div class="text-center"><i class="fa-solid fa-angle-right"></i></div>
                </div>

                {{-- Context sub menu (Buttons for each sprint to move to) --}}
                <div class="hidden shadow-lg" id="contextBoardMenu">
                    @foreach ($board->columns as $column)
                        <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                            id="context-board-menu-{{ $column->id }}">
                            <h1>{{ $column->name }}</h1>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const addColumnBtn = document.getElementById('add-column-btn');
            const inputField = document.getElementById('new-column-input');
            const columnsContainer = document.getElementById('columns-container');
            const newTaskInput = document.getElementById('new-task-input');
            const firstColumnTaskList = document.getElementById('task-list-{{ $board->columns->first()->id }}');

            // Task Items
            const taskMenus = document.querySelectorAll(`[id*="task-list-menu"]`); // Three dots
            const taskItems = document.querySelectorAll(`[id*="task-list-item"]`); // Rows of the table -- change to be more general name

            // Context Menu
            const taskContextMenu = document.getElementById(`task-context-menu`); // Context menu
            const contextSubMenu = document.getElementById(`contextBoardMenu`); // Context sub menu box
            const contextSubMenuChildren = contextSubMenu.children; // Context sub menu buttons

            // Add new column
            addColumnBtn.addEventListener('click', function () {
                inputField.classList.remove('hidden');
                inputField.focus();
                addColumnBtn.style.marginLeft = '20px';
            });

            // Create a new column
            inputField.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const columnName = inputField.value.trim();
                    if (columnName !== '') {
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
                                // const newColumn = document.createElement('div');
                                // newColumn.classList.add('bg-gray-100', 'shadow-lg', 'rounded-lg', 'p-4', 'w-64');
                                // newColumn.innerHTML = `<h2 class="text-xl font-bold">${data.column.name}</h2>`;
                                // columnsContainer.insertBefore(newColumn, document.getElementById('add-column-section'));
                                inputField.value = '';
                                inputField.classList.add('hidden');
                                // addColumnBtn.style.marginLeft = '0';
                                location.reload();
                            } else {
                                console.error('Error adding column:', data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                }
            });

            // Add new task
            newTaskInput.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const taskTitle = newTaskInput.value.trim();
                    if (taskTitle !== '') {
                        fetch('{{ route('tasks.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                title: taskTitle,
                                column_id: {{ $board->columns->first()->id }}
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Insert task into the task list of the first column
                                // const newTask = `<div class="bg-white p-2 my-2 rounded-lg shadow">${data.task.title}</div>`;
                                // firstColumnTaskList.insertAdjacentHTML('beforeend', newTask);
                                newTaskInput.value = '';
                                location.reload();
                            } else {
                                console.error('Error adding task:', data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                }
            });


            // Context Menu ---------------------------------------------------------------
            // Reset context menu on right click anywhere outside
            document.addEventListener('contextmenu', e => {
                taskContextMenu.classList.add('hidden');
                contextSubMenu.classList.add('hidden');

                // Clear temp arr
                selectedTaskItems = [];
            });

            // Reset context menu on left click anywhere outside
            document.addEventListener('click', e => {
                taskContextMenu.classList.add('hidden');
                contextSubMenu.classList.add('hidden');

                // Clear temp arr
                selectedTaskItems = [];
            });


            // MAKE INTO ONE LOOP
            // Show context menu when clicking three dots
            for (let taskMenu of taskMenus) {
                taskMenu.addEventListener('click', e => {
                    e.stopPropagation();

                    const rect = taskMenu.getBoundingClientRect();
                    taskContextMenu.style.left = (window.scrollX + rect.left) + 'px';
                    taskContextMenu.style.top = (window.scrollY + rect.top + rect.height) + 'px';
                    taskContextMenu.classList.remove('hidden');

                    // Set task as selected item
                    selectedTaskItems = [Number(taskMenu.parentElement.parentElement.id.replace(
                        'task-list-item-', ''))];
                });
            }

            // Right clicking functionality
            for (let task of taskItems) {
                task.addEventListener('contextmenu', e => {
                    e.stopPropagation();
                    // Show context menu when right-clicking
                    if (!e.ctrlKey) {
                        taskContextMenu.style.left = (e.pageX) + 'px';
                        taskContextMenu.style.top = (e.pageY + 2) + 'px';
                        taskContextMenu.classList.remove('hidden');

                        // Set task as selected item
                        if (selectedTaskItems.length <= 1) {
                            selectedTaskItems = [Number(task.id.replace('task-list-item-', ''))];
                        }
                        return false;
                    };

                    // Add task to temp array when ctrl right clicking
                    selectedTaskItems.push(Number(task.id.replace('task-list-item-', '')));
                    return false;
                }, false);
            }

            // Call moveTasks function when clicking on sub menu button
            for (let child of contextSubMenuChildren) {
                let subMenuId = Number(child.id.replace('context-board-menu-', ""));
                child.addEventListener('click', e => {
                    moveTasks(subMenuId);
                });
            }

            // Bulk move tasks from backlog to sprint board
            function moveTasks(board_id) {
                fetch('{{ route('backlog.moveTasks') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        task_ids: JSON.stringify(selectedTaskItems),
                        board_id: board_id // Num
                    })
                })
                .then(response => {
                    responseClone = response.clone();
                    return response.json();
                })
                .then(data => {
                    if (data.success) {
                        // Reset Selected task items
                        selectedTaskItems = [];

                        location.reload();

                    } else {
                        console.error('Error moving tasks:', data.message);
                    }

                    // print response for debugging
                }, function(rejectionReason) {
                    console.log('Error parsing JSON from response:', rejectionReason, responseClone);
                    responseClone.text()
                        .then(function(bodyText) {
                            console.log('Received the following instead of valid JSON:', bodyText);
                        });
                });
            }

        });
    </script>

</x-app-layout>
