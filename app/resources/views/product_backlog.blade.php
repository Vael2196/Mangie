<x-app-layout>
    <x-top-bar title="Product Backlog"/>
    <x-dropdown>
        <x-slot name="trigger">
            <button type="button" onclick="open"><i class="fa-solid fa-ellipsis"></i></button>
        </x-slot>
        <x-slot name="content">
            <x-dropdown-link href="#">Edit</x-dropdown-link>
            <x-dropdown-link href="#">Delete</x-dropdown-link>
        </x-slot>
    </x-dropdown>
    <div class="w-[97%] h-[80%]">
        <x-task-board>
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
        </x-task-board>
    </div>
</x-app-layout>