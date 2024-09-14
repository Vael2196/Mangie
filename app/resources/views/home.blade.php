@extends('dashboard')

@section('content')
    <div class="py-12">
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
@endsection