<div
    class="relative z-10 w-full max-w-5xl
           overflow-hidden rounded-3xl
           border border-gray-200
           bg-white shadow-2xl
           dark:border-gray-700
           dark:bg-gray-900"
>

    <div
        class="flex items-start gap-4
               border-b border-gray-200
               px-5 py-5 sm:px-7
               dark:border-gray-800"
    >
        <div
            class="mt-1 flex h-10 w-10 shrink-0
                   items-center justify-center
                   rounded-xl bg-indigo-50
                   text-indigo-600
                   dark:bg-indigo-950/60
                   dark:text-indigo-400"
        >
            <span class="material-symbols-rounded">
                task_alt
            </span>
        </div>

        <div class="min-w-0 flex-1">
            <p
                class="mb-1 text-xs font-semibold uppercase
                       tracking-wider text-gray-400"
            >
                Task #{{ $task->id }}
            </p>

            <input
                id="formTitle{{ $task->id }}"
                type="text"
                value="{{ $task->title }}"
                placeholder="Task title"
                class="w-full rounded-xl
                       border border-transparent
                       bg-transparent px-3 py-2
                       text-xl font-bold
                       text-gray-900
                       transition
                       hover:border-gray-200
                       hover:bg-gray-50
                       focus:border-indigo-400
                       focus:bg-white
                       focus:ring-4
                       focus:ring-indigo-100
                       dark:text-white
                       dark:hover:border-gray-700
                       dark:hover:bg-gray-800
                       dark:focus:border-indigo-600
                       dark:focus:bg-gray-900
                       dark:focus:ring-indigo-950"
            >
        </div>

        <button
            type="button"
            id="xButton{{ $task->id }}"
            class="flex h-10 w-10 shrink-0
                   items-center justify-center
                   rounded-xl text-gray-400
                   transition
                   hover:bg-gray-100
                   hover:text-gray-700
                   dark:hover:bg-gray-800
                   dark:hover:text-white"
            aria-label="Close task"
        >
            <span class="material-symbols-rounded text-[22px]">
                close
            </span>
        </button>
    </div>


    <div
        class="max-h-[calc(100vh-11rem)]
               overflow-y-auto"
    >
        <div
            class="grid grid-cols-1 gap-6
                   p-5 sm:p-7
                   lg:grid-cols-[minmax(0,1.6fr)_minmax(280px,0.8fr)]"
        >

            <div class="space-y-6">

                <section>
                    <div class="mb-3 flex items-center gap-2">
                        <span
                            class="material-symbols-rounded
                                   text-[20px] text-gray-400"
                        >
                            subject
                        </span>

                        <h3
                            class="text-sm font-semibold
                                   text-gray-800
                                   dark:text-gray-200"
                        >
                            Description
                        </h3>
                    </div>

                    <textarea
                        id="formDescription{{ $task->id }}"
                        rows="8"
                        placeholder="Add a more detailed description..."
                        class="w-full resize-y
                               rounded-2xl
                               border border-gray-200
                               bg-gray-50/70
                               px-4 py-3
                               text-sm leading-6
                               text-gray-800
                               shadow-inner
                               transition
                               placeholder:text-gray-400
                               hover:border-gray-300
                               focus:border-indigo-400
                               focus:bg-white
                               focus:ring-4
                               focus:ring-indigo-100
                               dark:border-gray-700
                               dark:bg-gray-800/70
                               dark:text-gray-100
                               dark:placeholder:text-gray-500
                               dark:hover:border-gray-600
                               dark:focus:border-indigo-600
                               dark:focus:bg-gray-900
                               dark:focus:ring-indigo-950"
                    >{{ $task->description }}</textarea>
                </section>


                <section
                    class="rounded-2xl border border-gray-200
                           bg-gray-50/70 p-4
                           dark:border-gray-700
                           dark:bg-gray-800/50"
                >
                    <div class="mb-4 flex items-center gap-2">
                        <span
                            class="material-symbols-rounded
                                   text-[20px] text-gray-400"
                        >
                            monitoring
                        </span>

                        <h3
                            class="text-sm font-semibold
                                   text-gray-800
                                   dark:text-gray-200"
                        >
                            Estimates
                        </h3>
                    </div>

                    <div
                        class="grid grid-cols-1 gap-4
                               sm:grid-cols-2"
                    >
                        <div>
                            <label
                                for="formStoryPoint{{ $task->id }}"
                                class="mb-1.5 block
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Story points
                            </label>

                            <input
                                type="number"
                                min="0"
                                id="formStoryPoint{{ $task->id }}"
                                value="{{ $task->story_points }}"
                                placeholder="0"
                                class="w-full rounded-xl
                                       border border-gray-200
                                       bg-white px-3.5 py-2.5
                                       text-sm text-gray-800
                                       shadow-sm
                                       focus:border-indigo-400
                                       focus:ring-4
                                       focus:ring-indigo-100
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-white
                                       dark:focus:border-indigo-600
                                       dark:focus:ring-indigo-950"
                            >
                        </div>

                        <div>
                            <label
                                for="formTimeLog{{ $task->id }}"
                                class="mb-1.5 block
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Time logged
                            </label>

                            <input
                                type="number"
                                min="0"
                                id="formTimeLog{{ $task->id }}"
                                value="{{ $task->time_log }}"
                                placeholder="0"
                                class="w-full rounded-xl
                                       border border-gray-200
                                       bg-white px-3.5 py-2.5
                                       text-sm text-gray-800
                                       shadow-sm
                                       focus:border-indigo-400
                                       focus:ring-4
                                       focus:ring-indigo-100
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-white
                                       dark:focus:border-indigo-600
                                       dark:focus:ring-indigo-950"
                            >
                        </div>
                    </div>
                </section>


                <div
                    class="flex flex-wrap gap-x-6 gap-y-2
                           text-xs text-gray-400"
                >
                    <span>
                        Created:
                        {{ $task->created_at?->format('d M Y, H:i') }}
                    </span>

                    <span>
                        Updated:
                        {{ $task->updated_at?->format('d M Y, H:i') }}
                    </span>
                </div>

            </div>


            <aside>
                <div
                    class="rounded-2xl
                           border border-gray-200
                           bg-gray-50/70 p-4
                           dark:border-gray-700
                           dark:bg-gray-800/50"
                >
                    <div class="mb-5 flex items-center gap-2">
                        <span
                            class="material-symbols-rounded
                                   text-[20px] text-gray-400"
                        >
                            tune
                        </span>

                        <h3
                            class="text-sm font-semibold
                                   text-gray-800
                                   dark:text-gray-200"
                        >
                            Details
                        </h3>
                    </div>


                    <div class="space-y-4">

                        <div>
                            <label
                                for="formColumn{{ $task->id }}"
                                class="mb-1.5 block
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Status
                            </label>

                            <select
                                id="formColumn{{ $task->id }}"
                                class="w-full rounded-xl
                                       border border-gray-200
                                       bg-white px-3 py-2.5
                                       text-sm text-gray-800
                                       shadow-sm
                                       focus:border-indigo-400
                                       focus:ring-4
                                       focus:ring-indigo-100
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-white
                                       dark:focus:border-indigo-600
                                       dark:focus:ring-indigo-950"
                            >
                                @foreach ($parent_board->columns as $column)
                                    <option
                                        value="{{ $column->id }}"
                                        @selected($task->column_id == $column->id)
                                    >
                                        {{ $column->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div>
                            <label
                                for="formAssignee{{ $task->id }}"
                                class="mb-1.5 block
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Assignee
                            </label>

                            <select
                                id="formAssignee{{ $task->id }}"
                                class="w-full rounded-xl
                                       border border-gray-200
                                       bg-white px-3 py-2.5
                                       text-sm text-gray-800
                                       shadow-sm
                                       focus:border-indigo-400
                                       focus:ring-4
                                       focus:ring-indigo-100
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-white
                                       dark:focus:border-indigo-600
                                       dark:focus:ring-indigo-950"
                            >
                                <option value="">
                                    No one
                                </option>

                                @foreach ($users as $assignee)
                                    <option value="{{ $assignee->id }}">
                                        {{ $assignee->name }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div>
                            <label
                                for="formLabels{{ $task->id }}"
                                class="mb-1.5 block
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Label
                            </label>

                            <select
                                id="formLabels{{ $task->id }}"
                                class="w-full rounded-xl
                                       border border-gray-200
                                       bg-white px-3 py-2.5
                                       text-sm text-gray-800
                                       shadow-sm
                                       focus:border-indigo-400
                                       focus:ring-4
                                       focus:ring-indigo-100
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-white
                                       dark:focus:border-indigo-600
                                       dark:focus:ring-indigo-950"
                            >
                                <option value="">
                                    No label
                                </option>

                                @foreach (
                                    [
                                        'API',
                                        'Backend',
                                        'Frontend',
                                        'UI/UX',
                                        'Database'
                                    ] as $label
                                )
                                    <option
                                        value="{{ $label }}"
                                        @selected($task->labels == $label)
                                    >
                                        {{ $label }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div>
                            <label
                                for="formPriority{{ $task->id }}"
                                class="mb-1.5 block
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Priority
                            </label>

                            <select
                                id="formPriority{{ $task->id }}"
                                class="w-full rounded-xl
                                       border border-gray-200
                                       bg-white px-3 py-2.5
                                       text-sm text-gray-800
                                       shadow-sm
                                       focus:border-indigo-400
                                       focus:ring-4
                                       focus:ring-indigo-100
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-white
                                       dark:focus:border-indigo-600
                                       dark:focus:ring-indigo-950"
                            >
                                <option value="">
                                    No priority
                                </option>

                                @foreach (
                                    ['Low', 'Medium', 'High']
                                    as $priority
                                )
                                    <option
                                        value="{{ $priority }}"
                                        @selected($task->priority == $priority)
                                    >
                                        {{ $priority }}
                                    </option>
                                @endforeach
                            </select>
                        </div>


                        <div>
                            <p
                                class="mb-1.5
                                       text-xs font-semibold
                                       text-gray-500
                                       dark:text-gray-400"
                            >
                                Sprint
                            </p>

                            <div
                                class="flex items-center gap-2
                                       rounded-xl
                                       border border-gray-200
                                       bg-white px-3 py-2.5
                                       text-sm text-gray-700
                                       shadow-sm
                                       dark:border-gray-700
                                       dark:bg-gray-900
                                       dark:text-gray-200"
                            >
                                <span
                                    class="material-symbols-rounded
                                           text-[18px]
                                           text-indigo-400"
                                >
                                    sprint
                                </span>

                                {{ $parent_board->name }}
                            </div>
                        </div>

                    </div>
                </div>
            </aside>

        </div>
    </div>


    {{-- Footer --}}
    <div
        class="flex items-center justify-between
               border-t border-gray-200
               bg-gray-50/70
               px-5 py-4 sm:px-7
               dark:border-gray-800
               dark:bg-gray-900"
    >
        <div class="min-h-5">
            <p
                id="task-success-{{ $task->id }}"
                class="hidden text-sm font-medium
                       text-emerald-600
                       dark:text-emerald-400"
            >
                Task saved successfully.
            </p>

            <p
                id="task-fail-{{ $task->id }}"
                class="hidden text-sm font-medium
                       text-red-600
                       dark:text-red-400"
            ></p>
        </div>

        <button
            type="button"
            id="saveTaskButton{{ $task->id }}"
            class="inline-flex items-center gap-2
                   rounded-xl bg-indigo-600
                   px-4 py-2.5
                   text-sm font-semibold
                   text-white shadow-sm
                   transition
                   hover:bg-indigo-500
                   focus:outline-none
                   focus:ring-4
                   focus:ring-indigo-200
                   disabled:cursor-not-allowed
                   disabled:opacity-60
                   dark:focus:ring-indigo-950"
        >
            <span
                class="material-symbols-rounded text-[18px]"
            >
                save
            </span>

            <span data-save-text>
                Save changes
            </span>
        </button>
    </div>

</div>


<script>
document.addEventListener('DOMContentLoaded', function () {

    const taskId = {{ $task->id }};

    const saveButton =
        document.getElementById(
            `saveTaskButton${taskId}`
        );

    const closeButton =
        document.getElementById(
            `xButton${taskId}`
        );

    const successMessage =
        document.getElementById(
            `task-success-${taskId}`
        );

    const failMessage =
        document.getElementById(
            `task-fail-${taskId}`
        );

    const modal =
        document.getElementById(
            `task-list-detail-${taskId}`
        );


    closeButton.addEventListener('click', function () {

        modal.dispatchEvent(
            new CustomEvent('task-detail:close')
        );

    });


    saveButton.addEventListener(
        'click',
        async function () {

            const title =
                document.getElementById(
                    `formTitle${taskId}`
                ).value.trim();

            const description =
                document.getElementById(
                    `formDescription${taskId}`
                ).value.trim();

            const assignee =
                document.getElementById(
                    `formAssignee${taskId}`
                ).value;

            const columnId =
                document.getElementById(
                    `formColumn${taskId}`
                ).value;

            const labels =
                document.getElementById(
                    `formLabels${taskId}`
                ).value;

            const priority =
                document.getElementById(
                    `formPriority${taskId}`
                ).value;

            const storyPoint =
                document.getElementById(
                    `formStoryPoint${taskId}`
                ).value;

            const timeLog =
                document.getElementById(
                    `formTimeLog${taskId}`
                ).value;


            if (!title) {

                failMessage.textContent =
                    'Task title cannot be empty.';

                failMessage.classList.remove('hidden');

                return;
            }


            successMessage.classList.add('hidden');
            failMessage.classList.add('hidden');

            saveButton.disabled = true;

            const saveText =
                saveButton.querySelector(
                    '[data-save-text]'
                );

            saveText.textContent = 'Saving...';


            try {

                const response = await fetch(
                    '{{ route('tasks.update', ['task' => $task->id]) }}',
                    {
                        method: 'PATCH',

                        headers: {
                            'Content-Type':
                                'application/json',

                            'Accept':
                                'application/json',

                            'X-CSRF-TOKEN':
                                document
                                    .querySelector(
                                        'meta[name="csrf-token"]'
                                    )
                                    .getAttribute(
                                        'content'
                                    )
                        },

                        body: JSON.stringify({
                            column_id:
                                Number(columnId),

                            title: title,

                            description:
                                description || null,

                            assignee:
                                assignee
                                    ? Number(assignee)
                                    : null,

                            labels:
                                labels || null,

                            priority:
                                priority || null,

                            storyPoint:
                                Number(
                                    storyPoint || 0
                                ),

                            timeLog:
                                Number(
                                    timeLog || 0
                                )
                        })
                    }
                );


                const data =
                    await response.json();


                if (!response.ok || !data.success) {

                    let message =
                        data.message ??
                        'Could not save task.';

                    if (data.errors) {
                        message =
                            Object
                                .values(data.errors)
                                .flat()
                                .join(' ');
                    }

                    throw new Error(message);
                }


                successMessage.classList.remove(
                    'hidden'
                );


                const taskTitle =
                    document.querySelector(
                        `#task-list-item-${taskId}
                         [data-task-title]`
                    );

                if (taskTitle) {
                    taskTitle.textContent = title;
                }

                setTimeout(function () {
                    window.location.reload();
                }, 450);

            } catch (error) {

                failMessage.textContent =
                    error.message ??
                    'Could not save task.';

                failMessage.classList.remove(
                    'hidden'
                );

            } finally {

                saveButton.disabled = false;

                saveText.textContent =
                    'Save changes';

            }
        }
    );

});
</script>
