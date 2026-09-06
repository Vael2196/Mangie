@props(['disabled' => false])

<!-- <input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-indigo-500 dark:focus:border-indigo-600 focus:ring-indigo-500 dark:focus:ring-indigo-600 rounded-md shadow-sm']) !!}> -->

<input
    {{ $disabled ? 'disabled' : '' }}

    {!! $attributes->merge([
        'class' =>
            'rounded-xl border-gray-300 bg-white
             px-3.5 py-2.5 text-sm text-gray-900
             shadow-sm
             placeholder:text-gray-400
             focus:border-indigo-500 focus:ring-indigo-500
             disabled:cursor-not-allowed disabled:bg-gray-100
             dark:border-gray-700 dark:bg-gray-900
             dark:text-gray-100 dark:placeholder:text-gray-500
             dark:focus:border-indigo-500 dark:focus:ring-indigo-500'
    ]) !!}
>