<!-- @props(['name','link'])

<a href = {{ $link }}>
    <div {{ $attributes->merge(['class' => 'w-full px-4 py-2 text-start leading-5 text-gray-700 dark:text-gray-300 hover:bg-gray-100 dark:hover:bg-gray-500 hover:cursor-pointer focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-800 transition duration-150 ease-in-out rounded flex space-x-3']) }}>
        <div class = "text-center">{{ $slot }}</div>
        <div class = "lg:block hidden text-sm">{{ $name }}</div>
    </div>
</a> -->


@props([
    'name',
    'link',
    'icon',
    'active' => false
])

<a
    href="{{ $link }}"
    @class([
        'group flex min-h-11 items-center justify-center
         gap-3 rounded-xl px-3 py-2.5
         text-sm font-semibold transition
         lg:justify-start',

        'bg-indigo-50 text-indigo-700
         ring-1 ring-indigo-100
         dark:bg-indigo-950/60
         dark:text-indigo-300
         dark:ring-indigo-900'
            => $active,

        'text-gray-500
         hover:bg-gray-100 hover:text-gray-900
         dark:text-gray-400
         dark:hover:bg-gray-800
         dark:hover:text-white'
            => !$active,
    ])
>
    <span
        @class([
            'material-symbols-rounded shrink-0 text-[22px]',

            'text-indigo-600 dark:text-indigo-400'
                => $active,

            'text-gray-400
             group-hover:text-indigo-600
             dark:text-gray-500
             dark:group-hover:text-indigo-400'
                => !$active,
        ])
    >
        {{ $icon }}
    </span>

    <span class="hidden lg:block">
        {{ $name }}
    </span>
</a>