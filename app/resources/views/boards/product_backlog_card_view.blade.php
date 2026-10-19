<x-app-layout>
    <meta name="task-move-base" content="{{ url('/tasks') }}">
    <meta name="task-dnd-context" content="backlog">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    @php
        $realtimeBoardIds =
            $boards
                ->pluck('id')
                ->values()
                ->all();
    @endphp

    <div
        data-task-sync-context="backlog"
        data-backlog-board-id="{{ $backlog->id }}"
        data-realtime-board-ids='@json($realtimeBoardIds)'
        data-realtime-project-id="{{ $projectId }}"
        data-task-criteria
        data-task-criteria-scope="backlog"
        data-priority="{{ $taskCriteria['priority'] }}"
        data-label="{{ $taskCriteria['label'] }}"
        data-sort-field="{{ $taskCriteria['sortField'] }}"
        data-sort-direction="{{ $taskCriteria['sortDirection'] }}"
    >
        <x-top-bar title="Product Backlog" :user="$user"/>
        <div
            class="mx-auto max-w-[1600px]
                px-3 py-5 sm:px-6 sm:py-8 lg:px-8"
            id="entirePage"
        >

            <div class='mb-4 flex flex-wrap items-start justify-between gap-3'>
                <h1
                    class="text-lg font-semibold
                        text-gray-900 dark:text-white"
                >
                    Issues:
                    <span id="issues-count">
                        {{ $backlogIssueCount }}
                    </span>
                </h1>
                {{-- Add list to card view dropdown switch here --}}
                <div class="flex min-w-0 flex-wrap items-center gap-2 sm:gap-3">

                    {{-- Change view switch --}}
                    <div
                        class="inline-flex rounded-xl
                            border border-gray-200
                            bg-white p-1 shadow-sm
                            dark:border-gray-700 dark:bg-gray-900"
                    >
                        <a
                            href="{{ route('backlog.show', 'list') }}"
                            @class([
                                'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition',

                                'bg-indigo-600 text-white'
                                    => request()->route('view') === 'list',

                                'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                                    => request()->route('view') !== 'list',
                            ])
                        >
                            <span class="material-symbols-rounded text-[19px]">
                                view_list
                            </span>

                            List
                        </a>


                        <a
                            href="{{ route('backlog.show', 'card') }}"
                            @class([
                                'flex items-center gap-2 rounded-lg px-3 py-2 text-sm font-semibold transition',

                                'bg-indigo-600 text-white'
                                    => request()->route('view') === 'card',

                                'text-gray-500 hover:bg-gray-100 dark:text-gray-300 dark:hover:bg-gray-800'
                                    => request()->route('view') !== 'card',
                            ])
                        >
                            <span class="material-symbols-rounded text-[19px]">
                                grid_view
                            </span>

                            Cards
                        </a>
                    </div>

                    @php
                        $criteriaScope = 'backlog';
                    @endphp
                    <x-sort-menu :scope="$criteriaScope" />
                    <x-filter-menu
                        :scope="$criteriaScope"
                        :labels="$backlog->labels"
                    />
                    @php
                        $backlogDropColumn =
                            $backlog->columns
                                ->sortBy('position')
                                ->first();
                    @endphp

                    @foreach($cookies as $key => $value)

                        @if($key === 'sort')

                            @if($value[0])
                                <x-sort-tag
                                    :scope="$criteriaScope"
                                    key="{{ $value[0] }}"
                                    direction="{{ $value[1] }}"
                                />
                            @endif

                        @elseif($value)

                            <x-cookie-tag
                                :scope="$criteriaScope"
                                key="{{ $key }}"
                                tag="{{ $value }}"
                            />

                        @endif

                    @endforeach
                    {{-- Create sprint button --}}
                    <div data-create-sprint-button><x-secondary-button>Create Sprint</x-secondary-button></div>
                </div>
            </div>

            @if($sharedBacklogs->isNotEmpty())
                <nav aria-label="Shared backlogs" class="mb-4 flex flex-wrap items-center gap-2 text-sm">
                    <span class="text-gray-500 dark:text-gray-400">Shared backlogs:</span>
                    @foreach($sharedBacklogs as $sharedBacklog)
                        <a href="{{ route('boards.show', $sharedBacklog) }}" class="rounded-lg border border-gray-200 px-3 py-1.5 font-medium text-indigo-600 hover:bg-indigo-50 dark:border-gray-700 dark:text-indigo-300 dark:hover:bg-gray-800">{{ $sharedBacklog->name }}</a>
                    @endforeach
                </nav>
            @endif

            {{-- Product backlog ------------------------------------------------- --}}
            {{-- Card view --}}

            <div id="task-list" class="dark:bg-gray-500">
                <x-task-board
                    data-task-dropzone="true"
                    data-task-list
                    data-column-id="{{ $backlogDropColumn?->id ?? 0 }}"
                    data-board-id="{{ $backlog->id }}"
                    data-can-write="{{ $canEditBacklog ? 'true' : 'false' }}"
                    data-reorder="false"
                >
                    @foreach ($backlog->columns as $column)
                        @foreach ($column->tasks as $task)

                            <x-task-box
                                :task="$task"
                                data-task-id="{{ $task->id }}"
                                data-column-id="{{ $task->column_id }}"
                            />

                        @endforeach
                    @endforeach
                </x-task-board>
            </div>

            {{-- ------------------------------------------------------------------ --}}

            {{-- Link to Form --}}
            <div class="w-full text-start">
                <div id="create-task-link" class="{{ $canEditBacklog ? 'block' : 'hidden' }}">
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

            {{-- Sprints --}}
            <section class="mt-10">
            <div
                class="mb-4 flex
                    items-center justify-between"
            >
                <div>
                    <p
                        class="text-xs font-semibold
                            uppercase tracking-wider
                            text-indigo-600
                            dark:text-indigo-400"
                    >
                        Planning
                    </p>

                    <h2
                        class="mt-1 text-xl font-bold
                            text-gray-900
                            dark:text-white"
                    >
                        Sprints
                    </h2>
                </div>
                <div data-create-sprint-button>
                        <x-secondary-button>
                            Create Sprint
                        </x-secondary-button>
                </div>
            </div>
            {{-- Sprint loading list --}}
                <div class="mb-7" id="sprint-loading-board-list">
                    @foreach ($boards as $board)
                        @php
                            $sprintDropColumn =
                                $board->columns
                                    ->firstWhere('name', 'TO DO')
                                ?? $board->columns
                                    ->sortBy('position')
                                    ->first();
                        @endphp
                        @if ($board->isBacklog())
                            @continue
                        @endif
                        <div id="sprint-loading-board-{{ $board->id }}" class="mb-3 sprint-board-status-{{$board->status}}">
                            @if ($board->completed == 0)
                                <x-sprint-loading-board-card
                                    :board="$board"
                                    :activeSprints="$activeSprints"
                                    data-task-dropzone="true"
                                    data-task-list
                                    data-column-id="{{ $sprintDropColumn?->id ?? 0 }}"
                                    data-board-id="{{ $board->id }}"
                                    data-reorder="false"
                                >
                                    @foreach ($board->columns as $column)
                                        @foreach ($column->tasks as $task)
                                            <x-task-box
                                                :task="$task"
                                                data-task-id="{{ $task->id }}"
                                                data-column-id="{{ $task->column_id }}"
                                            />
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
                        </div>
                        <p class="text-sm">Add tasks here or from the product backlog</p>
                    </div>


                </div>
            </section>
        </div>
    </div>

    <script>
        // Globals
        let listViews = document.querySelectorAll('.task-list-class');
        let cardViews = document.querySelectorAll('.task-card-class');
        // let viewButton = document.getElementById('viewButton');

        // // Load previous state on document load
        // document.addEventListener('DOMContentLoaded', function() {
        //     let viewIndex = localStorage.getItem('viewIndex')
        //     toggleViews(viewIndex); // Load previous state
        // });

        // Main loop
        window.addEventListener('DOMContentLoaded', function() {
            // Hack to make it look less jarring when reloading page
            // let entirePage = document.getElementById('entirePage');
            // entirePage.classList.remove('invisible');

            // Variables
            const createTaskButton = document.getElementById('create-task-link');
            const inputTaskField = document.getElementById('input-task-field');
            const taskColumn = document.getElementById('task-list').lastElementChild.lastElementChild; // Hack

            const issuesNo = document.getElementById('issues');

            //Create Sprint
            const createSprint = document.getElementById('create-sprint'); // Box created when clicking on create sprint button
            const createSprintButtons = document.querySelectorAll('[data-create-sprint-button]');
            const createSprintInput = document.getElementById('create-sprint-input'); // Input for name of sprint

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
            createSprintButtons.forEach(button => {
                button.addEventListener('click', () => {
                    createSprint.classList.remove('hidden');
                    createSprintInput.focus();
                });
            });

            createSprintInput.addEventListener('focusout', e => {
                createSprint.classList.add('hidden');
            });

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

                fetch('{{ route('tasks.store') }}', {
                        method: 'POST',
                        headers: {
                            'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]')
                                .getAttribute('content'),
                            'Content-Type': 'application/json'
                        },
                        body: JSON.stringify({
                            title: taskTitle,
                            column_id: {{ $backlog->columns->first()?->id ?? 0 }}
                        })
                    })
                    .then(response => response.json())
                    .then(async data => {
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
                            await window.MangieRealtime.applyMutationResponse(data);
                        } else {
                            console.error('Error adding task:', data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });


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
                            project_id: {{ $projectId }}
                        })
                    })
                    .then(response => response.json())
                    .then(async data => {
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
                            await window.MangieRealtime.applyMutationResponse(data);
                        } else {
                            console.error('Error creating board:', data.message);
                        }
                    })
                    .catch(error => console.error('Error:', error));
            });
        });
    </script>
</x-app-layout>
