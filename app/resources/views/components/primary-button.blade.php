<!-- <button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center px-4 py-2 bg-gray-800 dark:bg-gray-200 border border-transparent rounded-md font-semibold text-xs text-white dark:text-gray-800 uppercase tracking-widest hover:bg-gray-700 dark:hover:bg-white focus:bg-gray-700 dark:focus:bg-white active:bg-gray-900 dark:active:bg-gray-300 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> -->


<button
    {{ $attributes->merge([
        'type' => 'submit',
        'class' =>
            'inline-flex items-center justify-center gap-2
             rounded-lg border border-transparent
             bg-indigo-600 px-4 py-2.5
             text-sm font-semibold text-white
             shadow-sm shadow-indigo-600/10
             transition
             hover:bg-indigo-500
             focus:outline-none focus:ring-2
             focus:ring-indigo-500 focus:ring-offset-2
             disabled:cursor-not-allowed disabled:opacity-50
             dark:focus:ring-offset-gray-900'
    ]) }}
>
    {{ $slot }}
</button>