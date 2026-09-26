<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">
            {{ __('Profile Information') }}
        </h2>

        <p class="mt-1 text-sm text-gray-600 dark:text-gray-400">
            {{ __("Update your account's profile information and email address.") }}
        </p>
    </header>

    <form id="send-verification" method="post" action="{{ route('verification.send') }}">
        @csrf
    </form>

    <form method="post" action="{{ route('profile.update') }}" class="mt-6 space-y-6">
        @csrf
        @method('patch')

        <input
            type="hidden"
            name="avatar_data"
            value="{{ old('avatar_data') }}"
            data-avatar-data
        >

        <input
            type="hidden"
            name="remove_avatar"
            value="{{ old('remove_avatar', 0) }}"
            data-remove-avatar-value
        >

        <div>
            <x-input-label value="{{ __('Profile photo') }}" />

            <div class="mt-3 flex flex-wrap items-center gap-4">
                <x-user-avatar
                    :user="$user"
                    size="xl"
                    data-profile-avatar-preview
                />

                <div class="flex flex-wrap gap-2">
                    <label
                        class="inline-flex cursor-pointer items-center gap-2 rounded-xl
                               bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                               text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <span class="material-symbols-rounded text-[19px]">
                            add_a_photo
                        </span>

                        Choose photo

                        <input
                            type="file"
                            accept="image/jpeg,image/png,image/webp"
                            class="sr-only"
                            data-avatar-file
                        >
                    </label>

                    <button
                        type="button"
                        data-remove-avatar
                        class="inline-flex items-center gap-2 rounded-xl
                               border border-gray-300 px-4 py-2.5
                               text-sm font-semibold text-gray-600 transition
                               hover:bg-gray-100 dark:border-gray-600
                               dark:text-gray-300 dark:hover:bg-gray-700"
                    >
                        <span class="material-symbols-rounded text-[19px]">
                            person_remove
                        </span>

                        Use default
                    </button>
                </div>
            </div>

            <p class="mt-2 text-xs text-gray-500 dark:text-gray-400">
                JPG, PNG or WebP, up to 5 MB. You can reposition and zoom before saving.
            </p>

            <p
                data-avatar-error
                class="mt-2 hidden text-sm text-red-600 dark:text-red-400"
            ></p>

            <x-input-error class="mt-2" :messages="$errors->get('avatar_data')" />
        </div>

        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input id="name" name="name" type="text" class="mt-1 block w-full" :value="old('name', $user->name)" required autofocus autocomplete="name" />
            <x-input-error class="mt-2" :messages="$errors->get('name')" />
        </div>

        <div>
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input id="email" name="email" type="email" class="mt-1 block w-full" :value="old('email', $user->email)" required autocomplete="username" />
            <x-input-error class="mt-2" :messages="$errors->get('email')" />

            @if ($user instanceof \Illuminate\Contracts\Auth\MustVerifyEmail && ! $user->hasVerifiedEmail())
                <div>
                    <p class="text-sm mt-2 text-gray-800 dark:text-gray-200">
                        {{ __('Your email address is unverified.') }}

                        <button form="send-verification" class="underline text-sm text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 rounded-md focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800">
                            {{ __('Click here to re-send the verification email.') }}
                        </button>
                    </p>

                    @if (session('status') === 'verification-link-sent')
                        <p class="mt-2 font-medium text-sm text-green-600 dark:text-green-400">
                            {{ __('A new verification link has been sent to your email address.') }}
                        </p>
                    @endif
                </div>
            @endif
        </div>

        <div class="flex items-center gap-4">
            <x-primary-button>{{ __('Save') }}</x-primary-button>

            @if (session('status') === 'profile-updated')
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

    <div
        data-avatar-crop-modal
        class="fixed inset-0 z-[180] hidden"
        role="dialog"
        aria-modal="true"
        aria-labelledby="avatar-crop-title"
    >
        <button
            type="button"
            data-cancel-avatar-crop
            class="absolute inset-0 bg-gray-950/65 backdrop-blur-sm"
            aria-label="Cancel profile photo crop"
        ></button>

        <div class="relative flex min-h-full items-center justify-center p-4">
            <div
                class="w-full max-w-lg rounded-3xl border border-gray-200
                       bg-white p-6 shadow-2xl dark:border-gray-700
                       dark:bg-gray-900"
            >
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3
                            id="avatar-crop-title"
                            class="text-xl font-bold text-gray-900 dark:text-white"
                        >
                            Crop profile photo
                        </h3>

                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">
                            Drag the photo to position it inside the circle.
                        </p>
                    </div>

                    <button
                        type="button"
                        data-cancel-avatar-crop
                        class="flex h-10 w-10 items-center justify-center rounded-xl
                               text-gray-400 transition hover:bg-gray-100
                               hover:text-gray-700 dark:hover:bg-gray-800
                               dark:hover:text-white"
                        aria-label="Close"
                    >
                        <span class="material-symbols-rounded">close</span>
                    </button>
                </div>

                <div class="mx-auto mt-6 aspect-square w-full max-w-80
                            overflow-hidden rounded-full bg-gray-200 shadow-inner
                            ring-4 ring-indigo-100 dark:bg-gray-800
                            dark:ring-indigo-950">
                    <canvas
                        width="640"
                        height="640"
                        data-avatar-canvas
                        class="h-full w-full cursor-grab touch-none"
                    ></canvas>
                </div>

                <label class="mt-6 block">
                    <span class="text-xs font-semibold uppercase tracking-wide
                                 text-gray-500 dark:text-gray-400">
                        Zoom
                    </span>

                    <input
                        type="range"
                        min="1"
                        max="4"
                        step="0.01"
                        value="1"
                        data-avatar-zoom
                        class="mt-2 w-full accent-indigo-600"
                    >
                </label>

                <div class="mt-6 flex justify-end gap-2">
                    <button
                        type="button"
                        data-cancel-avatar-crop
                        class="rounded-xl px-4 py-2.5 text-sm font-semibold
                               text-gray-600 transition hover:bg-gray-100
                               dark:text-gray-300 dark:hover:bg-gray-800"
                    >
                        Cancel
                    </button>

                    <button
                        type="button"
                        data-confirm-avatar-crop
                        class="inline-flex items-center gap-2 rounded-xl
                               bg-indigo-600 px-4 py-2.5 text-sm font-semibold
                               text-white shadow-sm transition hover:bg-indigo-500"
                    >
                        <span class="material-symbols-rounded text-[19px]">crop</span>
                        Use photo
                    </button>
                </div>
            </div>
        </div>
    </div>
</section>
