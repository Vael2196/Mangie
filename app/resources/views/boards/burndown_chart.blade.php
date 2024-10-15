<x-app-layout>

    <x-top-bar :title="'Burndown Chart for ' . $board->name" :user="$user"/>

    <form method="GET" action="{{route('boards.show', $board->id)}}">
        <button type="submit" class="mx-4 px-4 py-2 bg-blue-600 dark:bg-blue-600 text-white rounded-lg hover:bg-blue-700 dark:hover:bg-blue-700">
            Back to Board
        </button>
    </form>


    {!! $chart->container() !!}
    {!! $chart->script() !!}


</x-app-layout>
