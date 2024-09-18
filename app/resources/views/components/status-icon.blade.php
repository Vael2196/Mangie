@props(["name"])

<div {{ $attributes->merge([ 'class' => 'bg-blue-500 text-blue-800 rounded text-xs font-bold w-min text-nowrap px-1 py-0.5'])}}>
    <h1>{{ $name }}</h1>
</div>