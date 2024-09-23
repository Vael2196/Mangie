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
        @foreach ($board->columns as $column)
            <h1 id="issues" class='mb-2'>Issues: {{count($column->tasks)}}</h1>
        @endforeach
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

        @foreach($board->columns as $column)
            <x-task-list-board id="task-list">
                    @foreach($column->tasks as $task)
                        <x-task-list-item :task="$task"/>
                    @endforeach
            </x-task-list-board>

            <!-- Task detail -->
            @foreach($column->tasks as $task)
                <div class='hidden' id="task-list-detail-{{$loop->index}}">
                    <x-task-detail :task="$task"/>
                </div>
            @endforeach

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
        @endforeach
    </div>

    <script>
        document.addEventListener('DOMContentLoaded', function () {
            const createTaskButton = document.getElementById('create-task-link');
            const inputTaskField = document.getElementById('input-task-field');
            const taskColumn = document.getElementById('task-list');
            const issuesNo = document.getElementById('issues');

            createTaskButton.addEventListener('click', e => {
                inputTaskField.classList.remove('hidden');
                inputTaskField.focus();
                createTaskButton.classList.add("hidden");
            });

            inputTaskField.addEventListener('focusout', e => {
                createTaskButton.classList.remove("hidden");
                inputTaskField.classList.add('hidden');
            });

            var tbody = taskColumn.children[0].children;
            for (let i = 0; i < tbody.length; i++) {
                const task = tbody[i];
                task.addEventListener('click', e => {
                    const taskDetail = document.getElementById(`task-list-detail-${i}`);
                    taskDetail.classList.toggle('hidden');
                });
            }

            // Add new task
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
                                column_id: {{ $board->columns->first()->id }}
                            })
                        })
                        .then(response => response.json())
                        .then(data => {
                            if (data.success) {
                                // Insert task into the task list of the first column
                                const newTask = `<tr class="px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-600 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded"
                                                    onclick="">
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
        });

    </script>
</x-app-layout>
