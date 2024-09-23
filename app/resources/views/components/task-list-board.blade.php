@props(["id" => ""])

<table {{ $attributes->merge([ "class" => "table-fixed border min-w-full overflow-x-auto", "id" => $id])}}>
    <tbody>
        {{$slot}}
    </tbody>
</table>
