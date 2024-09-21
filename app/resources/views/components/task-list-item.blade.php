@props(["task"])

<tr class="px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded"
    onclick="">
    <td class="py-2 pl-5 border-b-2">Name</td>
    <td class="py-2 border-b-2 w-20">Epic</td>
    <td class="py-2 border-b-2 w-20">Status</td>
    <td class="py-2 border-b-2 w-20">Priority</td>
    <td class="py-2 border-b-2 w-20">Assigned</td>
    {{-- <td>{{$task->name}}</td>
    <td>{{$task->epic}}</td>
    <td>{{$task->status}}</td>
    <td>{{$task->priority}}</td>
    <td>{{$task->assigned}}</td> --}}
</tr>