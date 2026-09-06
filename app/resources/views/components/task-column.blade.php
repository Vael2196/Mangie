@props(['title'])

<!-- <div {{ $attributes->merge(['class' => 'rounded min-h-full h-max w-64 bg-slate-100 px-1 pb-2 dark:bg-gray-700 flex flex-col space-y-3 shadow-lg'])}}>
    <h1 class="dark:text-white font-bold text-xl px-3 py-3">{{ $title }}</h1>
    {{$slot}}
</div> -->

@props(['title'])

<div
    {{ $attributes->merge([
        'class' =>
            'flex h-max min-h-40 w-72 shrink-0 flex-col
             rounded-2xl border border-gray-200
             bg-gray-100/80 p-3
             shadow-sm
             dark:border-gray-700 dark:bg-gray-800/80'
    ]) }}
>
    <div
        class="mb-3 flex items-center justify-between
               px-1 py-1"
    >
        <h2
            class="text-sm font-bold uppercase tracking-wide
                   text-gray-700 dark:text-gray-200"
        >
            {{ $title }}
        </h2>

        <span
            class="material-symbols-rounded
                   text-[19px] text-gray-400"
        >
            more_horiz
        </span>
    </div>

    <div class="space-y-3">
        {{ $slot }}
    </div>
</div>