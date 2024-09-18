@props(['title'])

<div {{ $attributes->merge(['class' => 'shrink rounded h-full w-64 bg-slate-100 px-1 flex flex-col space-y-3'])}}>
    <h1 class="px-6 py-3 font-medium mb-3">{{ $title }}</h1>
    {{$slot}}
</div>