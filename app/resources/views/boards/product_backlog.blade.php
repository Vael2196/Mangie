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
    <div class="px-10 flex flex-col w-[80vw] overflow-x-auto">
        <h1 class='mb-2'>Issues: Number</h1>
        {{-- <x-task-board>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
        </x-task-board> --}}

        <x-task-list-board id="task-list-container">
            <x-task-list-item task=""/>
        </x-task-list-board>

        {{-- Link to Form --}}
        <div id="create-task-link" class="block">
            <x-side-bar-link name="Create Issue" link="/backlog">
                <i class="fa-solid fa-plus"></i>
            </x-side-bar-link >
        </div>
        {{-- Form for submitting  --}}
        <input id="input-task-field" class="border hidden" type="text" name="taskName" placeholder="Enter Task Name" class="w-full"/>
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const createTaskButton = document.getElementById('create-task-link');
            const inputTaskField = document.getElementById('input-task-field');
            const taskListContainer = document.getElementById('task-list-container');

            createTaskButton.addEventListener('click', e => {
                inputTaskField.classList.remove('hidden');
                inputTaskField.focus();
                createTaskButton.classList.add("hidden");
            });

            inputTaskField.addEventListener('focusout', e => {
                createTaskButton.classList.remove("hidden");
                inputTaskField.classList.add('hidden');
            });

            inputTaskField.addEventListener('keypress', e => {
                if (e.key === 'Enter') {
                    const taskName = inputTaskField.value.trim();
                    if(taskName != ""){
                        fetch('{{ route('backlog.store') }}', {
                            method: 'POST',
                            headers: {
                                'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content'),
                                'Content-Type': 'application/json'
                            },
                            body: JSON.stringify({
                                name: taskName,
                                column_id: {{ $board->id }}
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {

                                taskListContainer.insertBefore(, createTaskButton)
                            }else{
                                console.error('Error adding column:', data.message);
                            }
                        })
                        .catch(error => console.error('Error:', error));
                    }
                }
            })
        });

    </script>
</x-app-layout>
