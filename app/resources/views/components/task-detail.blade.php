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

    <div class="max-h-[calc(100vh-11rem)] overflow-y-auto" data-task-detail-scroll>
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
                        rows="6"
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
                        <span class="material-symbols-rounded text-[20px] text-gray-400">event</span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Dates</h3>
                    </div>

                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Start date
                            <input
                                data-task-field="start_at"
                                type="datetime-local"
                                value="{{ $task->start_at?->format('Y-m-d\TH:i') }}"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3.5 py-2.5 text-sm text-gray-800 shadow-sm
                                       focus:border-indigo-400 focus:ring-indigo-100
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                        </label>

                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Due date
                            <input
                                data-task-field="due_at"
                                type="datetime-local"
                                value="{{ $task->due_at?->format('Y-m-d\TH:i') }}"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white
                                       px-3.5 py-2.5 text-sm text-gray-800 shadow-sm
                                       focus:border-indigo-400 focus:ring-indigo-100
                                       dark:border-gray-700 dark:bg-gray-900 dark:text-white"
                            >
                        </label>
                    </div>

                    <label class="mt-3 inline-flex items-center gap-2 text-sm font-medium
                                  text-gray-600 dark:text-gray-300">
                        <input
                            data-task-field="due_complete"
                            type="checkbox"
                            value="1"
                            @checked($task->due_complete)
                            class="rounded border-gray-300 text-indigo-600 focus:ring-indigo-500"
                        >
                        Mark the due date as complete
                    </label>
                </section>

                <section data-task-sections>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-[20px] text-gray-400">segment</span>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Sections</h3>
                        </div>
                        <button
                            type="button"
                            data-add-task-section
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5
                                   text-xs font-semibold text-indigo-600 transition
                                   hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50"
                        >
                            <span class="material-symbols-rounded text-[17px]">add</span>
                            Add section
                        </button>
                    </div>

                    <div class="space-y-3" data-task-section-list>
                        @foreach($task->sections as $section)
                            <div data-task-section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center gap-2">
                                    <input data-section-title type="text" value="{{ $section->title }}" maxlength="120" placeholder="Section title"
                                        class="min-w-0 flex-1 rounded-lg border-0 bg-transparent px-2 py-1.5 text-sm font-bold text-gray-800 focus:ring-2 focus:ring-indigo-400 dark:text-gray-100">
                                    <button type="button" data-remove-task-section class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" aria-label="Delete section">
                                        <span class="material-symbols-rounded text-[18px]">delete</span>
                                    </button>
                                </div>
                                <textarea data-section-content rows="3" maxlength="5000" placeholder="Add notes to this section..."
                                    class="mt-2 w-full resize-y rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200">{{ $section->content }}</textarea>
                            </div>
                        @endforeach
                    </div>
                    <p data-empty-sections class="{{ $task->sections->isEmpty() ? '' : 'hidden' }} rounded-xl border border-dashed border-gray-200 px-4 py-3 text-center text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                        Add a section for notes that deserve their own heading.
                    </p>
                </section>

                <section data-task-checklists>
                    <div class="mb-3 flex items-center justify-between gap-3">
                        <div class="flex items-center gap-2">
                            <span class="material-symbols-rounded text-[20px] text-gray-400">checklist</span>
                            <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Checklists</h3>
                        </div>
                        <button
                            type="button"
                            data-add-task-checklist
                            class="inline-flex items-center gap-1.5 rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 transition hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50"
                        >
                            <span class="material-symbols-rounded text-[17px]">add</span>
                            Add checklist
                        </button>
                    </div>

                    <div class="space-y-4" data-task-checklist-list>
                        @foreach($task->checklists as $checklist)
                            @php
                                $completeItems = $checklist->items->where('is_complete', true)->count();
                                $totalItems = $checklist->items->count();
                                $progress = $totalItems > 0 ? (int) round(($completeItems / $totalItems) * 100) : 0;
                            @endphp
                            <div data-task-checklist class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
                                <div class="flex items-center gap-2">
                                    <input data-checklist-title type="text" value="{{ $checklist->title }}" maxlength="120" placeholder="Checklist title"
                                        class="min-w-0 flex-1 rounded-lg border-0 bg-transparent px-2 py-1.5 text-sm font-bold text-gray-800 focus:ring-2 focus:ring-indigo-400 dark:text-gray-100">
                                    <button type="button" data-remove-task-checklist class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" aria-label="Delete checklist">
                                        <span class="material-symbols-rounded text-[18px]">delete</span>
                                    </button>
                                </div>
                                <div class="mt-3 flex items-center gap-3">
                                    <span data-checklist-progress-text class="w-9 text-xs text-gray-400">{{ $progress }}%</span>
                                    <div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800">
                                        <div data-checklist-progress-bar class="h-full rounded-full bg-emerald-500 transition-all" style="width: {{ $progress }}%"></div>
                                    </div>
                                </div>
                                <div class="mt-3 space-y-2" data-checklist-items>
                                    @foreach($checklist->items as $item)
                                        <div data-checklist-item class="flex items-center gap-2">
                                            <input data-checklist-item-complete type="checkbox" @checked($item->is_complete)
                                                class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
                                            <input data-checklist-item-content type="text" value="{{ $item->content }}" maxlength="500" placeholder="Checklist item"
                                                class="min-w-0 flex-1 rounded-lg border border-transparent bg-transparent px-2 py-1.5 text-sm text-gray-700 hover:border-gray-200 focus:border-indigo-400 focus:bg-white focus:ring-indigo-100 dark:text-gray-200 dark:hover:border-gray-700 dark:focus:bg-gray-900">
                                            <button type="button" data-remove-checklist-item class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-300 hover:bg-red-50 hover:text-red-500 dark:text-gray-600 dark:hover:bg-red-950/40" aria-label="Delete checklist item">
                                                <span class="material-symbols-rounded text-[17px]">close</span>
                                            </button>
                                        </div>
                                    @endforeach
                                </div>
                                <button type="button" data-add-checklist-item class="mt-3 inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200">
                                    <span class="material-symbols-rounded text-[16px]">add</span> Add item
                                </button>
                            </div>
                        @endforeach
                    </div>
                    <p data-empty-checklists class="{{ $task->checklists->isEmpty() ? '' : 'hidden' }} rounded-xl border border-dashed border-gray-200 px-4 py-3 text-center text-xs text-gray-400 dark:border-gray-700 dark:text-gray-500">
                        Break the work into a checklist and track each step.
                    </p>
                </section>

                <section class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="mb-4 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[20px] text-gray-400">monitoring</span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Estimates</h3>
                    </div>
                    <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Story points
                            <input data-task-field="storyPoint" type="number" min="0" value="{{ $task->story_points }}"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-800 shadow-sm focus:border-indigo-400 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </label>
                        <label class="text-xs font-semibold text-gray-500 dark:text-gray-400">
                            Time logged
                            <input data-task-field="timeLog" type="number" min="0" value="{{ $task->time_log }}"
                                class="mt-1.5 w-full rounded-xl border border-gray-200 bg-white px-3.5 py-2.5 text-sm text-gray-800 shadow-sm focus:border-indigo-400 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                        </label>
                    </div>
                </section>
            </div>

            <aside>
                <div class="rounded-2xl border border-gray-200 bg-gray-50/70 p-4 dark:border-gray-700 dark:bg-gray-800/50">
                    <div class="mb-5 flex items-center gap-2">
                        <span class="material-symbols-rounded text-[20px] text-gray-400">tune</span>
                        <h3 class="text-sm font-semibold text-gray-800 dark:text-gray-200">Details</h3>
                    </div>
                    <div class="space-y-4">
                        <div data-task-dropdown>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Column</p>
                            <input type="hidden" data-task-field="column_id" value="{{ $task->column_id }}">
                            <button type="button" data-task-dropdown-trigger class="mt-1.5 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-left text-sm text-gray-800 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <span data-task-dropdown-value>{{ $task->column->name }}</span>
                                <span class="material-symbols-rounded text-[18px] text-gray-400">expand_more</span>
                            </button>
                            <div data-task-dropdown-menu class="fixed z-[210] hidden max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1.5 shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                @foreach ($parent_board->columns as $column)
                                    <button type="button" data-task-dropdown-option data-value="{{ $column->id }}" data-label="{{ $column->name }}" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-200 dark:hover:bg-indigo-950/50 dark:hover:text-indigo-300">{{ $column->name }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div data-task-dropdown>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Assignee</p>
                            <input type="hidden" data-task-field="assignee" value="{{ $task->users->first()?->id }}">
                            <button type="button" data-task-dropdown-trigger class="mt-1.5 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-left text-sm text-gray-800 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <span data-task-dropdown-value>{{ $task->users->first()?->name ?? 'No one' }}</span>
                                <span class="material-symbols-rounded text-[18px] text-gray-400">expand_more</span>
                            </button>
                            <div data-task-dropdown-menu class="fixed z-[210] hidden max-h-64 overflow-y-auto rounded-xl border border-gray-200 bg-white p-1.5 shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                <button type="button" data-task-dropdown-option data-value="" data-label="No one" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-200 dark:hover:bg-indigo-950/50">No one</button>
                                @foreach ($users as $assignee)
                                    <button type="button" data-task-dropdown-option data-value="{{ $assignee->id }}" data-label="{{ $assignee->name }}" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-200 dark:hover:bg-indigo-950/50">
                                        <x-user-avatar :user="$assignee" size="xs" /> {{ $assignee->name }}
                                    </button>
                                @endforeach
                            </div>
                        </div>

                        <div data-task-label-picker data-board-id="{{ $parent_board->id }}">
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Labels</p>
                            <button type="button" data-task-label-trigger class="mt-1.5 flex min-h-11 w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2 text-left text-sm text-gray-800 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <span data-selected-labels class="flex flex-wrap gap-1.5">
                                    @forelse($task->boardLabels as $label)
                                        <span data-selected-label-id="{{ $label->id }}" class="inline-flex items-center gap-1.5 rounded-full border border-gray-200 px-2 py-0.5 text-xs font-semibold dark:border-gray-700">
                                            <span class="h-2 w-2 rounded-full" style="background-color: {{ $label->color }}"></span>{{ $label->name }}
                                        </span>
                                    @empty
                                        <span data-no-labels class="text-gray-400">No labels</span>
                                    @endforelse
                                </span>
                                <span class="material-symbols-rounded text-[18px] text-gray-400">expand_more</span>
                            </button>
                            <div data-task-label-menu class="fixed z-[210] hidden max-h-72 overflow-y-auto rounded-xl border border-gray-200 bg-white p-2 shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                <div data-task-label-options class="space-y-1">
                                    @forelse($parent_board->labels as $label)
                                        <button type="button" data-task-label-option data-label-id="{{ $label->id }}" data-label-name="{{ $label->name }}" data-label-color="{{ $label->color }}" aria-pressed="{{ $task->boardLabels->contains($label->id) ? 'true' : 'false' }}" class="flex w-full items-center gap-2 rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-gray-100 dark:text-gray-200 dark:hover:bg-gray-800">
                                            <span class="h-3 w-8 rounded" style="background-color: {{ $label->color }}"></span>
                                            <span class="min-w-0 flex-1 truncate">{{ $label->name }}</span>
                                            <span data-label-check class="material-symbols-rounded text-[18px] text-indigo-600 {{ $task->boardLabels->contains($label->id) ? '' : 'invisible' }}">check</span>
                                        </button>
                                    @empty
                                        <p data-empty-label-options class="px-3 py-2 text-xs text-gray-400">No labels yet</p>
                                    @endforelse
                                </div>
                                <button type="button" data-open-label-manager class="mt-2 flex w-full items-center gap-2 rounded-lg border-t border-gray-100 px-3 py-2.5 text-sm font-semibold text-indigo-600 hover:bg-indigo-50 dark:border-gray-800 dark:text-indigo-400 dark:hover:bg-indigo-950/40">
                                    <span class="material-symbols-rounded text-[18px]">edit</span> Create and manage labels
                                </button>
                            </div>
                        </div>

                        <div data-task-dropdown>
                            <p class="text-xs font-semibold text-gray-500 dark:text-gray-400">Priority</p>
                            <input type="hidden" data-task-field="priority" value="{{ $task->priority }}">
                            <button type="button" data-task-dropdown-trigger class="mt-1.5 flex w-full items-center justify-between rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-left text-sm text-gray-800 shadow-sm transition hover:border-indigo-300 dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                                <span data-task-dropdown-value>{{ $task->priority ?: 'No priority' }}</span>
                                <span class="material-symbols-rounded text-[18px] text-gray-400">expand_more</span>
                            </button>
                            <div data-task-dropdown-menu class="fixed z-[210] hidden rounded-xl border border-gray-200 bg-white p-1.5 shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                                @foreach (['' => 'No priority', 'Low' => 'Low', 'Medium' => 'Medium', 'High' => 'High'] as $value => $priority)
                                    <button type="button" data-task-dropdown-option data-value="{{ $value }}" data-label="{{ $priority }}" class="block w-full rounded-lg px-3 py-2 text-left text-sm text-gray-700 hover:bg-indigo-50 hover:text-indigo-700 dark:text-gray-200 dark:hover:bg-indigo-950/50">{{ $priority }}</button>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <p class="mb-1.5 text-xs font-semibold text-gray-500 dark:text-gray-400">Sprint</p>
                            <div class="flex items-center gap-2 rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm text-gray-700 shadow-sm dark:border-gray-700 dark:bg-gray-900 dark:text-gray-200">
                                <span class="material-symbols-rounded text-[18px] text-indigo-400">sprint</span>{{ $parent_board->name }}
                            </div>
                        </div>
                    </div>
                </div>
            </aside>
        </div>
    </div>

    <div class="flex flex-wrap items-center justify-between gap-3 border-t border-gray-200 bg-gray-50/70 px-5 py-4 sm:px-7 dark:border-gray-800 dark:bg-gray-900">
        <div class="min-h-5 flex-1">
            <p data-task-success class="hidden text-sm font-medium text-emerald-600 dark:text-emerald-400">Task saved successfully.</p>
            <p data-task-error class="hidden text-sm font-medium text-red-600 dark:text-red-400"></p>
        </div>
        <button type="button" data-delete-task class="inline-flex items-center gap-2 rounded-xl px-4 py-2.5 text-sm font-semibold text-red-600 transition hover:bg-red-50 dark:text-red-400 dark:hover:bg-red-950/40">
            <span class="material-symbols-rounded text-[18px]">delete</span>Delete
        </button>
        <button type="submit" data-save-task class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 disabled:cursor-not-allowed disabled:opacity-60">
            <span class="material-symbols-rounded text-[18px]">save</span><span data-save-text>Save changes</span>
        </button>
    </div>

    <div data-label-manager class="fixed inset-0 z-[220] hidden">
        <button type="button" data-close-label-manager class="absolute inset-0 bg-gray-950/60 backdrop-blur-sm" aria-label="Close label manager"></button>
        <div class="relative flex min-h-full items-center justify-center p-4">
            <div class="max-h-[90vh] w-full max-w-2xl overflow-y-auto rounded-3xl border border-gray-200 bg-white p-6 shadow-2xl dark:border-gray-700 dark:bg-gray-900">
                <div class="flex items-start justify-between gap-4">
                    <div>
                        <h3 class="text-xl font-bold text-gray-900 dark:text-white">Manage labels</h3>
                        <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Labels belong to this board. Save a list to reuse it elsewhere.</p>
                    </div>
                    <button type="button" data-close-label-manager class="flex h-9 w-9 items-center justify-center rounded-xl text-gray-400 hover:bg-gray-100 hover:text-gray-700 dark:hover:bg-gray-800 dark:hover:text-white"><span class="material-symbols-rounded">close</span></button>
                </div>

                <div class="mt-6 grid gap-6 md:grid-cols-2">
                    <section>
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">This board</h4>
                        <div data-board-label-manager-list class="mt-3 space-y-2">
                            @foreach($parent_board->labels as $label)
                                <div data-managed-label="{{ $label->id }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-700">
                                    <span class="h-5 w-10 rounded" style="background-color: {{ $label->color }}"></span>
                                    <span class="min-w-0 flex-1 truncate text-sm font-medium text-gray-700 dark:text-gray-200">{{ $label->name }}</span>
                                    <button type="button" data-delete-board-label="{{ $label->id }}" class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" aria-label="Delete {{ $label->name }}"><span class="material-symbols-rounded text-[18px]">delete</span></button>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-2xl bg-gray-50 p-3 dark:bg-gray-800/60">
                            <input type="text" data-new-label-name maxlength="50" placeholder="New label name" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <div class="mt-3 flex items-center gap-3">
                                <input type="color" data-new-label-color value="{{ $labelColors[5] ?? '#3b82f6' }}" class="h-10 w-14 cursor-pointer rounded-lg border border-gray-200 bg-white p-1 dark:border-gray-700 dark:bg-gray-900" aria-label="Label colour">
                                <button type="button" data-create-board-label class="flex-1 rounded-xl bg-indigo-600 px-3 py-2.5 text-sm font-semibold text-white hover:bg-indigo-500">Create label</button>
                            </div>
                        </div>
                    </section>

                    <section>
                        <h4 class="text-sm font-bold text-gray-800 dark:text-gray-200">Saved label lists</h4>
                        <div class="mt-3 space-y-2" data-label-library-list>
                            <div class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-700">
                                <span class="material-symbols-rounded text-[19px] text-indigo-500">bookmark</span>
                                <span class="min-w-0 flex-1 text-sm font-semibold text-gray-700 dark:text-gray-200">Standard labels</span>
                                <button type="button" data-import-label-library="standard" class="rounded-lg px-2.5 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50">Import</button>
                            </div>
                            @foreach($labelLibraries as $library)
                                <div data-label-library="{{ $library->id }}" class="flex items-center gap-2 rounded-xl border border-gray-200 px-3 py-2 dark:border-gray-700">
                                    <span class="material-symbols-rounded text-[19px] text-gray-400">bookmarks</span>
                                    <span class="min-w-0 flex-1 truncate text-sm font-semibold text-gray-700 dark:text-gray-200">{{ $library->name }}</span>
                                    <button type="button" data-import-label-library="{{ $library->id }}" class="rounded-lg px-2 py-1.5 text-xs font-semibold text-indigo-600 hover:bg-indigo-50 dark:text-indigo-400 dark:hover:bg-indigo-950/50">Import</button>
                                    <button type="button" data-delete-label-library="{{ $library->id }}" class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400" aria-label="Delete saved list"><span class="material-symbols-rounded text-[17px]">delete</span></button>
                                </div>
                            @endforeach
                        </div>
                        <div class="mt-4 rounded-2xl bg-gray-50 p-3 dark:bg-gray-800/60">
                            <input type="text" data-label-library-name maxlength="80" placeholder="Saved list name" class="w-full rounded-xl border border-gray-200 bg-white px-3 py-2.5 text-sm dark:border-gray-700 dark:bg-gray-900 dark:text-white">
                            <button type="button" data-save-label-library class="mt-2 w-full rounded-xl border border-indigo-200 bg-indigo-50 px-3 py-2.5 text-sm font-semibold text-indigo-700 hover:bg-indigo-100 dark:border-indigo-900 dark:bg-indigo-950/50 dark:text-indigo-300">Save this board's labels</button>
                        </div>
                    </section>
                </div>
                <p data-label-manager-error class="mt-4 hidden rounded-xl bg-red-50 px-3 py-2 text-sm text-red-600 dark:bg-red-950/40 dark:text-red-400"></p>
            </div>
        </div>
    </div>
</form>

<template id="task-section-template">
    <div data-task-section class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center gap-2">
            <input data-section-title type="text" value="Notes" maxlength="120" placeholder="Section title" class="min-w-0 flex-1 rounded-lg border-0 bg-transparent px-2 py-1.5 text-sm font-bold text-gray-800 focus:ring-2 focus:ring-indigo-400 dark:text-gray-100">
            <button type="button" data-remove-task-section class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"><span class="material-symbols-rounded text-[18px]">delete</span></button>
        </div>
        <textarea data-section-content rows="3" maxlength="5000" placeholder="Add notes to this section..." class="mt-2 w-full resize-y rounded-xl border border-gray-200 bg-gray-50 px-3 py-2.5 text-sm text-gray-700 focus:border-indigo-400 focus:ring-indigo-100 dark:border-gray-700 dark:bg-gray-800 dark:text-gray-200"></textarea>
    </div>
</template>

<template id="task-checklist-template">
    <div data-task-checklist class="rounded-2xl border border-gray-200 bg-white p-4 dark:border-gray-700 dark:bg-gray-900">
        <div class="flex items-center gap-2">
            <input data-checklist-title type="text" maxlength="120" value="Checklist" placeholder="Checklist title" class="min-w-0 flex-1 rounded-lg border-0 bg-transparent px-2 py-1.5 text-sm font-bold text-gray-800 focus:ring-2 focus:ring-indigo-400 dark:text-gray-100">
            <button type="button" data-remove-task-checklist class="flex h-8 w-8 items-center justify-center rounded-lg text-gray-400 hover:bg-red-50 hover:text-red-600 dark:hover:bg-red-950/40 dark:hover:text-red-400"><span class="material-symbols-rounded text-[18px]">delete</span></button>
        </div>
        <div class="mt-3 flex items-center gap-3"><span data-checklist-progress-text class="w-9 text-xs text-gray-400">0%</span><div class="h-2 flex-1 overflow-hidden rounded-full bg-gray-100 dark:bg-gray-800"><div data-checklist-progress-bar class="h-full rounded-full bg-emerald-500 transition-all" style="width: 0%"></div></div></div>
        <div class="mt-3 space-y-2" data-checklist-items></div>
        <button type="button" data-add-checklist-item class="mt-3 inline-flex items-center gap-1.5 rounded-lg px-2 py-1.5 text-xs font-semibold text-gray-500 hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"><span class="material-symbols-rounded text-[16px]">add</span> Add item</button>
    </div>
</template>

<template id="checklist-item-template">
    <div data-checklist-item class="flex items-center gap-2">
        <input data-checklist-item-complete type="checkbox" class="rounded border-gray-300 text-emerald-600 focus:ring-emerald-500">
        <input data-checklist-item-content type="text" maxlength="500" placeholder="Checklist item" class="min-w-0 flex-1 rounded-lg border border-transparent bg-transparent px-2 py-1.5 text-sm text-gray-700 hover:border-gray-200 focus:border-indigo-400 focus:bg-white focus:ring-indigo-100 dark:text-gray-200 dark:hover:border-gray-700 dark:focus:bg-gray-900">
        <button type="button" data-remove-checklist-item class="flex h-7 w-7 items-center justify-center rounded-lg text-gray-300 hover:bg-red-50 hover:text-red-500 dark:text-gray-600 dark:hover:bg-red-950/40"><span class="material-symbols-rounded text-[17px]">close</span></button>
    </div>
</template>
