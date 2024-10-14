@if($board->status == 0)
    <!-- Button to Activate Sprint -->
    <button
        onclick="document.getElementById('sprintStartPrompt').classList.toggle('hidden')"
        class="inline-flex items-center px-4 py-2 bg-white dark:bg-gray-800 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-gray-700 dark:text-gray-300 uppercase tracking-widest shadow-sm hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 disabled:opacity-25 transition ease-in-out duration-150"
    >
        Activate Sprint
    </button>
@else
    <!-- Disabled Button if Sprint is Active -->
    <button
        type="button"
        class="inline-flex items-center px-4 py-2 bg-green-600 dark:bg-green-600 border border-gray-300 dark:border-gray-500 rounded-md font-semibold text-xs text-white dark:text-white uppercase tracking-widest shadow-sm focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 dark:focus:ring-offset-gray-800 transition ease-in-out duration-150"
        disabled
    >
        Board is Active
    </button>
@endif

<div id="sprintStartPrompt" class="hidden fixed flex inset-0 items-center justify-center bg-black bg-opacity-50 z-50">
    <div class="bg-white dark:bg-gray-800 rounded-lg p-6 w-full max-w-lg">
        <div class="flex justify-between items-center pb-4 border-b">
            <h2 class="text-lg font-bold text-gray-800 dark:text-gray-100">Activate Sprint</h2>
            <button
                onclick="document.getElementById('sprintStartPrompt').classList.toggle('hidden')"
                class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-200"
            >
                &times;
            </button>
        </div>

        <!-- Modal Body -->
        <form method="POST" action="{{ route('boards.startSprint') }}" class="mt-4">
            @csrf
            <div class="mb-3 dark:text-white">
                <label for="board_id" class="form-label">Board ID</label>
                <input type="text" class="form-control dark:bg-transparent dark:text-white" id="board_id" name="board_id" value="{{ $board->id }}" readonly>
            </div>

            <!-- Start Date -->
            <div class="mb-4 dark:text-white">
                <label for="start_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Start Date</label>
                <input type="date" name="start_date" id="start_date" required class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md">
            </div>

            <!-- End Date -->
            <div class="mb-4 dark:text-white">
                <label for="end_date" class="block text-sm font-medium text-gray-700 dark:text-gray-300">End Date</label>
                <input type="date" name="end_date" id="end_date" required class="mt-1 block w-full p-2 border border-gray-300 dark:border-gray-600 dark:bg-gray-700 rounded-md">
            </div>

            <!-- Submit Button -->
            <div class="flex justify-end">
                <button type="submit" class="px-4 py-2 bg-green-600 dark:bg-green-600 text-white rounded-lg hover:bg-green-700 dark:hover:bg-green-700">
                    Start Sprint
                </button>
            </div>
        </form>
    </div>
</div>
