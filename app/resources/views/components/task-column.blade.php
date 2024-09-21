@props(['title'])

<div {{ $attributes->merge(['class' => 'rounded min-h-full h-max w-64 bg-slate-100 px-1 pb-2 dark:bg-gray-700 flex flex-col space-y-3'])}}>
    <h1 class="px-6 py-3 dark:text-white font-medium mb-3">{{ $title }}</h1>
    {{$slot}}
</div>