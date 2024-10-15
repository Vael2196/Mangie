<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-top-bar title="Product Backlog" :user="$user"/>
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button
                    class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>{{ Auth::user()->name }}</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd"
                                d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                clip-rule="evenodd" />
                        </svg>
                    </div>
                </button>
            </x-slot>

            <x-slot name="content">
                <x-dropdown-link>
                    {{ __('Profile') }}
                </x-dropdown-link>
                <x-dropdown-link>
                    {{ __('Log Out') }}
                </x-dropdown-link>
                </form>
            </x-slot>
        </x-dropdown>
    </div>
    <div class="px-10 flex flex-col w-[80vw] overflow-auto max-h-[70vh] invisible" id="entirePage">

        {{-- Sprint loading list --}}
        <div class="mb-7" id="sprint-loading-board-list">
            @foreach ($boards as $board)
                @if ($board->id == 1)
                    @continue
                @endif
                <div id="sprint-loading-board-{{ $board->id }}" class="mb-3">
                    @if ($board->completed == 0)
                        <x-sprint-loading-board-card :board="$board" :activeSprints="$activeSprints">
                            @foreach ($board->columns as $column)
                                @foreach ($column->tasks as $task)
                                    <x-task-box :task="$task" />
                                @endforeach
                            @endforeach
                        </x-sprint-loading-board-card>
                    @endif
                </div>
            @endforeach

            {{-- Create sprint input box list view --}}
            <div class="border min-w-full overflow-x-auto rounded p-3 bg-gray-100 hidden" id="create-sprint">
                <div class="flex justify-between mb-2">
                    <input class='border font-semibold text-xl' id="create-sprint-input" type="text"
                        placeholder="Enter Sprint Name"/>
                    <x-secondary-button>Start Sprint</x-secondary-button>
                </div>
                <p class="text-sm">Add tasks here or from the product backlog</p>
            </div>


        </div>

        <div class='flex justify-between mb-2'>
            <h1 id="issues">Issues: {{ count($tasks) }}</h1>
            {{-- Add list to card view dropdown switch here --}}
            <div class="flex space-x-5">

                {{-- Change view switch --}}
                <select id="viewButton" class="border rounded dark:bg-gray-700 dark:text-white hover:cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-500">
                    <option value="list">List View</option>
                    <option value="card" selected>Card View</option>
                </select>

                {{-- Sort Menu --}}
                <x-sort-menu/>

                {{-- Filter Menu --}}
                <x-filter-menu/>

                {{-- Cookie tags --}}
                @foreach($cookies as $key => $value)
                    @if($value)
                        <x-cookie-tag key="{{ $key }}" tag="{{ $value }}" />
                    @endif
                @endforeach

                {{-- Create sprint button --}}
                <div id="create-sprint-button"><x-secondary-button>Create Sprint</x-secondary-button></div>
            </div>
        </div>

        {{-- Product backlog ------------------------------------------------- --}}
        {{-- Card view --}}
        <div id="task-list" class="dark:bg-gray-500">
            <x-task-board>
                @foreach ($backlog->columns as $column)
                    @foreach ($column->tasks as $task)
                        <x-task-box :task="$task" />
                    @endforeach
                @endforeach
            </x-task-board>
        </div>

        {{-- ------------------------------------------------------------------ --}}

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
                    @foreach ($boards as $board)
                        <div class='bg-white border px-4 py-2 text-start leading-5 dark:bg-gray-600 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                            id="context-board-menu-{{ $board->id }}">
                            <h1>{{ $board->name }}</h1>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Link to Form --}}
        <div class="w-full text-start">
            <div id="create-task-link" class="block">
                <div
                    class = 'px-4 py-2 leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-700 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                    <div class = "text-center"><i class="fa-solid fa-plus"></i></div>
                    <p class = "lg:block hidden text-sm">Create Issue</p>
                </div>
            </div>
            {{-- Form for submitting  --}}
            <input id="input-task-field" class="border hidden w-full dark:bg-gray-600 dark:text-white" type="text"
                placeholder="Enter Task Name" class="w-full" />
        </div>
    </div>

    <script>
        // Globals
        let listViews = document.querySelectorAll('.task-list-class');
        let cardViews = document.querySelectorAll('.task-card-class');
        let viewButton = document.getElementById('viewButton');

        // // Load previous state on document load
        // document.addEventListener('DOMContentLoaded', function() {
        //     let viewIndex = localStorage.getItem('viewIndex')
        //     toggleViews(viewIndex); // Load previous state
        // });

        // Main loop
        window.addEventListener('DOMContentLoaded', function() {
            // Hack to make it look less jarring when reloading page
            let entirePage = document.getElementById('entirePage');
            entirePage.classList.remove('invisible');

            // Variables
            var selectedTaskItems = [];
            const createTaskButton = document.getElementById('create-task-link');
            const inputTaskField = document.getElementById('input-task-field');
            const taskColumn = document.getElementById('task-list').lastElementChild.lastElementChild; // Hack

            const issuesNo = document.getElementById('issues');

            //Create Sprint
            const createSprint = document.getElementById('create-sprint'); // Box created when clicking on create sprint button
            const createSprintButton = document.getElementById('create-sprint-button'); // Creates a sprint
            const createSprintInput = document.getElementById('create-sprint-input'); // Input for name of sprint

            // Task Items
            const taskMenus = document.querySelectorAll(`[id*="task-list-menu"]`); // Three dots
            const taskItems = document.querySelectorAll(`[id*="task-list-item"]`); // Rows of the table -- change to be more general name

            // Context Menu
            const taskContextMenu = document.getElementById(`task-context-menu`); // Context menu
            const contextSubMenu = document.getElementById(`contextBoardMenu`); // Context sub menu box
            const contextSubMenuChildren = contextSubMenu.children; // Context sub menu buttons

            // Create task when clicking button ------------------------------------------
            createTaskButton.addEventListener('click', e => {
                inputTaskField.classList.remove('hidden');
                inputTaskField.focus();
                createTaskButton.classList.add("hidden");
            });

            inputTaskField.addEventListener('focusout', e => {
                createTaskButton.classList.remove("hidden");
                inputTaskField.classList.add('hidden');
            });

            // Create sprint when clicking button ------------------------------------------
            createSprintButton.addEventListener('click', e => {
                createSprint.classList.remove('hidden');
                createSprintInput.focus();
            });

            createSprintInput.addEventListener('focusout', e => {
                createSprint.classList.add('hidden');
            });

            // Card to list view
            viewButton.addEventListener('change', e => {
                let view = viewButton.selectedIndex;
                console.log(view);
                // List view
                if (view == 0){
                    window.location.href = `{{ route('backlog.show', 'list')}}`;

                // Card view
                }else if (view == 1){
                    window.location.href = `{{ route('backlog.show', 'card')}}`;
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

            // ----------------------------------------------------------------------------

            // Add new task to the product backlog
            inputTaskField.addEventListener('keypress', function(e) {
                // Check if the key pressed is the Enter key
                if (e.key !== 'Enter') {
                    return;
                }

                // Check if the input field is not empty
                const taskTitle = inputTaskField.value.trim();
                if (taskTitle === '') {
                    return;
                }

                fetch('{{ route('backlog.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            title: taskTitle,
                            column_id: {{ $backlog->columns->first()->id }}
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // // Insert task into the task list of the first column
                            // const newTask = `<tr class = "px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded" id = "task-list-item-${data.task.id}">
                        //                 <td class="py-2 pl-5 border-b-2 min-w-20">${data.task.title}</td>
                        //                 <td class="py-2 border-b-2 w-20">

                        //                 </td>
                        //                 <td class="py-2 border-b-2 w-20">

                        //                 </td>
                        //                 <td class="py-2 border-b-2 w-10">Pr</td>
                        //                 <td class="py-2 border-b-2 w-10">As</td>
                        //                 <td class="py-1 border-b-2 w-10">
                        //                     <div id="task-list-menu-${data.task.id}" class="hover:cursor-pointer hover:bg-gray-200 dark:hover:bg-gray-700 bg-opacity-10 list-none rounded">
                        //                         <i class="fa-solid fa-ellipsis px-2 py-2"></i>
                        //                     </div>
                        //                 </td>
                        //             </tr>
                        //             `;
                            // taskColumn.insertAdjacentHTML('beforeend', newTask);
                            // issuesNo.innerHTML = `Issues: ${parseInt(issuesNo.innerHTML.split(":")[1]) + 1}`;
                            inputTaskField.value = '';
                            location.reload();
                        } else {
                            console.error('Error adding task:', data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });


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
                            // // Get product backlog if endpoint is 1
                            // let endPointBoard;
                            // if(board_id == 1){
                            //     endPointBoard = taskColumn;
                            // } else {
                            //     // Get sprint loading board if endpoint is anything else
                            //     // This is such a hack I dont even know what is happening
                            //     let sprintList = document.getElementById('sprint-loading-board-list');
                            //     let loadingBoardPTag = document.getElementById(`sprint-loading-p-tag-${board_id}`);
                            //     if (sprintList.contains(loadingBoardPTag)){
                            //         loadingBoardPTag.classList.add('hidden');
                            //     }
                            //     let endPointBoard = document.getElementById(`sprint-loading-table-${board_id}`);
                            // }
                            // // Insert task into the task list of the first column
                            // for(let task of data.tasks){
                            //     // Find task by ids
                            //     let taskItem = document.getElementById(`task-list-item-${task.id}`);
                            //     endPointBoard.appendChild(taskItem);
                            // }

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

            // Function to handle the creation of a new board
            createSprintInput.addEventListener('keypress', e => {
                // Check if the key pressed is the Enter key
                if (e.key !== 'Enter') {
                    return;
                }

                // Check if the input field is not empty
                const sprintTitle = createSprintInput.value.trim();
                if (sprintTitle === '') {
                    return;
                }

                // Submit the form via AJAX (using Fetch API)
                fetch('{{ route('boards.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            name: sprintTitle,
                            project_id: 1 // Change later maybe
                        })
                    })
                    .then(response => response.json())
                    .then(data => {
                        if (data.success) {
                            // const sprintList = document.getElementById('sprint-loading-board-list');
                            // // Add the new board dynamically to the page
                            // let new_sprint_board = `
                        //                     <div id="sprint-loading-board-${data.board.id}">
                        //                         <div class="border min-w-full overflow-x-auto rounded p-3 bg-gray-100">
                        //                             <div class="flex justify-between mb-2">
                        //                                 <h1 class='font-semibold text-xl'>${data.board.name}</h1>

                        //                             </div>
                        //                             <p class="text-sm">Add tasks here or from the product backlog</p>
                        //                         </div>
                        //                     </div>
                        //                     `;
                            // sprintList.insertAdjacentHTML('beforeend', new_sprint_board)
                            // Clear the input field
                            createSprintInput.value = '';
                            location.reload();
                        } else {
                            console.error('Error creating board:', data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
</x-app-layout>
