{{-- Task Success notification --}}
<div id="task-success-{{$task->id}}" class="hidden absolute top-0 right-0 z-50 w-full bg-green-100 border border-green-400 text-gray-700 px-4 py-3 rounded mb-4">
    Task has been Updated successfully
</div>

{{-- Task Fail notification --}}
<div id="task-fail-{{$task->id}}" class="hidden absolute top-0 right-0 z-50 w-full bg-red-100 border border-red-400 text-gray-700 px-4 py-3 rounded mb-4"></div>

<div class = "absolute top-0 right-0 h-full w-full p-5 flex justify-center items-center z-30 backdrop-blur-sm bg-black bg-opacity-30">
    <div class = "overflow-x-auto rounded lg:px-11 px-2 py-7 w-4/6 h-3/5 bg-white dark:bg-gradient-to-l from-slate-700 to-gray-900 border-2 flex flex-col">
        {{-- Top bar --}}
        <div class="flex justify-between items-center space-x-10 w-full mb-3">

            {{-- Task Title --}}
            <input id="formTitle{{$task->id}}" type="text" placeholder="{{$task->title ? $task->title : "Enter a title"}}" value="{{$task->title}}" class="placeholder-slate-700 text-2xl min-w-24 max-w-lg flex-grow dark:bg-transparent dark:text-white">

            {{-- Select details --}}
            <button type="button" id="saveTaskButton{{$task->id}}" class='inline-flex items-center px-4 py-2 bg-blue-500 dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-white dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-blue-600 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150'>
                Save
            </button>

            {{-- X button --}}
            <span class="flex items-start" id="xButton{{$task->id}}">
                <div class="p-2 hover:cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 bg-opacity-10 rounded">
                    <i class="fa-solid fa-xmark fa-2xl"></i>
                </div>
            </span>
        </div>

        {{-- Main content --}}
        <div class="flex justify-between w-full h-full overflow-y-hidden">
            <div class = "flex flex-col w-1/2 h-full min-w-64">
                <!-- Task info -->
                <div class="mb-10 flex flex-col dark:bg-transparent dark:text-white">
                    <label for="formDescription{{$task->id}}" class="mb-2">Description</label>
                    <textarea id="formDescription{{$task->id}}" rows="5" placeholder="Enter a description" value="{{$task->description}}" class="dark:placeholder-white placeholder-slate-700 dark:bg-transparent dark:text-white">{{$task->description}}</textarea>
                </div>
            </div>

            <!-- Task details -->
            <div class = "flex flex-col w-1/2 h-full items-center mb-2 min-w-64">
                {{-- Details box --}}
                <div class = "dark:text-white border-2 rounded-3 lg:w-3/4 w-10/12 h-3/4 flex flex-col justify-evenly px-3 py-1 mb-10">
                    <h1>Details</h1>

                    {{-- Assignee --}}
                    <div class = "flex justify-between w-full">
                        <p>Assignee</p>
                        <select class = "dark:bg-gray-700 dark:text-white rounded-md border-2 border-white focus:ring-blue-500 focus:border-blue-500 cursor-pointer" id="formAssignee{{$task->id}}">
                            <option value="" selected>No one</option>
                            @foreach ($users as $assignee)
                                <option value="{{$assignee->id}}">{{$assignee->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Column --}}
                    <div class = "flex justify-between w-full">
                        <p>Status</p>
                        <select class = "dark:bg-gray-700 dark:text-white w-50 rounded-md border-2 border-white focus:ring-blue-500 focus:border-blue-500 cursor-pointer" id="formColumn{{$task->id}}">
                            @foreach ($parent_board->columns as $column)
                                @if($task->column_id == $column->id)
                                    <option value="{{$column->id}}" selected>{{$column->name}}</option>
                                @else
                                    <option value="{{$column->id}}">{{$column->name}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Labels --}}
                    <div class = "flex justify-between w-full">
                        <p>Labels</p>
                        <select class = "dark:bg-gray-700 dark:text-white w-50 rounded-md border-2 border-white focus:ring-blue-500 focus:border-blue-500 cursor-pointer" id="formLabels{{$task->id}}">
                            <option value="" selected>Select</option>
                            @foreach (["API", "Backend", "Frontend", "UI/UX", "Database"] as $label)
                                @if($task->labels == $label)
                                    <option value="{{$label}}" selected>{{$label}}</option>
                                @else
                                    <option value="{{$label}}">{{$label}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Priority --}}
                    <div class = "flex justify-between w-full">
                        <p>Priority</p>
                        <select class = "dark:bg-gray-700 dark:text-white w-50 rounded-md border-2 border-white focus:ring-blue-500 focus:border-blue-500 cursor-pointer" id="formPriority{{$task->id}}">
                            <option value="" selected>Select</option>
                            @foreach (["Low", "Medium", "High"] as $priority)
                                @if($task->priority == $priority)
                                    <option value="{{$priority}}" selected>{{$priority}}</option>
                                @else
                                    <option value="{{$priority}}">{{$priority}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    {{-- Sprint board --}}
                    <div class = "flex justify-between w-full">
                        <p>Sprint</p>
                        <p class = "fw-bold px-2 cursor-default dark:text-white">{{$parent_board->name}}</p>
                    </div>

                    {{-- Story point estimate --}}
                    <div class = "flex justify-between w-full">
                        <p>SP ESTIMATE</p>
                        <input type="number" id="formStoryPoint{{$task->id}}" class = "text-end rounded-md w-10 dark:text-white dark:bg-transparent border-2 border-gray-300 focus:ring-blue-500 focus:border-blue-500 cursor-pointer placeholder-slate-700 dark:placeholder-white" placeholder="{{$task->story_points}}"/>
                    </div>

                    {{-- Time log --}}
                    <div class = "flex justify-between w-full dark:text-white">
                        <p>Time log</p>
                        <input type="number" id="formTimeLog{{$task->id}}" class = "text-end rounded-md w-20 dark:text-white dark:bg-transparent border-2 border-gray-300 focus:ring-blue-500 focus:border-blue-500 cursor-pointer placeholder-slate-700 dark:placeholder-white" placeholder="{{$task->time_log}}"/>
                    </div>
                </div>

                {{-- Creation dates --}}
                <div class = "flex flex-col items-start w-75">
                    <p>Created: <span>{{$task->created_at}}</span></p>
                    <p>Updated: <span>{{$task->updated_at}}</span></p>
                </div>
            </div>
        </div>
    </div>
</div>

<script type="module">
    import { getVisibleElements } from '/js/utils.js';

    document.addEventListener('DOMContentLoaded', function () {
        var task_id= Number("<?php echo "$task->id"?>");
        let saveButton = document.getElementById(`saveTaskButton${task_id}`);
        let xButton = document.getElementById(`xButton${task_id}`);
        let taskSuccess = document.getElementById(`task-success-${task_id}`);
        let taskFail = document.getElementById(`task-fail-${task_id}`);
        var initialStoryPoint = document.getElementById(`formStoryPoint${task_id}`).placeholder;
        var initialTimeLog = document.getElementById(`formStoryPoint${task_id}`).placeholder;

        // Remove notif on clicking outside
        document.addEventListener('click', e => {
            taskSuccess.classList.add('hidden');
            taskFail.classList.add('hidden');
        });

        // Save button functionality
        saveButton.addEventListener('click', e => {
            var inputTitle = document.getElementById(`formTitle${task_id}`).value;
            var inputDescription = document.getElementById(`formDescription${task_id}`).value;
            var assignee = document.getElementById(`formAssignee${task_id}`).value;
            var columnId = document.getElementById(`formColumn${task_id}`).value;
            var labels = document.getElementById(`formLabels${task_id}`).value;
            var priority = document.getElementById(`formPriority${task_id}`).value;
            var storyPoint = document.getElementById(`formStoryPoint${task_id}`).value;
            var timeLog = document.getElementById(`formTimeLog${task_id}`).value;

            // Get placeholder is story point not emitted
            if (!Number(storyPoint)){
                storyPoint = initialStoryPoint;
            }

            if(!Number(timeLog)){
                timeLog = initialTimeLog;

            }

            // Submit the form via AJAX (using Fetch API)
            fetch('{{ route('tasks.update') }}', {
                method: 'POST',
                body: JSON.stringify({
                    task_id: task_id,
                    column_id: columnId,
                    title: inputTitle,
                    description: inputDescription,
                    assignee: Number(assignee),
                    labels: labels,
                    priority: priority,
                    storyPoint: Number(storyPoint),
                    timeLog: Number(timeLog),
                }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                    let responseClone = response.clone();
                    return response.json();
                })
            .then(data => {
                if (data.success) {
                    console.log(data.task);

                    // Success message
                    taskSuccess.classList.remove('hidden');
                    taskFail.classList.add('hidden');
                } else {
                    console.error('Error updating task:', data.message);

                    // Error message
                    taskSuccess.classList.add('hidden');
                    taskFail.classList.remove('hidden');
                    taskFail.innerHTML = data.message;
                }
                // Print the response to the console
            }, function (rejectionReason) {
                console.log('Error parsing JSON from response:', rejectionReason, responseClone);
                responseClone.text()
                .then(function (bodyText) {
                    console.log('Received the following instead of valid JSON:', bodyText);
                });
            });
        });

        //  xbutton click behaviour
        xButton.addEventListener('click', e => {
            // Close the task detail view
            // let taskDetail = getVisibleElements(`#task-list-detail-${task_id}`);

            let taskDetail = document.getElementById(`task-list-detail-${task_id}`);
            taskDetail.classList.toggle('hidden');

            location.reload();
        });
    });
</script>
