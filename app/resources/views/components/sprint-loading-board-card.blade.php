<div {{ $attributes->merge(['class' => 'rounded w-full h-full bg-slate-100 p-3 m-3 grid sm:grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 2xl:grid-cols-5 grid-flow-dense gap-2 justify-items-center items-start'])}}>
    {{$slot}}
</div>
