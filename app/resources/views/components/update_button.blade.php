<div class="mb-4">
    <!-- Edit Button -->
    <button
        onclick="document.getElementById('editUserForm-{{$user->id}}').classList.toggle('hidden')"
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-blue-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-blue-800 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
    >
        Edit User
    </button>
</div>

<!-- Hidden Form -->
<div id="editUserForm-{{$user->id}}" class="hidden">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg">
        <div class="flex justify-between items-center pb-4 border-b">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">{{ $user->name }}</h2>
            <button
                onclick="document.getElementById('editUserForm-{{$user->id}}').classList.toggle('hidden')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            >
                &times;
            </button>
        </div>
        <form method="POST" action="{{ route('profile.user_update') }}">
            @csrf
            @method('PATCH')

            <div class="mb-3 dark:text-white">
                <label for="user_id" class="form-label">User ID</label>
                <input type="text" class="form-control dark:bg-transparent dark:text-white" id="user_id" name="user_id" value="{{ $user->id }}" readonly>
            </div>

            <div class="mb-4 dark:text-white">
                <label for="name" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Username</label>
                <input type="text" name="name" id="name" required value="{{ old('name', $user->name) }}" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md">
            </div>

            <div class="mb-4 dark:text-white">
                <label for="email" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Email</label>
                <input type="email" name="email" id="email" required value="{{ old('email', $user->email) }}" class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md">
            </div>

            {{-- Save new user --}}
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-green-600 dark:bg-green-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-green-700">
                    Update Details
                </button>
            </div>
        </form>
    </div>
</div>
