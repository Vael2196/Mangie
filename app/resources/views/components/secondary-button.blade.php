<!-- <button {{ $attributes->merge(['type' => 'button', 'class' => 'inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150']) }}>
    {{ $slot }}
</button> -->


<button
    {{ $attributes->merge([
        'type' => 'button',
        'class' =>
            'inline-flex items-center justify-center gap-2
             rounded-lg border border-gray-300
             bg-white px-4 py-2.5
             text-sm font-semibold text-gray-700
             shadow-sm transition
             hover:border-gray-400 hover:bg-gray-50
             focus:outline-none focus:ring-2
             focus:ring-indigo-500 focus:ring-offset-2
             disabled:cursor-not-allowed disabled:opacity-50
             dark:border-gray-700 dark:bg-gray-800
             dark:text-gray-200 dark:hover:bg-gray-700
             dark:focus:ring-offset-gray-900'
    ]) }}
>
    {{ $slot }}
</button>