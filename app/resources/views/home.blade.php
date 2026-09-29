<x-app-layout>
    <div
        data-task-sync-context="boards"
        data-realtime-board-ids='@json($boards->pluck('id')->values())'
        data-realtime-project-id="{{ $projectId }}"
        data-page-mode="{{ $pageMode }}"
        data-current-user-id="{{ $user->id }}"
        data-board-filter-show-inactive="{{ $boardFilters['showInactive'] ? '1' : '0' }}"
        data-board-filter-owned="{{ in_array('owned', $boardFilters['ownership'], true) ? '1' : '0' }}"
        data-board-filter-shared="{{ in_array('shared', $boardFilters['ownership'], true) ? '1' : '0' }}"
    >
    <x-top-bar
        :title="$pageMode === 'dashboard' ? 'Sprint Dashboard' : 'Home'"
        :user="$user"
    />

    <div
        class="mx-auto max-w-[1600px] px-6 py-8 lg:px-8"
        id="wholePage"
    >
        {{-- <h1 class="text-3xl px-6 font-bold dark:text-white mb-4">Project Boards</h1> --}}
        <div
            class="mb-8 flex flex-col gap-4
                sm:flex-row sm:items-center sm:justify-between"
        >
            <div>
                @if($pageMode === 'dashboard')
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        All sprint boards
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Create, review and manage your project sprints.
                    </p>
                @else
                    <h2 class="text-xl font-bold text-gray-900 dark:text-white">
                        Current work
                    </h2>

                    <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                        Keep track of the sprint your team is currently working on.
                    </p>
                @endif
            </div>


            <div class="flex flex-wrap items-center gap-3">

                <form
                    method="GET"
                    action="{{ $pageMode === 'dashboard' ? route('dashboard') : route('home') }}"
                    class="flex flex-wrap items-center gap-3 rounded-xl
                        border border-gray-200 bg-white px-3 py-2 shadow-sm
                        dark:border-gray-700 dark:bg-gray-900"
                    aria-label="Filter sprint boards"
                >
                    <input type="hidden" name="filters" value="1">

                    <label class="inline-flex cursor-pointer items-center gap-2
                        text-sm font-medium text-gray-600 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="show_inactive"
                            value="1"
                            data-sprint-filter
                            class="rounded border-gray-300 text-indigo-600
                                focus:ring-indigo-500 dark:border-gray-600
                                dark:bg-gray-800"
                            @checked($boardFilters['showInactive'])
                        >
                        Show inactive / completed
                    </label>

                    <span class="hidden h-5 w-px bg-gray-200 dark:bg-gray-700 sm:block"></span>

                    <label class="inline-flex cursor-pointer items-center gap-2
                        text-sm font-medium text-gray-600 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="ownership[]"
                            value="owned"
                            data-sprint-filter
                            class="rounded border-gray-300 text-indigo-600
                                focus:ring-indigo-500 dark:border-gray-600
                                dark:bg-gray-800"
                            @checked(in_array('owned', $boardFilters['ownership'], true))
                        >
                        Created by me
                    </label>

                    <label class="inline-flex cursor-pointer items-center gap-2
                        text-sm font-medium text-gray-600 dark:text-gray-300">
                        <input
                            type="checkbox"
                            name="ownership[]"
                            value="shared"
                            data-sprint-filter
                            class="rounded border-gray-300 text-indigo-600
                                focus:ring-indigo-500 dark:border-gray-600
                                dark:bg-gray-800"
                            @checked(in_array('shared', $boardFilters['ownership'], true))
                        >
                        Shared with me
                    </label>

                    <button
                        type="submit"
                        class="rounded-lg bg-gray-100 px-2.5 py-1.5
                            text-xs font-semibold text-gray-600 transition
                            hover:bg-gray-200 dark:bg-gray-800
                            dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        Apply
                    </button>
                </form>

                <div
                    class="inline-flex rounded-xl border border-gray-200
                        bg-white p-1 shadow-sm
                        dark:border-gray-700 dark:bg-gray-900"
                >
                    <button
                        type="button"
                        id="list-view-button"
                        class="view-toggle flex items-center gap-2
                            rounded-lg px-3 py-2
                            text-sm font-semibold transition"
                    >
                        <span class="material-symbols-rounded text-[19px]">
                            view_list
                        </span>

                        List
                    </button>

                    <button
                        type="button"
                        id="card-view-button"
                        class="view-toggle flex items-center gap-2
                            rounded-lg px-3 py-2
                            text-sm font-semibold transition"
                    >
                        <span class="material-symbols-rounded text-[19px]">
                            grid_view
                        </span>

                        Cards
                    </button>
                </div>


                @if($pageMode === 'dashboard')
                    <button
                        type="button"
                        id="create-board-button"
                        class="inline-flex items-center gap-2 rounded-xl
                            bg-indigo-600 px-4 py-2.5
                            text-sm font-semibold text-white
                            shadow-sm transition
                            hover:bg-indigo-500"
                    >
                        <span class="material-symbols-rounded text-[20px]">
                            add
                        </span>

                        New sprint
                    </button>
                @endif

            </div>
        </div>

        @if($pageMode === 'dashboard')
            <div
                id="create-board-panel"
                class="mb-6 hidden rounded-2xl
                    border border-indigo-200
                    bg-indigo-50/60 p-5
                    dark:border-indigo-900
                    dark:bg-indigo-950/30"
            >
                <form
                    id="new-board-form"
                    method="POST"
                    action="{{ route('boards.store') }}"
                    class="flex flex-col gap-3 sm:flex-row"
                >
                    @csrf

                    <input
                        type="hidden"
                        name="project_id"
                        value="{{ $projectId }}"
                    >

                    <input
                        type="text"
                        name="name"
                        id="board-name"
                        class="min-w-0 flex-1 rounded-xl
                            border-gray-300 bg-white
                            px-4 py-2.5 text-sm
                            shadow-sm
                            focus:border-indigo-500
                            focus:ring-indigo-500
                            dark:border-gray-700
                            dark:bg-gray-900
                            dark:text-white"
                        placeholder="Enter sprint name..."
                        autocomplete="off"
                        required
                    >

                    <button
                        type="submit"
                        class="inline-flex items-center justify-center gap-2
                            rounded-xl bg-indigo-600 px-5 py-2.5
                            text-sm font-semibold text-white
                            transition hover:bg-indigo-500"
                    >
                        <span class="material-symbols-rounded text-[19px]">
                            add
                        </span>

                        Create sprint
                    </button>

                    <button
                        type="button"
                        id="cancel-create-board"
                        class="rounded-xl border border-gray-300
                            bg-white px-4 py-2.5
                            text-sm font-semibold text-gray-600
                            transition hover:bg-gray-50
                            dark:border-gray-700
                            dark:bg-gray-800
                            dark:text-gray-300"
                    >
                        Cancel
                    </button>
                </form>
            </div>
        @endif

        @if (session('success'))
            <div class="bg-green-100 border border-green-400 text-gray-700 px-4 py-3 rounded relative mb-4">
                {{ session('success') }}
            </div>
        @endif

        <div
            id="sprint-empty-state"
            @class([
                'mb-5 rounded-2xl border border-dashed border-gray-300',
                'bg-white/60 px-6 py-10 text-center text-sm text-gray-500',
                'dark:border-gray-700 dark:bg-gray-900/50 dark:text-gray-400',
                'hidden' => $boards->isNotEmpty(),
            ])
        >
            No sprint boards match the selected filters.
        </div>

        <div
            class="card-view-sprint grid grid-cols-1
                gap-5
                sm:grid-cols-2
                xl:grid-cols-3
                2xl:grid-cols-4"
        >
           @foreach($boards as $board)
                <x-show-sprint-board :board="$board"/>
            @endforeach
        </div>

        {{-- List View --}}
        <div class="list-view-sprint hidden">
            <div
                class="overflow-hidden rounded-2xl
                    border border-gray-200 bg-white
                    shadow-sm
                    dark:border-gray-800 dark:bg-gray-900"
            >
                <table class="w-full">
                    <tbody class="divide-y divide-gray-100 dark:divide-gray-800">
                    @foreach($boards as $board)
                        <x-show-sprint-board-list :board="$board"/>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', () => {

            const cardView = document.querySelector('.card-view-sprint');
            const listView = document.querySelector('.list-view-sprint');

            const cardButton = document.getElementById('card-view-button');
            const listButton = document.getElementById('list-view-button');

            const createButton = document.getElementById('create-board-button');
            const createPanel = document.getElementById('create-board-panel');
            const cancelCreateButton = document.getElementById('cancel-create-board');
            const createForm = document.getElementById('new-board-form');
            const boardNameInput = document.getElementById('board-name');

            document.querySelectorAll('[data-sprint-filter]')
                .forEach(input => {
                    input.addEventListener('change', () => {
                        input.form?.requestSubmit();
                    });
                });


            function setView(view) {

                if (!cardView || !listView) {
                    return;
                }

                const cardClasses = [
                    'bg-indigo-600',
                    'text-white'
                ];

                const inactiveClasses = [
                    'text-gray-500',
                    'dark:text-gray-300'
                ];


                if (view === 'list') {

                    cardView.classList.add('hidden');
                    listView.classList.remove('hidden');

                    listButton?.classList.add(...cardClasses);
                    listButton?.classList.remove(...inactiveClasses);

                    cardButton?.classList.remove(...cardClasses);
                    cardButton?.classList.add(...inactiveClasses);

                } else {

                    listView.classList.add('hidden');
                    cardView.classList.remove('hidden');

                    cardButton?.classList.add(...cardClasses);
                    cardButton?.classList.remove(...inactiveClasses);

                    listButton?.classList.remove(...cardClasses);
                    listButton?.classList.add(...inactiveClasses);
                }

                localStorage.setItem('sprintView', view);
            }


            const savedView = localStorage.getItem('sprintView') ?? 'card';

            setView(savedView);


            cardButton?.addEventListener('click', () => {
                setView('card');
            });

            listButton?.addEventListener('click', () => {
                setView('list');
            });


            createButton?.addEventListener('click', () => {

                createPanel?.classList.remove('hidden');

                boardNameInput?.focus();
            });


            cancelCreateButton?.addEventListener('click', () => {

                createPanel?.classList.add('hidden');

                if (boardNameInput) {
                    boardNameInput.value = '';
                }
            });


            createForm?.addEventListener('submit', async (event) => {

                event.preventDefault();

                const boardName = boardNameInput?.value.trim();

                if (!boardName) {
                    return;
                }

                try {

                    const response = await fetch(
                        '{{ route('boards.store') }}',
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
                                name: boardName,
                                project_id: {{ $projectId }}
                            })
                        }
                    );


                    const data = await response.json();


                    if (!response.ok || !data.success) {

                        console.error(
                            'Failed to create sprint:',
                            data
                        );

                        return;
                    }


                    await window.MangieRealtime.applyMutationResponse(data);
                    boardNameInput.value = '';
                    createPanel.classList.add('hidden');

                } catch (error) {

                    console.error(
                        'Error creating sprint:',
                        error
                    );
                }
            });

        });
    </script>
    </div>

</x-app-layout>
