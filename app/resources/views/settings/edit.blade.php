<x-app-layout>
    <x-top-bar title="Settings" :user="$user" />
    <div class="mx-auto max-w-3xl space-y-6 px-4 py-8 sm:px-6">
        <form method="POST" action="{{ route('settings.update') }}" class="space-y-6">
            @csrf
            @method('PATCH')
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-bold">Notifications</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">Choose how often you receive emails about tasks you watch. Watch a task from its menu to subscribe. Your own changes do not generate an email to you.</p>
                <label for="notification-frequency" class="mt-5 block text-sm font-semibold">Task update emails</label>
                <select id="notification-frequency" name="notification_frequency" class="mt-2 w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                    @foreach(config('notifications.frequencies') as $value => $frequency)
                        <option value="{{ $value }}" @selected(old('notification_frequency', $user->notification_frequency) === $value)>{{ $frequency['label'] }}</option>
                    @endforeach
                </select>
                <p class="mt-3 text-xs leading-5 text-gray-500">Timed emails group changes from the first pending update. Instant notifications are queued as soon as a change is saved. Never pauses emails while keeping your watch list.</p>
                <x-input-error :messages="$errors->get('notification_frequency')" class="mt-2" />
            </section>
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm sm:p-7 dark:border-gray-800 dark:bg-gray-900">
                <h2 class="text-lg font-bold">Visibility</h2>
                <p class="mt-2 text-sm leading-6 text-gray-500 dark:text-gray-400">You appear online for one hour after your latest activity. Board admins and members can see your status; observers cannot.</p>
                <input type="hidden" name="invisible" value="0">
                <label class="mt-5 flex items-start gap-3 rounded-xl bg-gray-50 p-4 dark:bg-gray-800">
                    <input type="checkbox" name="invisible" value="1" @checked(old('invisible', $user->invisible)) class="mt-1 rounded border-gray-300 text-indigo-600 focus:ring-indigo-500">
                    <span><span class="block text-sm font-semibold">Appear offline</span><span class="mt-1 block text-sm text-gray-500 dark:text-gray-400">Hide your online status and last-seen time until you turn this off.</span></span>
                </label>
                <x-input-error :messages="$errors->get('invisible')" class="mt-2" />
            </section>
            <div class="flex items-center gap-4">
                <x-primary-button>Save settings</x-primary-button>
                @if(session('status') === 'settings-saved')<p class="text-sm text-emerald-600" role="status">Settings saved.</p>@endif
            </div>
        </form>
    </div>
</x-app-layout>
