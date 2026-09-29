<section
    x-data="{
        expanded: @js($errors->updatePassword->any()),
        showPasswords: false,
    }"
    data-password-settings
>
    <div class="flex flex-col gap-5 sm:flex-row sm:items-center sm:justify-between">
        <div class="flex min-w-0 items-start gap-4">
            <span
                class="flex h-11 w-11 shrink-0 items-center justify-center
                       rounded-xl bg-indigo-50 text-indigo-600
                       dark:bg-indigo-950/60 dark:text-indigo-400"
            >
                <span class="material-symbols-rounded text-[22px]">
                    password
                </span>
            </span>

            <div>
                <h2 class="text-lg font-semibold text-gray-900 dark:text-gray-100">
                    {{ __('Password') }}
                </h2>

                <p class="mt-1 text-sm leading-6 text-gray-600 dark:text-gray-400">
                    {{ __('Use a unique passphrase to protect your account.') }}
                </p>

                @if (session('status') === 'password-updated')
                    <p
                        class="mt-2 inline-flex items-center gap-1.5 text-sm
                               font-medium text-emerald-600 dark:text-emerald-400"
                        role="status"
                    >
                        <span class="material-symbols-rounded text-[18px]">
                            check_circle
                        </span>
                        {{ __('Password updated successfully.') }}
                    </p>
                @endif
            </div>
        </div>

        <button
            type="button"
            data-toggle-password-settings
            @click="expanded = !expanded"
            :aria-expanded="expanded.toString()"
            aria-controls="password-update-panel"
            class="inline-flex shrink-0 items-center justify-center gap-2
                   rounded-xl border border-gray-300 bg-white px-4 py-2.5
                   text-sm font-semibold text-gray-700 shadow-sm transition
                   hover:border-indigo-300 hover:bg-indigo-50
                   hover:text-indigo-700 focus:outline-none focus:ring-4
                   focus:ring-indigo-100 dark:border-gray-600
                   dark:bg-gray-900 dark:text-gray-200
                   dark:hover:border-indigo-700 dark:hover:bg-indigo-950/40
                   dark:hover:text-indigo-300 dark:focus:ring-indigo-950"
        >
            <span class="material-symbols-rounded text-[19px]">
                lock_reset
            </span>
            <span x-text="expanded ? '{{ __('Close') }}' : '{{ __('Change password') }}'">
                {{ __('Change password') }}
            </span>
        </button>
    </div>

    <div
        id="password-update-panel"
        x-cloak
        x-show="expanded"
        x-transition:enter="transition ease-out duration-200"
        x-transition:enter-start="opacity-0 -translate-y-2"
        x-transition:enter-end="opacity-100 translate-y-0"
        x-transition:leave="transition ease-in duration-150"
        x-transition:leave-start="opacity-100 translate-y-0"
        x-transition:leave-end="opacity-0 -translate-y-2"
        class="mt-6 border-t border-gray-200 pt-6 dark:border-gray-700"
    >
        <div
            class="mb-6 flex items-start gap-3 rounded-xl border
                   border-blue-200 bg-blue-50 px-4 py-3
                   text-sm leading-6 text-blue-800
                   dark:border-blue-900 dark:bg-blue-950/40
                   dark:text-blue-300"
        >
            <span class="material-symbols-rounded mt-0.5 text-[19px]">
                shield_lock
            </span>
            <p>
                Use at least 12 characters. A memorable passphrase is safer than
                reusing a password from another service.
            </p>
        </div>

        <form
            method="post"
            action="{{ route('password.update') }}"
            class="space-y-5"
            x-ref="passwordForm"
        >
            @csrf
            @method('put')

            <div>
                <div class="flex items-center justify-between gap-3">
                    <x-input-label
                        for="update_password_current_password"
                        :value="__('Current password')"
                    />

                    @if (Route::has('password.request'))
                        <a
                            href="{{ route('password.request') }}"
                            class="text-xs font-semibold text-indigo-600
                                   hover:text-indigo-500 dark:text-indigo-400"
                        >
                            {{ __('Forgot password?') }}
                        </a>
                    @endif
                </div>

                <x-text-input
                    id="update_password_current_password"
                    name="current_password"
                    ::type="showPasswords ? 'text' : 'password'"
                    class="mt-1.5 block w-full"
                    autocomplete="current-password"
                    required
                />
                <x-input-error
                    :messages="$errors->updatePassword->get('current_password')"
                    class="mt-2"
                />
            </div>

            <div class="grid gap-5 md:grid-cols-2">
                <div>
                    <x-input-label
                        for="update_password_password"
                        :value="__('New password')"
                    />
                    <x-text-input
                        id="update_password_password"
                        name="password"
                        ::type="showPasswords ? 'text' : 'password'"
                        class="mt-1.5 block w-full"
                        autocomplete="new-password"
                        minlength="12"
                        required
                    />
                    <x-input-error
                        :messages="$errors->updatePassword->get('password')"
                        class="mt-2"
                    />
                </div>

                <div>
                    <x-input-label
                        for="update_password_password_confirmation"
                        :value="__('Confirm new password')"
                    />
                    <x-text-input
                        id="update_password_password_confirmation"
                        name="password_confirmation"
                        ::type="showPasswords ? 'text' : 'password'"
                        class="mt-1.5 block w-full"
                        autocomplete="new-password"
                        minlength="12"
                        required
                    />
                    <x-input-error
                        :messages="$errors->updatePassword->get('password_confirmation')"
                        class="mt-2"
                    />
                </div>
            </div>

            <label
                class="inline-flex cursor-pointer items-center gap-2
                       text-sm text-gray-600 dark:text-gray-300"
            >
                <input
                    type="checkbox"
                    x-model="showPasswords"
                    class="rounded border-gray-300 text-indigo-600
                           focus:ring-indigo-500 dark:border-gray-600
                           dark:bg-gray-900"
                >
                {{ __('Show passwords') }}
            </label>

            <div class="flex flex-wrap items-center justify-end gap-3">
                <button
                    type="button"
                    @click="
                        expanded = false;
                        showPasswords = false;
                        $refs.passwordForm.reset();
                    "
                    class="rounded-xl px-4 py-2.5 text-sm font-semibold
                           text-gray-600 transition hover:bg-gray-100
                           dark:text-gray-300 dark:hover:bg-gray-700"
                >
                    {{ __('Cancel') }}
                </button>

                <x-primary-button>
                    <span class="material-symbols-rounded text-[19px]">
                        lock_reset
                    </span>
                    {{ __('Update password') }}
                </x-primary-button>
            </div>
        </form>
    </div>
</section>
