<table {{ $attributes->merge([ "class" => "table-fixed border min-w-full overflow-x-auto"])}}>
    <tbody>
        {{$slot}}
    </tbody>
</table>
