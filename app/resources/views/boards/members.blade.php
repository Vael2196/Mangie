<x-app-layout>
    <x-top-bar :title="$board->name . ' · Participants'" :user="auth()->user()" />
    <main class="mx-auto max-w-4xl space-y-6 px-6 py-8" data-board-members data-board-id="{{ $board->id }}">
        <a href="{{ route('boards.show', $board) }}" class="inline-flex items-center gap-2 text-sm font-semibold text-indigo-600 dark:text-indigo-400">
            <span class="material-symbols-rounded text-[18px]">arrow_back</span> Back to board
        </a>
        <div>
            <h1 class="text-2xl font-bold">Board participants</h1>
            <p class="mt-2 text-sm text-gray-500 dark:text-gray-400">Admins manage access. Members edit and invite. Observers can view the board and task details.</p>
        </div>
        <p data-members-status class="hidden rounded-xl border border-indigo-200 bg-indigo-50 p-3 text-sm text-indigo-800 dark:border-indigo-900 dark:bg-indigo-950 dark:text-indigo-200" role="status"></p>

        @can('invite', $board)
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="font-bold">Invite by name or email</h2>
                <p class="mt-1 text-sm text-gray-500">No account yet? Enter their email and they can register before joining.</p>
                <form data-board-invite class="mt-4 flex flex-wrap items-start gap-3">
                    <div class="relative min-w-56 flex-1">
                        <input data-invite-query type="text" required maxlength="255" autocomplete="off" placeholder="Name or email address"
                            aria-label="Invitee name or email" aria-controls="invite-suggestions" aria-expanded="false"
                            class="w-full rounded-xl border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                        <div id="invite-suggestions" data-invite-suggestions class="absolute inset-x-0 top-full z-20 mt-2 hidden rounded-xl border border-gray-200 bg-white p-2 shadow-xl dark:border-gray-700 dark:bg-gray-900"></div>
                    </div>
                    <select data-invite-role aria-label="Invitation role" class="rounded-xl border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                        <option value="member">Member · can edit</option>
                        <option value="observer">Observer · view only</option>
                    </select>
                    <button class="rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Send invitation</button>
                </form>
                <div class="mt-5 border-t border-gray-100 pt-4 dark:border-gray-800">
                    <h3 class="text-sm font-semibold">Share an invitation link</h3>
                    <p class="mt-1 text-xs text-gray-500">Anyone holding the link can join with a verified account. Links expire after 7 days. The selected role above also applies to the link.</p>
                    <button type="button" data-create-share-link class="mt-3 rounded-xl border border-gray-300 px-3 py-2 text-sm font-semibold hover:bg-gray-50 dark:border-gray-700 dark:hover:bg-gray-800">Create share link</button>
                    <div data-share-result class="mt-3 hidden flex-wrap items-center gap-2">
                        <input data-share-url readonly aria-label="Share invitation URL" class="min-w-40 flex-1 rounded-lg border-gray-300 bg-gray-50 text-xs dark:border-gray-700 dark:bg-gray-800">
                        <button type="button" data-copy-share-link class="rounded-lg bg-indigo-50 px-3 py-2 text-sm font-semibold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300">Copy link</button>
                    </div>
                </div>
            </section>
        @endcan

        <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
            <h2 class="mb-4 font-bold">Participants ({{ $board->users->count() }})</h2>
            <div class="divide-y divide-gray-100 dark:divide-gray-800">
                @foreach($board->users as $participant)
                    <div class="flex flex-wrap items-center gap-3 py-3" data-member-row data-user-id="{{ $participant->id }}">
                        <x-user-avatar :user="$participant" size="md" />
                        <div class="min-w-0 flex-1">
                            <p class="text-sm font-semibold">{{ $participant->name }} {{ $participant->is(auth()->user()) ? '(you)' : '' }}</p>
                            <p class="text-xs text-gray-500">{{ ucfirst($participant->pivot->role) }}{{ !$participant->hasVerifiedEmail() ? ' · email verification pending' : '' }}</p>
                        </div>
                        @can('manageMembers', $board)
                            <select data-member-role aria-label="Role for {{ $participant->name }}" class="rounded-lg border-gray-300 bg-white text-sm dark:border-gray-700 dark:bg-gray-800">
                                @foreach(['admin' => 'Admin', 'member' => 'Member', 'observer' => 'Observer'] as $role => $label)
                                    <option value="{{ $role }}" @selected($participant->pivot->role === $role)>{{ $label }}</option>
                                @endforeach
                            </select>
                            <button type="button" data-save-member-role class="rounded-lg px-3 py-2 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950">Save role</button>
                            @unless($participant->is(auth()->user()))
                                <button type="button" data-remove-member class="rounded-lg px-3 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950">Remove</button>
                            @endunless
                        @endcan
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-gray-500">At least one verified admin must remain. Before leaving as the last admin, promote another verified participant.</p>
            <button type="button" data-leave-board class="mt-3 rounded-xl border border-red-200 px-4 py-2 text-sm font-semibold text-red-600 hover:bg-red-50 dark:border-red-900 dark:hover:bg-red-950">Leave board</button>
        </section>

        @can('invite', $board)
            <section class="rounded-2xl border border-gray-200 bg-white p-5 shadow-sm dark:border-gray-800 dark:bg-gray-900">
                <h2 class="font-bold">Pending invitations and share links</h2>
                <p class="mt-1 text-xs text-gray-500">Removed participants need a new invitation to rejoin. You can revoke individual links here.</p>
                <div data-pending-invitations>
                    @forelse($invitations as $invitation)
                        <div class="mt-3 flex items-center gap-3 text-sm" data-invitation-row>
                            <span class="min-w-0 flex-1 break-all">{{ $invitation->email ?? 'Share link' }} · {{ $invitation->role }} · expires {{ $invitation->expires_at->format('j M') }}</span>
                            @can('manageMembers', $board)
                                <button type="button" data-revoke-invitation="{{ $invitation->id }}" class="rounded-lg px-3 py-2 font-semibold text-red-600 hover:bg-red-50 dark:hover:bg-red-950">Revoke</button>
                            @endcan
                        </div>
                    @empty
                        <p class="mt-3 text-sm text-gray-500">No pending invitations.</p>
                    @endforelse
                </div>
            </section>
        @endcan
    </main>
</x-app-layout>
