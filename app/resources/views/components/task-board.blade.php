<div {{ $attributes->merge(['class' => 'rounded w-full h-full bg-slate-100 p-3 m-3 grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 2xl:grid-cols-4 grid-flow-dense justify-items-center items-start dark:bg-gray-700'])}}>
    {{$slot}}
</div>
