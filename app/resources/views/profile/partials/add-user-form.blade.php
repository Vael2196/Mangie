<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Create User') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __('Ensure the email is correct') }}
        </p>
    </header>

    <form method="post" action="{{ route('profile.users.add') }}" class="mt-6 space-y-6">
        @csrf

        {{-- Row for name and email to add --}}
        <div>
            <x-input-label for="add_user_name" :value="__('Name')" />
            <x-text-input id="add_user_name" name="name" type="text" class="mt-1 block w-full" autocomplete="name" />
            <x-input-error :messages="$errors->addUser->get('name')" class="mt-2" />
        </div>

        <div>
            <x-input-label for="add_user_email" :value="__('Email')" />
            <x-text-input id="add_user_email" name="email" type="email" class="mt-1 block
            w-full" autocomplete="email" />
            <x-input-error :messages="$errors->addUser->get('email')" class="mt-2" />
        </div>

        {{-- Save new user --}}
        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'user-added')
                <p
                    x-data="{ show: true }"
                    x-show="show"
                    x-transition
                    x-init="setTimeout(() => show = false, 2000)"
                    class="text-sm text-gray-600 dark:text-gray-400"
                >{{ __('Saved.') }}</p>
            @endif
        </div>
    </form>
</section>
