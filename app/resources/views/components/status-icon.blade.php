@props([
    'name',
    'color' => null,
])

<div
    {{ $attributes->class([
        'inline-flex w-min items-center gap-1.5 whitespace-nowrap rounded-full border px-2 py-0.5 text-xs font-semibold',
        'border-blue-200 bg-blue-50 text-blue-700 dark:border-blue-900 dark:bg-blue-950/60 dark:text-blue-300' => !$color,
        'border-gray-200 bg-white/80 text-gray-700 dark:border-gray-700 dark:bg-gray-900/80 dark:text-gray-200' => $color,
    ]) }}
>
    @if($color)
        <span
            class="h-2 w-2 rounded-full"
            style="background-color: {{ $color }}"
        ></span>
    @endif

    <span>{{ $name }}</span>
</div>
