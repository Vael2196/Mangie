<x-app-layout>
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
                        value="1"
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
            class="card-view-sprint grid grid-cols-1
                gap-5
                sm:grid-cols-2
                xl:grid-cols-3
                2xl:grid-cols-4"
        >
            @if ($activeSprints < 1 && Str::contains(url()->current(), '/home'))
                <h>There are no active sprints</h>
            @endif
           @foreach($boards as $board)
                @if($board->id == 1)
                    @continue
                @endif
                @if($pageMode === 'dashboard')
                    <x-show-sprint-board :board="$board"/>
                @elseif($pageMode === 'home')
                    @if($activeSprints && $board->status == 1)
                        <x-show-sprint-board :board="$board"/>
                    @elseif(!$activeSprints && $board->completed == 0)
                        <x-show-sprint-board :board="$board"/>
                    @endif
                @endif
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
                    @if($board->id == 1)
                        @continue
                    @endif
                    @if($pageMode === 'dashboard')
                        <x-show-sprint-board-list :board="$board"/>
                    @elseif($pageMode === 'home')
                        @if($activeSprints && $board->status == 1)
                            <x-show-sprint-board-list :board="$board"/>
                        @elseif(!$activeSprints && $board->completed == 0)
                            <x-show-sprint-board-list :board="$board"/>
                        @endif
                    @endif
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
                                project_id: 1
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


                    window.location.reload();

                } catch (error) {

                    console.error(
                        'Error creating sprint:',
                        error
                    );
                }
            });

        });
    </script>

</x-app-layout>
