<x-app-layout>
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-top-bar title="Product Backlog"/>
    <div class="hidden sm:flex sm:items-center sm:ms-6">
        <x-dropdown align="right" width="48">
            <x-slot name="trigger">
                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-gray-500 dark:text-gray-400 bg-white dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none transition ease-in-out duration-150">
                    <div>{{ Auth::user()->name }}</div>

                    <div class="ms-1">
                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
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
    <div class="px-10 flex flex-col w-[80vw] overflow-auto max-h-[70vh]">

        {{-- Sprint loading list --}}
        <div class="mb-7 space-y-3">
            @foreach($boards as $board)
                @if($board->id == 1)
                    @continue
                @endif
                <x-sprint-loading-board>
                    @foreach($board->columns as $column)
                        @foreach($column->tasks as $task)
                            <x-task-list-item :task="$task"/>
                        @endforeach
                    @endforeach
                </x-sprint-loading-board>
            @endforeach
        </div>

        <div class='flex justify-between mb-2'>
            <h1 id="issues">Issues: {{count($tasks)}}</h1>
            {{-- Add list to card view dropdown switch here --}}
            <div>
                <x-secondary-button>Create Sprint</x-secondary-button>
            </div>
        </div>
        {{-- Product backlog main list --}}
        <div id="task-list">

            {{-- List view --}}
            <x-task-list-board>
                @foreach($tasks as $task)
                    <x-task-list-item :task="$task"/>
                @endforeach
            </x-task-list-board>

            {{-- Board view --}}
            {{--  --}}
            {{--  --}}
        </div>

        {{-- Context Menu --}}
        <div class="hidden absolute z-30" id="task-context-menu">
            <div class="lg:flex lg:items-start">
                {{-- "Move To" Button --}}
                <div class='bg-white border px-4 py-2 items-center leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                    <div class = "text-center" onmouseover="document.getElementById('contextBoardMenu').classList.toggle('hidden')">Move To</div>
                    <div class="text-center"><i class="fa-solid fa-angle-right"></i></div>
                </div>

                {{-- Context sub menu (Buttons for each sprint to move to) --}}
                <div class="hidden shadow-lg" id="contextBoardMenu">
                    @foreach($boards as $board)
                        <div class='bg-white border px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out flex space-x-3'
                            id="context-board-menu-{{$board->id}}">
                            <h1>{{$board->name}}</h1>
                        </div>
                    @endforeach
                </div>
            </div>
        </div>

        {{-- Link to Form --}}
        <div class="w-full text-start">
            <div id="create-task-link" class="block">
                <div class = 'px-4 py-2 leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3'>
                    <div class = "text-center"><i class="fa-solid fa-plus"></i></div>
                    <p class = "lg:block hidden text-sm">Create Issue</p>
                </div>
            </div>
            {{-- Form for submitting  --}}
            <input id="input-task-field" class="border hidden w-full" type="text" placeholder="Enter Task Name" class="w-full"/>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            var selectedTaskItems = [];
            const createTaskButton = document.getElementById('create-task-link');
            const inputTaskField = document.getElementById('input-task-field');
            const taskColumn = document.getElementById('task-list').lastElementChild; // Hack

            const issuesNo = document.getElementById('issues');
            const createSprint = document.getElementById('create-sprint');

            // Task Items
            const taskMenus = document.querySelectorAll(`[id*="task-list-menu"]`); // Three dots
            const taskItems = document.querySelectorAll(`[id*="task-list-item"]`); // Rows of the table -- change to be more general name

            // Context Menu
            const taskContextMenu = document.getElementById(`task-context-menu`); // Context menu
            const contextSubMenu = document.getElementById(`contextBoardMenu`); // Context sub menu box
            const contextSubMenuChildren = contextSubMenu.children; // Context sub menu buttons

            createTaskButton.addEventListener('click', e => {
                inputTaskField.classList.remove('hidden');
                inputTaskField.focus();
                createTaskButton.classList.add("hidden");
            });

            inputTaskField.addEventListener('focusout', e => {
                createTaskButton.classList.remove("hidden");
                inputTaskField.classList.add('hidden');
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
            for(let taskMenu of taskMenus){
                taskMenu.addEventListener('click', e => {
                    e.stopPropagation();

                    const rect = taskMenu.getBoundingClientRect();
                    taskContextMenu.style.left = (window.scrollX + rect.left) + 'px';
                    taskContextMenu.style.top = (window.scrollY + rect.top + rect.height) + 'px';
                    taskContextMenu.classList.remove('hidden');

                    // Set task as selected item
                    selectedTaskItems = [Number(taskMenu.parentElement.parentElement.id.replace('task-list-item', ''))];
                });
            }

            // Right clicking functionality
            for(let task of taskItems){
                task.addEventListener('contextmenu', e=> {
                    e.stopPropagation();
                    // Show context menu when right-clicking
                    if (!e.ctrlKey){
                        taskContextMenu.style.left = (e.pageX) + 'px';
                        taskContextMenu.style.top = (e.pageY + 2) + 'px';
                        taskContextMenu.classList.remove('hidden');

                        // Set task as selected item
                        selectedTaskItems = [Number(task.id.replace('task-list-item', ''))];
                        return false;
                    };

                    // Add task to temp array when ctrl right clicking
                    selectedTaskItems.push(Number(task.id.replace('task-list-item', '')));
                    return false;
                }, false);
            }

            // Call moveTasks function when clicking on sub menu button
            for (let child of contextSubMenuChildren){
                let subMenuId = Number(child.id.replace('context-board-menu-', ""));
                child.addEventListener('click', moveTasks(subMenuId));
            }

            // ----------------------------------------------------------------------------

            // Add new task to the product backlog
            inputTaskField.addEventListener('keypress', function (e) {
                if (e.key === 'Enter') {
                    const taskTitle = inputTaskField.value.trim();
                    if (taskTitle !== '') {
                        fetch('{{ route('backlog.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
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
                                // Insert task into the task list of the first column
                                const newTask = `<tr class="px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded">
                                                    <td class="py-2 pl-5 border-b-2">${data.task.title}</td>
                                                    <td class="py-2 border-b-2 w-20">Epic</td>
                                                    <td class="py-2 border-b-2 w-20">Status</td>
                                                    <td class="py-2 border-b-2 w-20">Priority</td>
                                                    <td class="py-2 border-b-2 w-20">Assigned</td>
                                                </tr>`;
                                taskColumn.insertAdjacentHTML('beforeend', newTask);
                                issuesNo.innerHTML = `Issues: ${parseInt(issuesNo.innerHTML.split(":")[1]) + 1}`;
                                inputTaskField.value = '';
                            } else {
                                console.error('Error adding task:', data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                }
            });


            // Bulk move tasks from backlog to sprint board
            function moveTasks(board_id){
                console.log(board_id);
                console.log(selectedTaskItems);
                console.log(JSON.stringify({task_ids: selectedTaskItems}));
                fetch('{{ route('backlog.moveTasks') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        task_ids: JSON.stringify({task_ids: selectedTaskItems}),// BUG HERE
                        board_id: board_id // Num
                    })
                })
                .then(response => response.json())
                .then(data => {
                    if (data.success) {
                        // // Insert tasks into the
                        // const loadingSprint = document.getElementById(`loading-board-${board_id}`);
                        // const loadingSprintColumn = loadingSprint.lastElementChild;
                        // const loadingSprintIssues = loadingSprint.getElementById('issues');

                        // const newTasks = "";
                        // for(let task of data.task){
                        //     const newTask += `<tr class="px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded">
                        //                     <td class="py-2 pl-5 border-b-2">${task.title}</td>
                        //                     <td class="py-2 border-b-2 w-20">Epic</td>
                        //                     <td class="py-2 border-b-2 w-20">Status</td>
                        //                     <td class="py-2 border-b-2 w-20">Priority</td>
                        //                     <td class="py-2 border-b-2 w-20">Assigned</td>
                        //                 </tr>`;
                        // }
                        // loadingSprintColumn.insertAdjacentHTML('beforeend', newTask);
                        // loadingSprintIssues.innerHTML = `Issues: ${parseInt(issuesNo.innerHTML.split(":")[1]) + 1}`;
                    } else {
                        console.error('Error moving tasks:', data.message);
                    }
                })
            }
        });

    </script>
</x-app-layout>
