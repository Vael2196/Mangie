<div class = "absolute top-0 right-0 h-full w-full p-5 flex justify-center items-center z-30 backdrop-blur-sm bg-black bg-opacity-30">
    <div class = "rounded lg:px-11 lg:py-7 p-5 lg:w-4/6 w-5/6 h-3/5 bg-white dark:bg-gradient-to-l from-slate-700 to-gray-900 border-2 flex">
        <div class="flex justify-between">
            <div class = "flex flex-col lg:w-1/2 h-full">
                <!-- Task info -->
                <div class="mb-10 flex flex-col">
                    <input id="formTitle" type="text" placeholder="{{$task->title}}" value="{{$task->title ? $task->title : "Enter a title"}}" class="placeholder-slate-700 text-xl mb-4">
                    <label for="formDescription" class="mb-2">Description</label>
                    <textarea id="formDescription" rows="3" placeholder="{{$task->description ? $task->description : "Enter a description"}}" value="{{$task->description}}"></textarea>
                </div>

                <!-- Task activity -->
                <div>
                    <h3 class = "mb-4">Activity</h3>

                    <!-- Tab icons -->
                    <div class = "flex justify-between w-100">
                        {{-- <div class = "flex w-75"
                            <h5 class = "py-1" >Show:</h5>
                            <div class = "flex w-100 justify-center">
                                <p class = "btn btn-outline-light btn-sm me-3">ALL</p>
                                <p class = "btn btn-outline-light btn-sm me-3">Comments</p>
                                <p class = "btn btn-outline-light btn-sm me-3">History</p>
                            </div>
                        </div> --}}

                        {{-- <div class = "flex">
                            <p>Newest First</p>
                            <p>^</p>
                        </div> --}}
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
                </div>

            </div>

            <!-- Task details -->
            <div class = "lg:flex lg:flex-col w-1/2 h-full items-center hidden mb-2">
                {{-- Select details --}}
                <button type="button" id="saveTaskButton{{$task->id}}" class='mb-3 inline-flex items-center px-4 py-2 bg-blue-500 dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-white dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-blue-600 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150'>
                    Save
                </button>

                {{-- Details box --}}
                <div class = "border-2 rounded-3 w-3/4 h-3/4 flex flex-col justify-evenly px-3 py-1 mb-10">
                    <h1>Details</h1>
                    <div class = "flex justify-between mb-4 w-full">
                        <p>Assignee</p>
                        <select class = "dark:bg-transparent" id="formAssignee">
                            @foreach ($users as $assignee)
                                <option value="{{$assignee->id}}">{{$assignee->name}}</option>
                            @endforeach
                        </select>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>Labels</p>
                        <select class = "dark:bg-transparent w-50" id="formLabels">
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
                        <input type="number" id="formStoryPoint" class = "border-2 rounded w-10 dark:bg-transparent"  placeholder="{{$task->story_points}}" value="{{$task->story_points}}"/>
                    </div>
                </div>

                {{-- Creation dates --}}
                <div class = "flex flex-col items-start w-75">
                    <p>Created: <span>{{$task->created_at}}</span></p>
                    <p>Updated: <span>{{$task->updated_at}}</span></p>
                </div>
            </div>
        </div>

        <span class="flex items-start" id="xButton{{$task->id}}" onclick="this.parentElement.parentElement.parentElement.classList.toggle('hidden')">
            <div class="p-2 hover:cursor-pointer hover:bg-gray-100 dark:hover:bg-gray-900 bg-opacity-10 rounded">
                <i class="fa-solid fa-xmark fa-2xl"></i>
            </div>
        </span>
    </div>
</div>


<script>
    document.addEventListener('DOMContentLoaded', function () {
        var task_id= Number("<?php echo "$task->id"?>");

        var hasClicked = false;
        saveButton = document.getElementById(`saveTaskButton${task_id}`);
        xButton = document.getElementById(`xButton${task_id}`);

        var initialInputTitle = document.getElementById("formTitle").value;
        var initialInputDescription = document.getElementById("formDescription").value;
        var initialAssignee = document.getElementById("formAssignee").value;
        var initialLabels = document.getElementById("formLabels").value;
        var initialStoryPoint = document.getElementById("formStoryPoint").value;

        saveButton.addEventListener('click', e => {
            hasClicked = true;
            var inputTitle = document.getElementById("formTitle").value;
            var inputDescription = document.getElementById("formDescription").value;
            var assignee = document.getElementById("formAssignee").value;
            var labels = document.getElementById("formLabels").value;
            var storyPoint = document.getElementById("formStoryPoint").value;

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

        // Reset the form submission when closing without saving
        xButton.addEventListener('click', e => {
            //  Skip when the save button has been clicked
            if(hasClicked){
                // hasClicked = false;
                return;
            }

            // Reset variables to their original values
            document.getElementById("formTitle").value = initialInputTitle;
            document.getElementById("formDescription").value = initialInputDescription;
            document.getElementById("formLabels").value = initialLabels;
            document.getElementById("formAssignee").value = initialAssignee;
            document.getElementById("formStoryPoint").value = initialStoryPoint;
        });

        });
    });


</script>
