<x-app-layout>

    <!-- Meta tag for CSRF token -->
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <x-top-bar :title="$board->name" :user="$user"/>

    <div class="mx-auto max-w-[1600px] px-6 py-8 lg:px-8">
        <div class="flex flex-col">

            <div
                class="flex flex-col gap-5 rounded-2xl
                    border border-gray-200 bg-white
                    p-5 shadow-sm
                    lg:flex-row lg:items-center lg:justify-between
                    dark:border-gray-800 dark:bg-gray-900"
            >

                <div class="min-w-0 flex-1">
                    <p
                        class="text-xs font-semibold uppercase tracking-wider
                            text-indigo-600 dark:text-indigo-400"
                    >
                        Sprint goal
                    </p>

                    <p
                        class="mt-2 max-w-3xl text-sm leading-6
                            text-gray-600 dark:text-gray-300"
                    >
                        @if ($board->status == 1 || $board->completed == 1)
                            {{ $board->sprint_goal }}
                        @else
                            No sprint goal has been set yet.
                        @endif
                    </p>
                </div>

                <div class="flex space-x-8 items-center">

                    @if ($board->completed == 0)
                        <x-sprint-start-details :board="$board" :daysLeft="$daysLeft"/>
                    @endif


                    {{-- <select id="activateSprint" class="bg-white dark:bg-gray-700 dark:text-white rounded-lg p-2">
                        @foreach (['INACTIVE', 'ACTIVE'] as $status)
                            @if ($board->status == 1) {
                                <option value={{$status}} selected>{{ $status }}</option>
                            } @else {
                                <option value={{$status}}>{{$status}}</option>
                            }
                            @endif
                        @endforeach
                    </select> --}}

                    @if ($board->status == 1)
                        <p class="lg:block">
                            @if($daysLeft != null && $daysLeft > 0)
                                {{ $daysLeft }} days left
                            @elseif($daysLeft == 0)
                                Sprint ends today
                            @else
                                Sprint has ended
                            @endif
                        </p>
                    @elseif ($board->completed == 1)
                        <p class="lg:block hidden">Sprint is completed</p>
                    @else
                        <p class="lg:block hidden">Sprint is not active</p>
                    @endif

                    @if ($board->completed == 0 && $board->status == 1)
                        <form method="POST" action="{{ route('boards.complete', $board->id) }}" onsubmit="return confirm('Are you sure you want to complete the sprint early?')">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                Complete Sprint
                            </button>
                        </form>
                    @endif

                    @if ($board->completed == 1)
                        <form method="POST" action="{{ route('boards.burndownChart', $board->id) }}">
                            @csrf
                            <button type="submit" class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150">
                                Burndown Chart
                            </button>
                        </form>
                    @endif

                    <a class="hover:cursor-pointer"><i class="fa-solid fa-ellipsis"></i></a>
                </div>
            </div>
            <div class="flex space-x-3">
                <div class="flex flex-wrap items-center gap-3">

                    {{-- User search --}}
                    <div class="relative">
                        <input
                            type="text"
                            id="user-input"
                            class="w-64 rounded-xl border border-gray-300
                                bg-white px-3.5 py-2.5 text-sm
                                text-gray-900 shadow-sm
                                placeholder:text-gray-400
                                focus:border-indigo-500 focus:ring-indigo-500
                                dark:border-gray-700 dark:bg-gray-900
                                dark:text-white dark:placeholder:text-gray-500"
                            placeholder="Add a participant..."
                            autocomplete="off"
                        >

                        <ul
                            id="user-dropdown"
                            class="absolute left-0 top-full z-50 mt-2 hidden
                                max-h-56 w-72 overflow-y-auto
                                rounded-xl border border-gray-200
                                bg-white p-1.5 shadow-xl
                                dark:border-gray-700 dark:bg-gray-800"
                        ></ul>

                        <p
                            id="user-error"
                            class="mt-1.5 hidden text-sm text-red-600 dark:text-red-400"
                        ></p>
                    </div>


                    {{-- Existing members --}}
                    <ul id="user-list" class="flex -space-x-2">
                        @foreach($board->users as $boardUser)
                            <li
                                class="flex h-9 w-9 items-center justify-center
                                    rounded-full border-2 border-white
                                    bg-indigo-100 text-indigo-600 shadow-sm
                                    dark:border-gray-900 dark:bg-indigo-950
                                    dark:text-indigo-400"
                                title="{{ $boardUser->name }}"
                                data-user-id="{{ $boardUser->id }}"
                            >
                                <span class="material-symbols-rounded text-[22px]">
                                    account_circle
                                </span>
                            </li>
                        @endforeach
                    </ul>

                </div>

                @php
                    $criteriaScope = 'board_' . $board->id;
                @endphp
                <x-sort-menu :scope="$criteriaScope" />
                <x-filter-menu :scope="$criteriaScope" />

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
            </div>
        </div>

        <div
            id="columns-container"
            class="mt-6 flex max-w-full flex-nowrap
                items-start gap-5 overflow-x-auto
                rounded-2xl border border-gray-200
                bg-gradient-to-br from-gray-50 to-indigo-50/40
                p-5 pb-7
                dark:border-gray-800
                dark:from-gray-950 dark:to-indigo-950/20"
        >
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
                    {{-- @if ($loop->first)
                        <!-- Input field for adding a new task in the first column -->
                        <div class="mt-2">
                            <input type="text" id="new-task-input" class="bg-white shadow-inner rounded-lg p-2 w-full dark:bg-gray-500 dark:text-white" placeholder="Enter new task" />
                        </div>
                    @endif --}}
                </x-task-column>
            @endforeach

            <!-- Option to add new columns -->
            <div
                id="add-column-section"
                class="w-72 shrink-0"
            >
                {{-- Initial button --}}
                <button
                    type="button"
                    id="add-column-btn"
                    class="flex w-full items-center justify-center gap-2
                        rounded-xl border-2 border-dashed
                        border-gray-300 bg-white/50
                        px-4 py-3 text-sm font-semibold
                        text-gray-500 transition
                        hover:border-indigo-300
                        hover:bg-indigo-50
                        hover:text-indigo-600
                        dark:border-gray-700
                        dark:bg-gray-900/40
                        dark:text-gray-400
                        dark:hover:border-indigo-700
                        dark:hover:bg-indigo-950/30
                        dark:hover:text-indigo-400"
                >
                    <span class="material-symbols-rounded text-[20px]">
                        add
                    </span>

                    Add column
                </button>


                {{-- Replacement editor --}}
                <div
                    id="new-column-editor"
                    class="hidden rounded-2xl
                        border border-gray-200
                        bg-gray-100/80 p-3 shadow-sm
                        dark:border-gray-700
                        dark:bg-gray-800/80"
                >
                    <input
                        id="new-column-input"
                        type="text"
                        class="w-full rounded-xl
                            border border-gray-300
                            bg-white px-3.5 py-2.5
                            text-sm text-gray-900 shadow-sm
                            placeholder:text-gray-400
                            focus:border-indigo-500
                            focus:ring-indigo-500
                            dark:border-gray-700
                            dark:bg-gray-900
                            dark:text-white"
                        placeholder="Column name..."
                        autocomplete="off"
                    >

                    <div class="mt-3 flex items-center gap-2">
                        <button
                            type="button"
                            id="confirm-add-column"
                            class="inline-flex items-center gap-1.5
                                rounded-lg bg-indigo-600
                                px-3 py-2 text-sm font-semibold
                                text-white transition
                                hover:bg-indigo-500"
                        >
                            <span class="material-symbols-rounded text-[18px]">
                                add
                            </span>

                            Add
                        </button>

                        <button
                            type="button"
                            id="cancel-add-column"
                            class="rounded-lg px-3 py-2
                                text-sm font-semibold text-gray-500
                                transition hover:bg-gray-200
                                dark:text-gray-400
                                dark:hover:bg-gray-700"
                        >
                            Cancel
                        </button>
                    </div>

                    <p
                        id="new-column-error"
                        class="mt-2 hidden text-xs
                            text-red-600 dark:text-red-400"
                    ></p>
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
        // Show sprint details view when clicking on the activate sprint button
        // document.getElementById('activateSprint').addEventListener('click', function () {
        //     document.getElementById('boardDetailsModal').classList.toggle('hidden');
        // });

        // let activeSprint = document.getElementById('activateSprint');

        // // function toggleView(activateSprint) {
        // //     if (viewOption == 0) {
        // //         viewSelect.value = 'INACTIVE';
        // //     } else if (viewOption == 1) {
        // //         viewSelect.value = 'ACTIVE';
        // //     }
        // // }

        // document.getElementById('activateSprint').addEventListener('change', function () {
        //     var status = this.value;

        //     if (status === "ACTIVE") {
        //         status = 1;
        //     } else {
        //         status = 0;
        //     }

        //     const board_id = {{ $board->id }};

        //     fetch('{{ route('boards.updateStatus')}}', {
        //         method: 'POST',
        //         headers: {
        //             'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
        //             'Content-Type': 'application/json'
        //         },
        //         body: JSON.stringify({
        //             status: status,
        //             board_id: board_id
        //         })
        //     })
        //     .then(response => {
        //             let responseClone = response.clone();
        //             return response.json();
        //         })
        //     .then(data => {
        //         if (data.success) {
        //             console.log('Board status updated successfully');
        //         } else {
        //             console.error('Error updating board status:', data.message);
        //         }
        //     })
        //     .catch(error => console.error('Error:', error));

        // })

        document.addEventListener('DOMContentLoaded', () => {
            const userInput = document.getElementById('user-input');
            const userDropdown = document.getElementById('user-dropdown');
            const userList = document.getElementById('user-list');
            const userError = document.getElementById('user-error');

            if (!userInput || !userDropdown || !userList) {
                return;
            }

            const existingUserIds = new Set(
                [...userList.querySelectorAll('[data-user-id]')]
                    .map(element => Number(element.dataset.userId))
            );

            let availableUsers = [];
            let selectedUser = null;
            let searchTimeout = null;


            function showError(message) {
                userError.textContent = message;
                userError.classList.remove('hidden');
            }


            function clearError() {
                userError.textContent = '';
                userError.classList.add('hidden');
            }


            function hideDropdown() {
                userDropdown.classList.add('hidden');
            }


            function renderUsers(users) {
                userDropdown.innerHTML = '';

                if (!users.length) {
                    const emptyItem = document.createElement('li');

                    emptyItem.className =
                        'px-3 py-2 text-sm text-gray-500 dark:text-gray-400';

                    emptyItem.textContent = 'No users found';

                    userDropdown.appendChild(emptyItem);
                    userDropdown.classList.remove('hidden');

                    return;
                }

                users.forEach(user => {
                    const alreadyAdded = existingUserIds.has(Number(user.id));

                    const item = document.createElement('li');

                    item.className =
                        'flex items-center justify-between rounded-lg px-3 py-2 ' +
                        'text-sm transition ' +
                        (alreadyAdded
                            ? 'cursor-default text-gray-400 dark:text-gray-500'
                            : 'cursor-pointer text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 ' +
                            'dark:text-gray-200 dark:hover:bg-indigo-950/50 dark:hover:text-indigo-300'
                        );

                    const name = document.createElement('span');
                    name.textContent = user.name;

                    item.appendChild(name);

                    if (alreadyAdded) {
                        const status = document.createElement('span');

                        status.className =
                            'text-xs font-medium text-gray-400';

                        status.textContent = 'Added';

                        item.appendChild(status);
                    } else {
                        item.addEventListener('click', async event => {
                            event.stopPropagation();

                            selectedUser = user;
                            userInput.value = user.name;

                            clearError();
                            hideDropdown();

                            await addUser(user);
                        });
                    }

                    userDropdown.appendChild(item);
                });

                userDropdown.classList.remove('hidden');
            }


            async function fetchUsers(query = '') {
                try {
                    const response = await fetch(
                        '{{ route('users.search') }}',
                        {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,

                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },

                            body: JSON.stringify({
                                query: query
                            })
                        }
                    );

                    if (!response.ok) {
                        throw new Error(
                            `User search failed with status ${response.status}`
                        );
                    }

                    const data = await response.json();

                    availableUsers = data.success
                        ? data.users
                        : [];

                    renderUsers(availableUsers);

                } catch (error) {
                    console.error('User search failed:', error);

                    hideDropdown();
                    showError('Could not load users.');
                }
            }


            async function addUser(user) {
                clearError();

                try {
                    const response = await fetch(
                        '{{ route('boards.addUser', ['board' => $board->id]) }}',
                        {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    document.querySelector(
                                        'meta[name="csrf-token"]'
                                    ).content,

                                'Content-Type': 'application/json',
                                'Accept': 'application/json'
                            },

                            body: JSON.stringify({
                                user_id: user.id
                            })
                        }
                    );

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        showError(
                            data.message ?? 'Could not add this participant.'
                        );

                        return;
                    }


                    existingUserIds.add(Number(user.id));


                    const newIcon = document.createElement('li');

                    newIcon.dataset.userId = user.id;
                    newIcon.title = user.name;

                    newIcon.className =
                        'flex h-9 w-9 items-center justify-center ' +
                        'rounded-full border-2 border-white ' +
                        'bg-indigo-100 text-indigo-600 shadow-sm ' +
                        'dark:border-gray-900 dark:bg-indigo-950 ' +
                        'dark:text-indigo-400';

                    newIcon.innerHTML = `
                        <span class="material-symbols-rounded text-[22px]">
                            account_circle
                        </span>
                    `;

                    userList.appendChild(newIcon);

                    userInput.value = '';
                    selectedUser = null;

                    hideDropdown();

                } catch (error) {
                    console.error('Adding participant failed:', error);

                    showError('Could not add this participant.');
                }
            }


            userInput.addEventListener('focus', () => {
                fetchUsers(userInput.value.trim());
            });


            userInput.addEventListener('input', () => {
                selectedUser = null;
                clearError();

                clearTimeout(searchTimeout);

                searchTimeout = setTimeout(() => {
                    fetchUsers(userInput.value.trim());
                }, 200);
            });


            userInput.addEventListener('keydown', async event => {
                if (event.key !== 'Enter') {
                    return;
                }

                event.preventDefault();

                const typedName = userInput.value.trim();

                if (!typedName) {
                    showError('Please choose a user.');

                    return;
                }


                let user =
                    selectedUser &&
                    selectedUser.name.toLowerCase() === typedName.toLowerCase()
                        ? selectedUser
                        : availableUsers.find(
                            candidate =>
                                candidate.name.toLowerCase() ===
                                typedName.toLowerCase()
                        );


                /*
                * If the list hasn't caught up with the typing yet,
                * search once more before treating the value as invalid.
                */
                if (!user) {
                    try {
                        const response = await fetch(
                            '{{ route('users.search') }}',
                            {
                                method: 'POST',

                                headers: {
                                    'X-CSRF-TOKEN':
                                        document.querySelector(
                                            'meta[name="csrf-token"]'
                                        ).content,

                                    'Content-Type': 'application/json',
                                    'Accept': 'application/json'
                                },

                                body: JSON.stringify({
                                    query: typedName
                                })
                            }
                        );

                        const data = await response.json();

                        user = data.users?.find(
                            candidate =>
                                candidate.name.toLowerCase() ===
                                typedName.toLowerCase()
                        );

                    } catch (error) {
                        console.error(error);
                    }
                }


                if (!user) {
                    showError('Please select a valid user from the list.');

                    return;
                }


                if (existingUserIds.has(Number(user.id))) {
                    showError('This user is already on the board.');

                    return;
                }


                await addUser(user);
            });


            document.addEventListener('click', event => {
                if (
                    event.target !== userInput &&
                    !userDropdown.contains(event.target)
                ) {
                    hideDropdown();
                }
            });
        });


        document.addEventListener('DOMContentLoaded', function () {
            const columnsContainer = document.getElementById('columns-container');
            const newTaskInput = document.getElementById('new-task-input');
            const firstColumnTaskList = document.getElementById('task-list-{{ $board->columns->first()->id }}');

            // Task Items
            const taskItems = document.querySelectorAll(`[id*="task-list-item"]`); // Rows of the table -- change to be more general name

            // Context Menu
            var selectedTaskItems = [];
            const taskContextMenu = document.getElementById(`task-context-menu`); // Context menu
            const contextSubMenu = document.getElementById(`contextBoardMenu`); // Context sub menu box
            const contextSubMenuChildren = contextSubMenu.children; // Context sub menu buttons

            // Add new column
            const addColumnBtn = document.getElementById('add-column-btn');

            const columnEditor = document.getElementById('new-column-editor');

            const inputField = document.getElementById('new-column-input');

            const confirmColumnBtn = document.getElementById('confirm-add-column');

            const cancelColumnBtn = document.getElementById('cancel-add-column');

            const columnError = document.getElementById('new-column-error');


            function openColumnEditor() {
                addColumnBtn.classList.add('hidden');

                columnEditor.classList.remove('hidden');

                inputField.focus();
            }


            function closeColumnEditor() {
                inputField.value = '';

                columnError.textContent = '';
                columnError.classList.add('hidden');

                columnEditor.classList.add('hidden');

                addColumnBtn.classList.remove('hidden');
            }


            async function createColumn() {
                const columnName = inputField.value.trim();

                if (!columnName) {
                    columnError.textContent =
                        'Please enter a column name.';

                    columnError.classList.remove('hidden');

                    return;
                }

                columnError.classList.add('hidden');

                try {
                    const response = await fetch(
                        '{{ route('columns.store') }}',
                        {
                            method: 'POST',

                            headers: {
                                'X-CSRF-TOKEN':
                                    document
                                        .querySelector(
                                            'meta[name="csrf-token"]'
                                        )
                                        .getAttribute('content'),

                                'Content-Type': 'application/json',
                                'Accept': 'application/json',
                            },

                            body: JSON.stringify({
                                name: columnName,
                                board_id: {{ $board->id }},
                            }),
                        }
                    );

                    const data = await response.json();

                    if (!response.ok || !data.success) {
                        columnError.textContent =
                            data.message ??
                            'Could not create column.';

                        columnError.classList.remove('hidden');

                        return;
                    }

                    window.location.reload();

                } catch (error) {
                    console.error(
                        'Creating column failed:',
                        error
                    );

                    columnError.textContent =
                        'Could not create column.';

                    columnError.classList.remove('hidden');
                }
            }


            addColumnBtn?.addEventListener(
                'click',
                openColumnEditor
            );


            cancelColumnBtn?.addEventListener(
                'click',
                closeColumnEditor
            );


            confirmColumnBtn?.addEventListener(
                'click',
                createColumn
            );


            inputField?.addEventListener(
                'keydown',
                event => {
                    if (event.key === 'Enter') {
                        event.preventDefault();

                        createColumn();
                    }

                    if (event.key === 'Escape') {
                        event.preventDefault();

                        closeColumnEditor();
                    }
                }
            );

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
            // newTaskInput.addEventListener('keypress', function (e) {
            //     if (e.key === 'Enter') {
            //         const taskTitle = newTaskInput.value.trim();
            //         if (taskTitle !== '') {
            //             fetch('{{ route('tasks.store') }}', {
            //                 method: 'POST',
            //                 headers: {
            //                     'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
            //                     'Content-Type': 'application/json'
            //                 },
            //                 body: JSON.stringify({
            //                     title: taskTitle,
            //                     column_id: {{ $board->columns->first()->id }}
            //                 })
            //             })
            //             .then(response => response.json())
            //             .then(data => {
            //                 if (data.success) {
            //                     // Insert task into the task list of the first column
            //                     // const newTask = `<div class="bg-white p-2 my-2 rounded-lg shadow">${data.task.title}</div>`;
            //                     // firstColumnTaskList.insertAdjacentHTML('beforeend', newTask);
            //                     newTaskInput.value = '';
            //                     location.reload();
            //                 } else {
            //                     console.error('Error adding task:', data.message);
            //                 }
            //             })
            //             .catch(error => console.error('Error:', error));
            //         }
            //     }
            // });


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
            function moveTasks(column_id) {
                fetch('{{ route('boards.moveTasks') }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute(
                            'content'),
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({
                        task_ids: JSON.stringify(selectedTaskItems),
                        column_id: column_id // Num
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
