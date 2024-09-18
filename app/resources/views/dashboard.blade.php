<x-app-layout>
    {{-- <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
        {{ __('Dashboard') }}
    </h2>

    <h1>hello</h1>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    {{ __("You're logged in!") }}
                </div>
            </div>
        </div>
    </div> --}}

    <div class = "flex flex-col">
        <x-top-bar title="Sprint"/>
        <div class = "flex pr-10 items-start">
            <p class = "text-sm px-6 min-h-20 grow">This is a description of a sprint</p>
            <div class = "flex space-x-8 items-center">
                <p>X-days left</p>
                <x-secondary-button>Complete Sprint</x-secondary-button>
                <a class = "hover:cursor-pointer"><i class="fa-solid fa-ellipsis"></i></a>
            </div>
        </div>

        <ul class = "flex space-x-2 px-6">
            <li class = "text-orange-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
            <li class = "text-purple-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
            <li class = "text-red-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
        </ul>
    </div>
    <div class = "flex flex-nowrap space-x-5 h-4/6 p-5 overflow-auto max-w-[80vw] max-h-[70vh]">
        <x-task-column title="TO DO">
            <x-task-box DESP="To make a task that safiuhiohgfutugcjvhkbln;lkvjchfxghcvjbklncfxg "/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
            <x-task-box DESP="To make a task"/>
        </x-task-column>
        <x-task-column title="DOING"></x-task-column>
        <x-task-column title="DONE"></x-task-column>
        <a class = "hover:cursor-pointer" href = "/"><i class="fa-regular fa-square-plus fa-2x"></i></a>
    </div>

    {{-- <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white dark:bg-gray-800 overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 dark:text-gray-100">
                    <!-- Insert Project Management Content -->
                    <div class="container mx-auto">
                        <h1 class="text-3xl font-black text-center my-8">Dashboard</h1>

                        @foreach($projects as $project)
                            <div class="bg-gray-100 dark:bg-gray-700 rounded-lg shadow-lg p-6 mb-8">
                                <h2 class="text-2xl font-bold">{{ $project->name }}</h2>
                                
                                @foreach($project->boards as $board)
                                    <div class="bg-white dark:bg-gray-800 rounded-lg shadow-md p-4 my-4">
                                        <h3 class="text-xl font-semibold">{{ $board->name }}</h3>
                                        
                                        <div class="flex space-x-4">
                                            @foreach($board->columns as $column)
                                                <div class="bg-gray-200 dark:bg-gray-600 rounded-lg p-4 w-1/4">
                                                    <h4 class="font-bold mb-2">{{ $column->name }}</h4>
                                                    <ul>
                                                        @foreach($column->tasks as $task)
                                                            <li class="bg-white dark:bg-gray-900 p-2 my-2 rounded-lg shadow">
                                                                {{ $task->title }}
                                                            </li>
                                                        @endforeach
                                                    </ul>
                                                </div>
                                            @endforeach
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                    <!-- End of Project Management Content -->
                </div>
            </div>
        </div>
    </div>

 --}}




    
</x-app-layout>
