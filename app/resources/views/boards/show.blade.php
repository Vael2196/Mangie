<x-app-layout>

    <meta name="task-move-base" content="{{ url('/tasks') }}">
    <meta name="task-dnd-context" content="board">
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
                <x-task-column :column="$column" :editable="$board->completed == 0">

                    <div
                        class="task-list space-y-3
                            rounded-xl transition"
                        id="task-list-{{ $column->id }}"
                        data-task-dropzone="true"
                        data-column-id="{{ $column->id }}"
                        data-column-name="{{ $column->name }}"
                    >
                        @foreach($column->tasks as $task)
                            <x-task-box
                                :task="$task"
                                draggable="true"
                                data-dnd-task="true"
                                data-task-id="{{ $task->id }}"
                            />
                        @endforeach
                    </div>

                    @if($board->completed == 0)
                        <div
                            class="add-task-section mt-3"
                            data-column-id="{{ $column->id }}"
                        >
                            <button
                                type="button"
                                class="add-task-btn flex w-full items-center gap-2
                                    rounded-lg px-2.5 py-2
                                    text-sm font-medium text-gray-500
                                    transition
                                    hover:bg-gray-200/80 hover:text-gray-700
                                    dark:text-gray-400
                                    dark:hover:bg-gray-700
                                    dark:hover:text-gray-200"
                            >
                                <span class="material-symbols-rounded text-[19px]">
                                    add
                                </span>

                                Add a task
                            </button>

                            <div class="new-task-editor hidden">
                                <textarea
                                    class="new-task-input min-h-[90px] w-full
                                        resize-none rounded-xl
                                        border border-gray-200 bg-white
                                        px-3 py-2.5 text-sm
                                        text-gray-900 shadow-sm
                                        placeholder:text-gray-400
                                        focus:border-indigo-500
                                        focus:ring-indigo-500
                                        dark:border-gray-700
                                        dark:bg-gray-900
                                        dark:text-white"
                                    maxlength="255"
                                    rows="3"
                                    placeholder="Enter a title for this task..."
                                ></textarea>

                                <div class="mt-2 flex items-center gap-2">

                                    <button
                                        type="button"
                                        class="confirm-add-task
                                            inline-flex items-center
                                            rounded-lg bg-indigo-600
                                            px-3 py-2
                                            text-sm font-semibold text-white
                                            transition
                                            hover:bg-indigo-500
                                            disabled:cursor-not-allowed
                                            disabled:opacity-50"
                                    >
                                        Add task
                                    </button>

                                    <button
                                        type="button"
                                        class="cancel-add-task
                                            flex h-9 w-9 items-center
                                            justify-center rounded-lg
                                            text-gray-500 transition
                                            hover:bg-gray-200
                                            hover:text-gray-700
                                            dark:text-gray-400
                                            dark:hover:bg-gray-700
                                            dark:hover:text-white"
                                        aria-label="Cancel"
                                    >
                                        <span class="material-symbols-rounded text-[21px]">
                                            close
                                        </span>
                                    </button>

                                </div>

                                <p
                                    class="task-error mt-2 hidden
                                        text-xs text-red-600
                                        dark:text-red-400"
                                ></p>
                            </div>
                        </div>
                    @endif

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
        <div
            id="delete-column-modal"
            class="fixed inset-0 z-[120] hidden"
            role="dialog"
            aria-modal="true"
            aria-labelledby="delete-column-title"
        >

            {{-- Backdrop --}}
            <div
                data-delete-column-backdrop
                class="absolute inset-0
                    bg-gray-950/55
                    backdrop-blur-[2px]"
            ></div>


            <div
                class="relative flex min-h-full
                    items-center justify-center
                    p-4"
            >

                <div
                    class="w-full max-w-md
                        rounded-3xl
                        border border-gray-200
                        bg-white p-6
                        shadow-2xl
                        dark:border-gray-700
                        dark:bg-gray-900"
                >

                    <div
                        class="flex items-start gap-4"
                    >

                        <div
                            class="flex h-11 w-11
                                shrink-0 items-center
                                justify-center rounded-xl
                                bg-red-50
                                text-red-600
                                dark:bg-red-950/50
                                dark:text-red-400"
                        >
                            <span
                                class="material-symbols-rounded
                                    text-[23px]"
                            >
                                delete
                            </span>
                        </div>


                        <div class="min-w-0 flex-1">

                            <h3
                                id="delete-column-title"
                                class="text-lg font-bold
                                    text-gray-900
                                    dark:text-white"
                            >
                                Delete column?
                            </h3>

                            <p
                                class="mt-2 text-sm
                                    leading-6
                                    text-gray-500
                                    dark:text-gray-400"
                            >
                                The empty column
                                <span
                                    id="delete-column-name"
                                    class="font-semibold
                                        text-gray-700
                                        dark:text-gray-200"
                                ></span>
                                will be permanently deleted.
                                This cannot be undone.
                            </p>

                        </div>
                    </div>


                    <p
                        id="delete-column-error"
                        class="mt-4 hidden
                            rounded-xl
                            bg-red-50 px-3 py-2
                            text-sm text-red-600
                            dark:bg-red-950/40
                            dark:text-red-400"
                    ></p>


                    <div
                        class="mt-6 flex
                            justify-end gap-2"
                    >

                        <button
                            type="button"
                            id="cancel-delete-column"
                            class="rounded-xl
                                px-4 py-2.5
                                text-sm font-semibold
                                text-gray-600
                                transition
                                hover:bg-gray-100
                                dark:text-gray-300
                                dark:hover:bg-gray-800"
                        >
                            Cancel
                        </button>


                        <button
                            type="button"
                            id="confirm-delete-column"
                            class="inline-flex
                                items-center gap-2
                                rounded-xl
                                bg-red-600
                                px-4 py-2.5
                                text-sm font-semibold
                                text-white
                                shadow-sm transition
                                hover:bg-red-500
                                disabled:cursor-not-allowed
                                disabled:opacity-60"
                        >
                            <span
                                class="material-symbols-rounded
                                    text-[18px]"
                            >
                                delete
                            </span>

                            <span data-delete-label>
                                Delete column
                            </span>
                        </button>

                    </div>

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
            const taskItems = document.querySelectorAll(`[id*="task-list-item"]`);

            var selectedTaskItems = [];
            const taskContextMenu = document.getElementById(`task-context-menu`);
            const contextSubMenu = document.getElementById(`contextBoardMenu`);
            const contextSubMenuChildren = contextSubMenu.children;

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


            // New column handler
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

            const columnMenuWrappers =
                document.querySelectorAll(
                    '[data-column-menu]'
                );

            const columnsBaseUrl =
                @json(url('/columns'));

            const csrfToken =
                document
                    .querySelector(
                        'meta[name="csrf-token"]'
                    )
                    .getAttribute('content');


            function closeAllColumnMenus(
                except = null
            ) {
                columnMenuWrappers.forEach(
                    wrapper => {

                        if (wrapper === except) {
                            return;
                        }

                        const panel =
                            wrapper.querySelector(
                                '[data-column-menu-panel]'
                            );

                        const toggle =
                            wrapper.querySelector(
                                '[data-column-menu-toggle]'
                            );

                        panel.classList.add('hidden');

                        toggle.setAttribute(
                            'aria-expanded',
                            'false'
                        );
                    }
                );
            }


            async function sendColumnRequest(
                url,
                method,
                body = null
            ) {
                const options = {
                    method: method,

                    headers: {
                        'X-CSRF-TOKEN': csrfToken,
                        'Accept': 'application/json',
                        'Content-Type':
                            'application/json',
                    },
                };


                if (body !== null) {
                    options.body =
                        JSON.stringify(body);
                }


                const response =
                    await fetch(url, options);


                let data = {};

                try {
                    data = await response.json();
                } catch (error) {
                    // Response had no JSON body.
                }


                if (
                    !response.ok ||
                    data.success === false
                ) {
                    throw new Error(
                        data.message ??
                        'The column could not be updated.'
                    );
                }


                return data;
            }


            columnMenuWrappers.forEach(
                wrapper => {

                    const columnId =
                        Number(
                            wrapper.dataset.columnId
                        );

                    let columnName =
                        wrapper.dataset.columnName;

                    const toggle =
                        wrapper.querySelector(
                            '[data-column-menu-toggle]'
                        );

                    const panel =
                        wrapper.querySelector(
                            '[data-column-menu-panel]'
                        );

                    const menuError =
                        wrapper.querySelector(
                            '[data-column-menu-error]'
                        );

                    const colourButtons =
                        wrapper.querySelectorAll(
                            '[data-column-color]'
                        );

                    const copyButton =
                        wrapper.querySelector(
                            '[data-copy-column]'
                        );

                    const deleteButton =
                        wrapper.querySelector(
                            '[data-delete-column]'
                        );

                    const columnElement =
                        document.getElementById(
                            `column-${columnId}`
                        );

                    const nameDisplay =
                        columnElement.querySelector(
                            '[data-column-name-display]'
                        );

                    const nameText =
                        columnElement.querySelector(
                            '[data-column-name-text]'
                        );

                    const nameEditor =
                        columnElement.querySelector(
                            '[data-column-name-editor]'
                        );

                    const nameInput =
                        columnElement.querySelector(
                            '[data-column-name-input]'
                        );

                    const nameError =
                        columnElement.querySelector(
                            '[data-column-name-error]'
                        );

                    let savingColumnName = false;

                    function clearColumnNameError() {
                        nameError.textContent = '';

                        nameError.classList.add(
                            'hidden'
                        );
                    }

                    function showColumnNameError(message) {
                        nameError.textContent = message;

                        nameError.classList.remove(
                            'hidden'
                        );
                    }

                    function openColumnNameEditor() {
                        closeAllColumnMenus();

                        clearColumnNameError();

                        nameInput.value =
                            columnName;

                        nameDisplay.classList.add(
                            'hidden'
                        );

                        nameEditor.classList.remove(
                            'hidden'
                        );

                        nameInput.focus();

                        nameInput.select();
                    }


                    function cancelColumnNameEditor() {
                        nameInput.value =
                            columnName;

                        clearColumnNameError();

                        nameEditor.classList.add(
                            'hidden'
                        );

                        nameDisplay.classList.remove(
                            'hidden'
                        );
                    }


                    async function saveColumnName() {

                        if (savingColumnName) {
                            return;
                        }


                        const nextName =
                            nameInput.value.trim();


                        if (!nextName) {
                            showColumnNameError(
                                'Column name cannot be empty.'
                            );

                            nameInput.focus();

                            return;
                        }


                        if (nextName === columnName) {
                            cancelColumnNameEditor();

                            return;
                        }


                        clearColumnNameError();

                        savingColumnName = true;

                        nameInput.disabled = true;


                        try {

                            const data =
                                await sendColumnRequest(
                                    `${columnsBaseUrl}/${columnId}/name`,
                                    'PATCH',
                                    {
                                        name: nextName,
                                    }
                                );


                            columnName =
                                data.column.name;

                            nameText.textContent =
                                columnName;

                            wrapper.dataset.columnName =
                                columnName;

                            columnElement.dataset.columnName =
                                columnName;

                            nameInput.value =
                                columnName;


                            nameEditor.classList.add(
                                'hidden'
                            );

                            nameDisplay.classList.remove(
                                'hidden'
                            );

                        } catch (error) {

                            showColumnNameError(
                                error.message ??
                                'Could not rename column.'
                            );

                            nameInput.focus();

                        } finally {

                            savingColumnName = false;

                            nameInput.disabled = false;

                        }
                    }

                    function clearMenuError() {
                        menuError.textContent = '';

                        menuError.classList.add(
                            'hidden'
                        );
                    }


                    function showMenuError(message) {
                        menuError.textContent =
                            message;

                        menuError.classList.remove(
                            'hidden'
                        );
                    }

                    nameDisplay?.addEventListener(
                        'click',
                        event => {

                            event.stopPropagation();

                            openColumnNameEditor();
                        }
                    );


                    nameInput?.addEventListener(
                        'keydown',
                        event => {

                            if (event.key === 'Enter') {

                                event.preventDefault();

                                saveColumnName();

                                return;
                            }


                            if (event.key === 'Escape') {

                                event.preventDefault();

                                cancelColumnNameEditor();
                            }
                        }
                    );

                    nameInput?.addEventListener(
                        'blur',
                        () => {

                            if (
                                !nameEditor
                                    .classList
                                    .contains('hidden')
                            ) {
                                saveColumnName();
                            }
                        }
                    );

                    toggle.addEventListener(
                        'click',
                        event => {

                            event.stopPropagation();

                            clearMenuError();

                            const wasHidden =
                                panel.classList.contains(
                                    'hidden'
                                );


                            closeAllColumnMenus();


                            if (wasHidden) {
                                panel.classList.remove(
                                    'hidden'
                                );

                                toggle.setAttribute(
                                    'aria-expanded',
                                    'true'
                                );
                            }
                        }
                    );

                    panel.addEventListener(
                        'click',
                        event => {
                            event.stopPropagation();
                        }
                    );

                    colourButtons.forEach(
                        button => {

                            button.addEventListener(
                                'click',
                                async () => {

                                    clearMenuError();

                                    const color =
                                        button.dataset.color;


                                    colourButtons.forEach(
                                        item => {
                                            item.disabled =
                                                true;
                                        }
                                    );


                                    try {

                                        await sendColumnRequest(
                                            `${columnsBaseUrl}/${columnId}/color`,
                                            'PATCH',
                                            {
                                                color: color,
                                            }
                                        );

                                        window.location.reload();

                                    } catch (error) {

                                        showMenuError(
                                            error.message
                                        );

                                        colourButtons.forEach(
                                            item => {
                                                item.disabled =
                                                    false;
                                            }
                                        );
                                    }
                                }
                            );
                        }
                    );

                    copyButton?.addEventListener(
                        'click',
                        async () => {

                            clearMenuError();

                            const label =
                                copyButton.querySelector(
                                    '[data-copy-label]'
                                );

                            copyButton.disabled = true;

                            label.textContent =
                                'Copying...';


                            try {

                                await sendColumnRequest(
                                    `${columnsBaseUrl}/${columnId}/copy`,
                                    'POST'
                                );

                                window.location.reload();

                            } catch (error) {

                                showMenuError(
                                    error.message
                                );

                                copyButton.disabled =
                                    false;

                                label.textContent =
                                    'Copy column';
                            }
                        }
                    );

                    deleteButton?.addEventListener(
                        'click',
                        () => {

                            closeAllColumnMenus();

                            openDeleteColumnModal(
                                columnId,
                                columnName
                            );
                        }
                    );
                }
            );

            document.addEventListener(
                'click',
                () => {
                    closeAllColumnMenus();
                }
            );


            const deleteColumnModal =
                document.getElementById(
                    'delete-column-modal'
                );

            const deleteColumnName =
                document.getElementById(
                    'delete-column-name'
                );

            const deleteColumnError =
                document.getElementById(
                    'delete-column-error'
                );

            const cancelDeleteColumn =
                document.getElementById(
                    'cancel-delete-column'
                );

            const confirmDeleteColumn =
                document.getElementById(
                    'confirm-delete-column'
                );

            const deleteBackdrop =
                deleteColumnModal.querySelector(
                    '[data-delete-column-backdrop]'
                );

            let pendingDeleteColumnId = null;


            function openDeleteColumnModal(
                columnId,
                columnName
            ) {
                pendingDeleteColumnId =
                    columnId;

                deleteColumnName.textContent =
                    `"${columnName}"`;

                deleteColumnError.textContent =
                    '';

                deleteColumnError.classList.add(
                    'hidden'
                );

                deleteColumnModal.classList.remove(
                    'hidden'
                );

                document.body.classList.add(
                    'overflow-hidden'
                );
            }


            function closeDeleteColumnModal() {
                pendingDeleteColumnId = null;

                deleteColumnModal.classList.add(
                    'hidden'
                );

                document.body.classList.remove(
                    'overflow-hidden'
                );
            }


            cancelDeleteColumn.addEventListener(
                'click',
                closeDeleteColumnModal
            );


            deleteBackdrop.addEventListener(
                'click',
                closeDeleteColumnModal
            );


            document.addEventListener(
                'keydown',
                event => {

                    if (
                        event.key === 'Escape' &&
                        !deleteColumnModal
                            .classList
                            .contains('hidden')
                    ) {
                        closeDeleteColumnModal();
                    }
                }
            );


            confirmDeleteColumn.addEventListener(
                'click',
                async () => {

                    if (
                        pendingDeleteColumnId === null
                    ) {
                        return;
                    }


                    deleteColumnError.classList.add(
                        'hidden'
                    );

                    confirmDeleteColumn.disabled =
                        true;

                    const label =
                        confirmDeleteColumn
                            .querySelector(
                                '[data-delete-label]'
                            );

                    label.textContent =
                        'Deleting...';


                    try {

                        await sendColumnRequest(
                            `${columnsBaseUrl}/${pendingDeleteColumnId}`,
                            'DELETE'
                        );

                        window.location.reload();

                    } catch (error) {

                        deleteColumnError.textContent =
                            error.message;

                        deleteColumnError.classList.remove(
                            'hidden'
                        );

                        confirmDeleteColumn.disabled =
                            false;

                        label.textContent =
                            'Delete column';
                    }
                }
            );

            // Add task thingy
            const addTaskSections =
                document.querySelectorAll('.add-task-section');

            addTaskSections.forEach(section => {

                const columnId = Number(section.dataset.columnId);

                const addTaskBtn =
                    section.querySelector('.add-task-btn');

                const editor =
                    section.querySelector('.new-task-editor');

                const input =
                    section.querySelector('.new-task-input');

                const confirmBtn =
                    section.querySelector('.confirm-add-task');

                const cancelBtn =
                    section.querySelector('.cancel-add-task');

                const errorElement =
                    section.querySelector('.task-error');


                function showTaskError(message) {
                    errorElement.textContent = message;
                    errorElement.classList.remove('hidden');
                }


                function clearTaskError() {
                    errorElement.textContent = '';
                    errorElement.classList.add('hidden');
                }


                function openTaskEditor() {
                    addTaskBtn.classList.add('hidden');
                    editor.classList.remove('hidden');

                    clearTaskError();

                    input.focus();
                }


                function closeTaskEditor() {
                    input.value = '';

                    clearTaskError();

                    editor.classList.add('hidden');
                    addTaskBtn.classList.remove('hidden');
                }


                async function createTask() {

                    const title = input.value.trim();

                    if (!title) {
                        showTaskError(
                            'Please enter a task title.'
                        );

                        input.focus();

                        return;
                    }


                    clearTaskError();

                    confirmBtn.disabled = true;


                    try {

                        const response = await fetch(
                            '{{ route('tasks.store') }}',
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
                                    title: title,
                                    column_id: columnId,
                                }),
                            }
                        );


                        const data = await response.json();


                        if (!response.ok || !data.success) {

                            showTaskError(
                                data.message ??
                                'Could not create task.'
                            );

                            return;
                        }


                        /*
                        * Reload just like the existing Add Column
                        * implementation.
                        *
                        * This guarantees the newly created task receives
                        * all of the normal x-task-box functionality,
                        * including its task-detail menu.
                        */
                        window.location.reload();

                    } catch (error) {

                        console.error(
                            'Creating task failed:',
                            error
                        );

                        showTaskError(
                            'Could not create task.'
                        );

                    } finally {

                        confirmBtn.disabled = false;

                    }
                }


                addTaskBtn.addEventListener(
                    'click',
                    openTaskEditor
                );


                cancelBtn.addEventListener(
                    'click',
                    closeTaskEditor
                );


                confirmBtn.addEventListener(
                    'click',
                    createTask
                );


                input.addEventListener(
                    'keydown',
                    event => {

                        /*
                        * Enter       = create
                        * Shift+Enter = newline
                        */
                        if (
                            event.key === 'Enter' &&
                            !event.shiftKey
                        ) {
                            event.preventDefault();

                            createTask();
                        }


                        // Escape = cancel
                        if (event.key === 'Escape') {

                            event.preventDefault();

                            closeTaskEditor();
                        }
                    }
                );

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
