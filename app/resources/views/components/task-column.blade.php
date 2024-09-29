@props(['title'])

<div {{ $attributes->merge(['class' => 'rounded min-h-full h-max w-64 bg-slate-100 px-1 pb-2 dark:bg-gray-700 flex flex-col space-y-3 shadow-lg'])}}>
    <h1 class="dark:text-white font-bold text-xl px-3 py-3">{{ $title }}</h1>
    {{$slot}}
</div>
