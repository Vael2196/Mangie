<div {{ $attributes->merge([ "class" => "border min-w-full overflow-x-auto rounded p-3 bg-gray-100"])}}>
    <h1 class="mb-3 font-semibold">THIS IS A SPRINT</h1>
    @if ($slot->isNotEmpty())
    <table {{ $attributes->merge([ "class" => "table-fixed border min-w-full overflow-x-auto rounded bg-white"])}}>
        {{ $slot }}
    </table>
    @else
        <p class="text-sm">Add tasks here or from the product backlog</p>
    @endif
</div>
