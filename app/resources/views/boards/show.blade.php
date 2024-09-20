<x-app-layout>

    <div class="container mx-auto mt-8">
        <div class="flex flex-col">
            <x-top-bar :title="$board->name"/>
            
            <div class="flex pr-10 items-start">
                <p class = "text-sm px-6 min-h-20 max-h-40 overflow-y-auto max-w-[65vw]">This is a description of the board.</p>
                <div class="flex space-x-8 items-center">
                    <p class = "lg:block hidden">X-days-left</p>
                    <x-secondary-button>Complete Board</x-secondary-button>
                    <a class="hover:cursor-pointer"><i class="fa-solid fa-ellipsis"></i></a>
                </div>
            </div>

            <ul class="flex space-x-2 px-6">
                <!-- Display users here -->
                <li class="text-orange-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
                <li class="text-purple-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
                <li class="text-red-500"><i class="fa-solid fa-circle-user fa-2x"></i></li>
            </ul>
        </div>

        <div class="flex flex-nowrap space-x-5 h-4/6 p-5 overflow-auto max-w-[80vw] max-h-[60vh]">
            <!-- Display columns and tasks -->
            @foreach($board->columns as $column)
                <x-task-column :title="$column->name">
                    @foreach($column->tasks as $task)
                        <x-task-box :desp="$task->title"/>
                    @endforeach
                </x-task-column>
            @endforeach

            <!-- Option to add new columns -->
            <a class="hover:cursor-pointer" href="#"><i class="fa-regular fa-square-plus fa-2x"></i></a>
        </div>
    </div>

</x-app-layout>