<form
    data-task-detail
    data-task-id="{{ $task->id }}"
    data-task-version="{{ $task->version ?? 1 }}"
    class="relative z-10 w-full max-w-5xl overflow-hidden rounded-3xl
           border border-gray-200 bg-white shadow-2xl
           dark:border-gray-700 dark:bg-gray-900"
>
    <div
        class="flex items-start gap-4 border-b border-gray-200 px-5 py-5
               sm:px-7 dark:border-gray-800"
    >
        <div
            class="mt-1 flex h-10 w-10 shrink-0 items-center justify-center
                   rounded-xl bg-indigo-50 text-indigo-600
                   dark:bg-indigo-950/60 dark:text-indigo-400"
        >
            <span class="material-symbols-rounded">task_alt</span>
        </div>

        <div class="min-w-0 flex-1">
            <p class="mb-1 text-xs font-semibold uppercase tracking-wider text-gray-400">
                Task #{{ $task->id }}
            </p>
            <input
                data-task-field="title"
                type="text"
                value="{{ $task->title }}"
                placeholder="Task title"
                class="w-full rounded-xl border border-transparent bg-transparent
                       px-3 py-2 text-xl font-bold text-gray-900 transition
                       hover:border-gray-200 hover:bg-gray-50
                       focus:border-indigo-400 focus:bg-white focus:ring-4
                       focus:ring-indigo-100 dark:text-white
                       dark:hover:border-gray-700 dark:hover:bg-gray-800
                       dark:focus:border-indigo-600 dark:focus:bg-gray-900
                       dark:focus:ring-indigo-950"
            >
        </div>

        <button
            type="button"
            data-close-task-modal
            class="flex h-10 w-10 shrink-0 items-center justify-center rounded-xl
                   text-gray-400 transition hover:bg-gray-100 hover:text-gray-700
                   dark:hover:bg-gray-800 dark:hover:text-white"
            aria-label="Close task"
        >
            <span class="material-symbols-rounded text-[22px]">close</span>
        </button>
    </div>

    <div class="max-h-[calc(100vh-11rem)] overflow-y-auto">
        <div
            class="grid grid-cols-1 gap-6 p-5 sm:p-7
                   lg:grid-cols-[minmax(0,1.6fr)_minmax(280px,0.8fr)]"
        >
            <div class="space-y-6">
                <section>
                    <div class="mb-3 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[20px] text-gray-400">subject</span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                            Description
                        </h3>
                    </div>
                    <textarea
                        data-task-field="description"
                        rows="8"
                        placeholder="Add a more detailed description..."
                        class="w-full resize-y rounded-2xl border border-gray-200
                               bg-gray-50/70 px-4 py-3 text-sm leading-6 text-gray-800
                               shadow-inner transition placeholder:text-gray-400
                               hover:border-gray-300 focus:border-indigo-400 focus:bg-white
                               focus:ring-4 focus:ring-indigo-100 dark:border-gray-700
                               dark:bg-gray-800/70 dark:text-gray-100
                               dark:placeholder:text-gray-500 dark:hover:border-gray-600
                               dark:focus:border-indigo-600 dark:focus:bg-gray-900
                               dark:focus:ring-indigo-950"
                    >{{ $task->description }}</textarea>
                </section>

                <section
                    class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4
                           dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[20px] text-gray-400">monitoring</span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Estimates</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Story points
                            <input
                                data-task-field="storyPoint"
                                type="number"
                                min="0"
                                value="{{ $task->story_points }}"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3.5 py-2.5 text-sm text-gray-800 shadow-sm
                                       focus:border-indigo-400 focus:ring-indigo-100
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                        </label>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Time logged
                            <input
                                data-task-field="timeLog"
                                type="number"
                                min="0"
                                value="{{ $task->time_log }}"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3.5 py-2.5 text-sm text-gray-800 shadow-sm
                                       focus:border-indigo-400 focus:ring-indigo-100
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                        </label>
                    </div>
                </section>
            </div>

            <aside>
                <div
                    class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4
                           dark:border-gray-700 dark:bg-gray-800/50"
                >
                    <div class="mb-5 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[20px] text-gray-400">tune</span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Details</h3>
                    </div>
                    <div class="space-y-4">
                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Status
                            <select
                                data-task-field="column_id"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3 py-2.5 text-sm text-gray-800 shadow-sm
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                @foreach ($parent_board->columns as $column)
                                    <option value="{{ $column->id }}" @selected($task->column_id == $column->id)>
                                        {{ $column->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Assignee
                            <select
                                data-task-field="assignee"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3 py-2.5 text-sm text-gray-800 shadow-sm
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="">No one</option>
                                @foreach ($users as $assignee)
                                    <option
                                        value="{{ $assignee->id }}"
                                        @selected($task->users->contains($assignee->id))
                                    >
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Label
                            <select
                                data-task-field="labels"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3 py-2.5 text-sm text-gray-800 shadow-sm
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="">No label</option>
                                @foreach (['API', 'Backend', 'Frontend', 'UI/UX', 'Database'] as $label)
                                    <option value="{{ $label }}" @selected($task->labels == $label)>
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <label class="block text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Priority
                            <select
                                data-task-field="priority"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3 py-2.5 text-sm text-gray-800 shadow-sm
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                                <option value="">No priority</option>
                                @foreach (['Low', 'Medium', 'High'] as $priority)
                                    <option value="{{ $priority }}" @selected($task->priority == $priority)>
                                        {{ $priority }}
                                    </option>
                                @endforeach
                            </select>
                        </label>

                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400">Sprint</p>
                            <div
                                class="flex items-center gap-2 rounded-xl border border-gray-200
                                       bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200"
                            >
                                <span class="material-symbols-rounded text-[18px] text-indigo-400">sprint</span>
                                {{ $parent_board->name }}
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div
        class="flex flex-wrap items-center justify-between gap-3 border-t
               border-gray-200 bg-gray-50/70 px-5 py-4 sm:px-7
               dark:border-gray-800 dark:bg-gray-900"
    >
        <div class="min-h-5 flex-1">
            <p data-task-success class="hidden text-sm font-medium text-emerald-600 dark:text-emerald-400">
                Task saved successfully.
            </p>
            <p data-task-error class="hidden text-sm font-medium text-red-600 dark:text-red-400"></p>
        </div>

        <button
            type="button"
            data-delete-task
            class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5
                   text-sm font-semibold text-red-600 transition hover:bg-red-50
                   dark:text-red-400 dark:hover:bg-red-950/40"
        >
            <span class="material-symbols-rounded text-[18px]">delete</span>
            Delete
        </button>

        <button
            type="submit"
            data-save-task
            class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5
                   text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500
                   disabled:cursor-not-allowed disabled:opacity-60"
        >
            <span class="material-symbols-rounded text-[18px]">save</span>
            <span data-save-text>Save changes</span>
        </button>
    </div>
</form>
