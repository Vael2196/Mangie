<div class = "absolute top-0 right-0 h-full w-full p-5 flex justify-center items-center z-30 backdrop-blur-sm bg-black bg-opacity-30">
    <div class = "overflow-x-auto rounded lg:px-11 px-2 lg:py-7 py-3 w-4/6 h-3/5 bg-white dark:bg-gradient-to-l from-slate-700 to-gray-900 border-2 flex flex-col">
        {{-- Top bar --}}
        <div class="flex justify-between items-center space-x-10 w-full mb-3">

            {{-- Task Title --}}
            <input id="formTitle{{$task->id}}" type="text" placeholder="{{$task->title ? $task->title : "Enter a title"}}" value="{{$task->title}}" class="placeholder-slate-700 text-xl min-w-24 flex-grow">

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
                <div class="mb-10 flex flex-col">
                    <label for="formDescription{{$task->id}}" class="mb-2">Description</label>
                    <textarea id="formDescription{{$task->id}}" rows="3" placeholder="{{$task->description ? $task->description : "Enter a description"}}" value="{{$task->description}}" class="placeholder-slate-700"></textarea>
                </div>
{{--
                <!-- Task activity -->
                <div>
                    <h3 class = "mb-4">Activity</h3>

                    <!-- Tab icons -->
                    <div class = "flex justify-between w-100">
                        <div class = "flex w-75"
                            <h5 class = "py-1" >Show:</h5>
                            <div class = "flex w-100 justify-center">
                                <p class = "btn btn-outline-light btn-sm me-3">ALL</p>
                                <p class = "btn btn-outline-light btn-sm me-3">Comments</p>
                                <p class = "btn btn-outline-light btn-sm me-3">History</p>
                            </div>
                        </div>

                        <div class = "flex">
                            <p>Newest First</p>
                            <p>^</p>
                        </div>
                    </div>
                    <!-- To DO -->
                    <div class = "flex justify-between mb-4">
                        <p><span>XXXXXXXXX</span> changed the status</p>
                        <p>(09/09/2024)</p>
                    </div>

                    <div>
                        <h5>To DO: </h5>
                        <p>This is a description and its purpose it to describe the task and the reason for this is to fill up the word count and make a buffer layer so that the text can be sized correctly</p>
                    </div>
                </div> --}}

            </div>

            <!-- Task details -->
            <div class = "flex flex-col w-1/2 h-full items-center mb-2 min-w-64">
                {{-- Details box --}}
                <div class = "border-2 rounded-3 lg:w-3/4 w-10/12 h-3/4 flex flex-col justify-evenly px-3 py-1 mb-10">
                    <h1>Details</h1>
                    <div class = "flex justify-between mb-4 w-full">
                        <p>Assignee</p>
                        <select class = "dark:bg-transparent" id="formAssignee{{$task->id}}">
                            @foreach ($users as $assignee)
                                <option value="{{$assignee->id}}">{{$assignee->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>Labels</p>
                        <select class = "dark:bg-transparent w-50" id="formLabels{{$task->id}}">
                            @foreach (["API", "Backend", "Frontend", "UI/UX", "Database"] as $label)
                                @if($task->labels == $label)
                                    <option value="{{$label}}" selected>{{$label}}</option>
                                @else
                                    <option value="{{$label}}">{{$label}}</option>
                                @endif
                            @endforeach
                        </select>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>Sprint</p>
                        <p class = "fw-bold px-2">{{$parent_board->name}}</p>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>SP ESTIMATE</p>
                        <input type="number" id="formStoryPoint{{$task->id}}" class = "border-2 rounded w-10 dark:bg-transparent"  placeholder="{{$task->story_points}}" value="{{$task->story_points}}"/>
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


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var task_id= Number("<?php echo "$task->id"?>");
        let saveButton = document.getElementById(`saveTaskButton${task_id}`);
        let xButton = document.getElementById(`xButton${task_id}`);  // Not being saved as unique variable

        var initialInputTitle = document.getElementById(`formTitle${task_id}`).value;
        var initialInputDescription = document.getElementById(`formDescription${task_id}`).value;
        var initialAssignee = document.getElementById(`formAssignee${task_id}`).value;
        var initialLabels = document.getElementById(`formLabels${task_id}`).value;
        var initialStoryPoint = document.getElementById(`formStoryPoint${task_id}`).value;

        saveButton.addEventListener('click', e => {
            var inputTitle = document.getElementById(`formTitle${task_id}`).value;
            var inputDescription = document.getElementById(`formDescription${task_id}`).value;
            var assignee = document.getElementById(`formAssignee${task_id}`).value;
            var labels = document.getElementById(`formLabels${task_id}`).value;
            var storyPoint = document.getElementById(`formStoryPoint${task_id}`).value;

            // Submit the form via AJAX (using Fetch API)
            fetch('{{ route('tasks.update') }}', {
                method: 'POST',
                body: JSON.stringify({
                    task_id: task_id,
                    title: inputTitle,
                    description: inputDescription,
                    assignee: Number(assignee),
                    labels: labels,
                    storyPoint: Number(storyPoint)
                }),
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').getAttribute('content')
                }
            })
            .then(response => {
                    responseClone = response.clone();
                    return response.json();
                })
            .then(data => {
                if (data.success) {
                    console.log(data.task);
                } else {
                    console.error('Error updating task:', data.message);
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
            xButton.parentElement.parentElement.parentElement.classList.toggle('hidden');

            // Reset the form submission when closing without saving
            document.getElementById(`formTitle${task_id}`).value = initialInputTitle;
            document.getElementById(`formDescription${task_id}`).value = initialInputDescription;
            document.getElementById(`formAssignee${task_id}`).value = initialAssignee;
            document.getElementById(`formLabels${task_id}`).value = initialLabels;
            document.getElementById(`formStoryPoint${task_id}`).value = initialStoryPoint;
        });
    });


</script>
