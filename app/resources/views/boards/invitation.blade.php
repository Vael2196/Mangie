<x-guest-layout>
    <div class="space-y-5">
        <div>
            <p class="text-xs font-semibold uppercase tracking-wider text-indigo-600">Board invitation</p>
            <h1 class="mt-2 text-xl font-bold text-gray-900 dark:text-white">{{ $invitation->board->name }}</h1>
            <p class="mt-2 text-sm text-gray-600 dark:text-gray-400">You’re invited as a {{ $invitation->role }}. Joining is permanent until you leave or an admin removes you.</p>
        </div>
        @if(!$user)
            <p class="text-sm text-gray-600 dark:text-gray-400">Sign in or create an account{{ $invitation->email ? ' using the address that received this email' : '' }}. You’ll return here to join once your email is verified.</p>
            <div class="flex gap-3">
                <a href="{{ route('login') }}" class="rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Sign in</a>
                <a href="{{ route('register') }}" class="rounded-xl border border-gray-300 px-4 py-2 text-sm font-semibold text-gray-700 dark:border-gray-700 dark:text-gray-200">Create account</a>
            </div>
        @elseif(!$matchesEmail)
            <p class="text-sm text-red-600">This invitation was sent to a different email address. Sign out and use the account that received it.</p>
            <form method="POST" action="{{ route('logout') }}">@csrf <x-primary-button>Sign out</x-primary-button></form>
        @elseif(!$user->hasVerifiedEmail())
            <p class="text-sm text-gray-600 dark:text-gray-400">Verify your email before joining this board.</p>
            <a href="{{ route('verification.notice') }}" class="inline-block rounded-xl bg-indigo-600 px-4 py-2 text-sm font-semibold text-white">Verify email</a>
        @else
            <form method="POST" action="{{ route('board-invitations.accept', $token) }}">
                @csrf
                <x-primary-button>Join board</x-primary-button>
            </form>
        @endif
        <p class="text-xs text-gray-400">Invitation expires {{ $invitation->expires_at->format('j M Y') }}.</p>
    </div>
</x-guest-layout>
