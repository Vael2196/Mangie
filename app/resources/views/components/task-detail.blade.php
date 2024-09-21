@props(['task'])

<div class = "absolute top-0 right-0 h-full w-full p-5 flex justify-center items-center z-30 backdrop-blur-sm bg-black bg-opacity-30">
    <div class = "rounded lg:px-11 lg:py-7 p-5 lg:w-4/6 w-5/6 h-3/5 bg-white dark:bg-gradient-to-l from-slate-700 to-gray-900 border-2 flex">
        <div class="flex justify-between">
            <div class = "flex flex-col lg:w-1/2 h-full">
                <!-- Task info -->
                <div class="mb-10">
                    <h1>NAME</h1>
                    <h3>Description</h3>
                    <p>This is a description and its purpose it to describe the task and the reason for this is to fill up the word count and make a buffer layer so that the text can be sized correctly</p>
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
                <select class = "mb-2 dark:bg-transparent">
                    <option value="24" selected>Product 1</option>
                    <option value="32">Product 2</option>
                    <option value="54">Product 3</option>
                </select>

                {{-- Details box --}}
                <div class = "border-2 rounded-3 w-3/4 h-3/4 flex flex-col justify-evenly px-3 py-3 mb-10">
                    <h1>Details</h1>
                    <div class = "flex justify-between mb-4 w-full">
                        <p>Assignee</p>
                        <select class = "dark:bg-transparent">
                            <option value="24" selected>Product 1</option>
                            <option value="32">Product 2</option>
                            <option value="54">Product 3</option>
                        </select>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>Labels</p>
                        <select class = "dark:bg-transparent">
                            <option value="24" selected>Product 1</option>
                            <option value="32">Product 2</option>
                            <option value="54">Product 3</option>
                        </select>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>Parent</p>
                        
                        <select class = "dark:bg-transparent">
                            <option value="24" selected>Product 1</option>
                            <option value="32">Product 2</option>
                            <option value="54">Product 3</option>
                        </select>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>Sprint</p>
                        <p class = "fw-bold px-2">Sprint A</p>
                    </div>

                    <div class = "flex justify-between mb-4 w-full">
                        <p>SP ESTIMATE</p>
                        <input type="number" id="typeNumber" class = "border-2 rounded w-20 dark:bg-transparent"  placeholder="Number"/>
                    </div>
                </div>

                {{-- Creation dates --}}
                <div class = "flex flex-col items-start w-75">
                    <p>Created: <span>09/09/2024</span></p>
                    <p>Updated: <span>09/09/2024</span></p>
                </div>
            </div>
        </div>

        <span class="flex items-start" onclick="document.querySelector('details').removeAttribute('open')">
            <div class="p-2 hover:cursor-pointer hover:bg-gray-100 bg-opacity-10 rounded">
                <i class="fa-solid fa-xmark fa-2xl"></i>
            </div>
        </span>
    </div>
</div>
