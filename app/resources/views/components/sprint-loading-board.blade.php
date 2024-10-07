@props(['name'])

<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100"])}}>
    <div class="flex justify-between mb-2">
        <h1 class="font-semibold text-xl">{{$name}}</h1>
        <x-secondary-button>Start Sprint</x-secondary-button>
    </div>
    @if ($slot->isNotEmpty())
    <table {{ $attributes->merge([ "class" => "table-fixed border min-w-full overflow-x-auto rounded bg-white"])}}>
        {{ $slot }}
    </table>
    @else
        <p class="text-sm">Add tasks here or from the product backlog</p>
    @endif
</div>
