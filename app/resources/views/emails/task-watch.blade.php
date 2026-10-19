<x-mail::message>
# A task you watch has changed

**{{ $taskTitle }}** on **{{ $boardName }}** has
{{ $changeCount === 1 ? 'an update' : $changeCount.' updates' }}.
@if($actorName)
The most recent update from another participant was made by {{ $actorName }}.
@endif

<x-mail::button :url="$taskUrl">
View task
</x-mail::button>

You receive this email because you watch this task.
[Change email frequency]({{ route('settings.edit') }}) or uncheck Watch in the task menu.
</x-mail::message>
