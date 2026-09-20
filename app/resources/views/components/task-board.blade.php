<div
    {{ $attributes->merge([
        'class' =>
            'min-h-[72px] rounded w-full h-full bg-slate-100 p-3 m-3 grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 grid-flow-dense justify-items-center items-start dark:bg-gray-700'
    ]) }}
>
    {{ $slot }}

    <div
        data-empty-drop-marker
        class="pointer-events-none flex min-h-[58px]
               w-full items-center justify-center
               rounded-xl border-2 border-dashed
               border-gray-200 px-3 text-xs
               text-gray-400 dark:border-gray-700
               dark:text-gray-500"
    >
        Drop a task here
    </div>
</div>
