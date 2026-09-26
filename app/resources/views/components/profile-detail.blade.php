<div x-data="{ isProfileModalOpen: false }" class="relative z-50">
    <div
        x-show="isProfileModalOpen"
        @click.away="isProfileModalOpen = false"
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-0 z-50"
        x-cloak
    >
        <div class="absolute top-24 right-0 w-64 max-w-xs bg-white dark:bg-gray-800 rounded-lg p-6 shadow-lg">
            <div class="flex justify-between items-center pb-4 border-b">
                <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Profile Details</h2>
                <button @click="isProfileModalOpen = false" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200">
                    &times;
                </button>
            </div>

            <div class="mt-4">
                <div class="mb-4 flex justify-center">
                    <x-user-avatar :user="$user" size="xl" />
                </div>

                <div class="mb-6 text-gray-800 dark:text-gray-100">
                    <h1>Username: {{$user->name}}</h1>
                    @if (Auth::user()->admin == 1)
                        <h1 class="text-green-600">Admin</h1>
                    @endif
                </div>

                <!-- Edit Profile Button -->
                <form method="GET" action="{{ route('profile.edit') }}" class="mb-4">
                    @csrf
                    <button class="px-4 py-2 bg-blue-600 text-white rounded-lg hover:bg-blue-700 transition duration-150 ease-in-out">
                        Edit Profile
                    </button>
                </form>

                <!-- Logout Button -->
                <form method="POST" action="{{ route('logout') }}" class="mb-4">
                    @csrf
                    <button class="px-4 py-2 bg-red-600 text-white rounded-lg hover:bg-red-700 transition duration-150 ease-in-out">
                        Logout
                    </button>
                </form>

                <!-- Admin-only Button -->
                @if (Auth::user()->admin == 1)
                    <form method="GET" action="{{ route('profile.add-user') }}">
                        @csrf
                        <button class="px-4 py-2 bg-green-600 text-white rounded-lg hover:bg-green-700 transition duration-150 ease-in-out">
                            Manage Users
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>
</div>
