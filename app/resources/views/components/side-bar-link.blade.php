@props(['name'])

<div {{ $attributes->merge(['class' => 'w-full px-4 py-2 text-start text-sm leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-800 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3']) }}>
    {{ $slot }}
    <h1 class="lg:block hidden">{{$name}}</h1>
</div>