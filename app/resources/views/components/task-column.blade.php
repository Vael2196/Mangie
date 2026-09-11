@props([
    'column',
    'editable' => true,
])

@php
    $palette = [
        'gray' => [
            'column' =>
                'border-gray-200 bg-gray-100/80
                 dark:border-gray-700 dark:bg-gray-800/80',

            'swatch' => 'bg-gray-400',
        ],

        'blue' => [
            'column' =>
                'border-blue-200 bg-blue-50/90
                 dark:border-blue-900 dark:bg-blue-950/40',

            'swatch' => 'bg-blue-500',
        ],

        'green' => [
            'column' =>
                'border-emerald-200 bg-emerald-50/90
                 dark:border-emerald-900 dark:bg-emerald-950/40',

            'swatch' => 'bg-emerald-500',
        ],

        'yellow' => [
            'column' =>
                'border-amber-200 bg-amber-50/90
                 dark:border-amber-900 dark:bg-amber-950/40',

            'swatch' => 'bg-amber-400',
        ],

        'orange' => [
            'column' =>
                'border-orange-200 bg-orange-50/90
                 dark:border-orange-900 dark:bg-orange-950/40',

            'swatch' => 'bg-orange-500',
        ],

        'red' => [
            'column' =>
                'border-rose-200 bg-rose-50/90
                 dark:border-rose-900 dark:bg-rose-950/40',

            'swatch' => 'bg-rose-500',
        ],

        'purple' => [
            'column' =>
                'border-violet-200 bg-violet-50/90
                 dark:border-violet-900 dark:bg-violet-950/40',

            'swatch' => 'bg-violet-500',
        ],

        'pink' => [
            'column' =>
                'border-pink-200 bg-pink-50/90
                 dark:border-pink-900 dark:bg-pink-950/40',

            'swatch' => 'bg-pink-500',
        ],
    ];


    $currentColor =
        array_key_exists(
            $column->color ?? 'gray',
            $palette
        )
            ? $column->color
            : 'gray';


    $canDelete =
        ($column->tasks_count ?? 0) === 0;

    $taskCount = (int) ($column->tasks_count ?? 0);

@endphp


<div
    id="column-{{ $column->id }}"
    data-column-id="{{ $column->id }}"
    data-column-name="{{ $column->name }}"
    {{ $attributes->merge([
        'class' =>
            'flex h-max min-h-40 w-72 shrink-0 flex-col
             rounded-2xl border p-3 shadow-sm
             transition-colors duration-200 ' .
             $palette[$currentColor]['column']
    ]) }}
>
    <div
        class="relative mb-3 flex
            items-center justify-between
            gap-2 px-1 py-1"
    >

        <div class="min-w-0 flex-1">

            @if($editable)

                <button
                    type="button"
                    data-column-name-display
                    class="group flex max-w-full items-center
                        rounded-lg px-1.5 py-1
                        text-left transition
                        hover:bg-black/5
                        dark:hover:bg-white/10"
                    title="Click to rename column"
                >
                    <span
                        data-column-name-text
                        class="truncate text-sm font-bold
                            uppercase tracking-wide
                            text-gray-700
                            dark:text-gray-200"
                    >
                        {{ $column->name }}
                    </span>
                </button>

                <div
                    data-column-name-editor
                    class="hidden"
                >
                    <input
                        type="text"
                        data-column-name-input
                        value="{{ $column->name }}"
                        maxlength="255"
                        autocomplete="off"
                        class="w-full rounded-lg
                            border border-indigo-300
                            bg-white px-2.5 py-1.5
                            text-sm font-semibold
                            text-gray-800 shadow-sm
                            outline-none
                            transition
                            focus:border-indigo-500
                            focus:ring-2
                            focus:ring-indigo-200
                            dark:border-indigo-700
                            dark:bg-gray-900
                            dark:text-gray-100
                            dark:focus:border-indigo-500
                            dark:focus:ring-indigo-950"
                    >

                    <p
                        data-column-name-error
                        class="mt-1 hidden
                            text-xs text-red-600
                            dark:text-red-400"
                    ></p>
                </div>

            @else

                <h2
                    class="truncate text-sm font-bold
                        uppercase tracking-wide
                        text-gray-700
                        dark:text-gray-200"
                >
                    {{ $column->name }}
                </h2>

            @endif

        </div>


        <div
            class="flex shrink-0 items-center gap-1"
        >

            <div
                data-column-task-count
                class="flex h-8 items-center gap-1
                    rounded-lg px-2
                    text-xs font-semibold
                    text-gray-500
                    dark:text-gray-400"
                title="{{ $taskCount }} {{ $taskCount === 1 ? 'task' : 'tasks' }}"
            >
                <span
                    class="material-symbols-rounded
                        text-[17px]"
                >
                    task_alt
                </span>

                <span data-task-count-value>
                    {{ $taskCount }}
                </span>
            </div>

        @if($editable)

            <div
                class="relative"
                data-column-menu
                data-column-id="{{ $column->id }}"
                data-column-name="{{ $column->name }}"
            >

                <button
                    type="button"
                    data-column-menu-toggle
                    class="flex h-8 w-8 items-center
                           justify-center rounded-lg
                           text-gray-400 transition
                           hover:bg-black/5
                           hover:text-gray-700
                           dark:hover:bg-white/10
                           dark:hover:text-gray-100"
                    aria-label="Column actions"
                    aria-expanded="false"
                >
                    <span
                        class="material-symbols-rounded
                               text-[20px]"
                    >
                        more_horiz
                    </span>
                </button>

                <div
                    data-column-menu-panel
                    class="absolute right-0 top-10 z-50
                           hidden w-64 overflow-hidden
                           rounded-2xl
                           border border-gray-200
                           bg-white shadow-2xl
                           dark:border-gray-700
                           dark:bg-gray-900"
                >

                    <div
                        class="border-b border-gray-100
                               px-4 py-3
                               dark:border-gray-800"
                    >
                        <p
                            class="text-sm font-semibold
                                   text-gray-800
                                   dark:text-gray-100"
                        >
                            Column actions
                        </p>
                    </div>


                    <div class="p-2">

                        <div class="px-2 pb-3 pt-1">

                            <div
                                class="mb-2 flex items-center gap-2"
                            >
                                <span
                                    class="material-symbols-rounded
                                           text-[19px]
                                           text-gray-400"
                                >
                                    palette
                                </span>

                                <span
                                    class="text-sm font-medium
                                           text-gray-700
                                           dark:text-gray-200"
                                >
                                    Change colour
                                </span>
                            </div>


                            <div
                                class="grid grid-cols-8
                                       gap-1.5"
                            >
                                @foreach(
                                    $palette
                                    as $colorName => $config
                                )

                                    <button
                                        type="button"
                                        data-column-color
                                        data-color="{{ $colorName }}"
                                        title="{{ ucfirst($colorName) }}"
                                        aria-label="Set column colour to {{ $colorName }}"
                                        class="relative flex
                                               h-7 w-7
                                               items-center
                                               justify-center
                                               rounded-full
                                               ring-offset-2
                                               transition
                                               hover:scale-110
                                               focus:outline-none
                                               focus:ring-2
                                               focus:ring-indigo-500
                                               dark:ring-offset-gray-900
                                               {{ $config['swatch'] }}"
                                    >
                                        @if(
                                            $currentColor
                                            === $colorName
                                        )
                                            <span
                                                class="
                                                    material-symbols-rounded
                                                    text-[16px]
                                                    text-white
                                                "
                                            >
                                                check
                                            </span>
                                        @endif
                                    </button>

                                @endforeach
                            </div>

                        </div>


                        <div
                            class="my-1 border-t
                                   border-gray-100
                                   dark:border-gray-800"
                        ></div>

                        <button
                            type="button"
                            data-copy-column
                            class="flex w-full
                                   items-center gap-3
                                   rounded-xl px-3 py-2.5
                                   text-left text-sm
                                   font-medium text-gray-700
                                   transition
                                   hover:bg-gray-100
                                   dark:text-gray-200
                                   dark:hover:bg-gray-800"
                        >
                            <span
                                class="material-symbols-rounded
                                       text-[19px]
                                       text-gray-400"
                            >
                                content_copy
                            </span>

                            <span data-copy-label>
                                Copy column
                            </span>
                        </button>


                        <div
                            class="my-1 border-t
                                   border-gray-100
                                   dark:border-gray-800"
                        ></div>

                        @if($canDelete)

                            <button
                                type="button"
                                data-delete-column
                                class="flex w-full
                                       items-center gap-3
                                       rounded-xl px-3 py-2.5
                                       text-left text-sm
                                       font-medium
                                       text-red-600
                                       transition
                                       hover:bg-red-50
                                       dark:text-red-400
                                       dark:hover:bg-red-950/40"
                            >
                                <span
                                    class="material-symbols-rounded
                                           text-[19px]"
                                >
                                    delete
                                </span>

                                Delete column
                            </button>

                        @else

                            <button
                                type="button"
                                disabled
                                class="flex w-full
                                       cursor-not-allowed
                                       items-center gap-3
                                       rounded-xl px-3 py-2.5
                                       text-left text-sm
                                       font-medium
                                       text-gray-300
                                       dark:text-gray-600"
                                title="Remove all tasks before deleting this column"
                            >
                                <span
                                    class="material-symbols-rounded
                                           text-[19px]"
                                >
                                    delete
                                </span>

                                Delete column
                            </button>

                            <p
                                class="px-3 pb-1 pt-0.5
                                       text-[11px]
                                       leading-4
                                       text-gray-400
                                       dark:text-gray-500"
                            >
                                Move or remove all tasks first.
                            </p>

                        @endif


                        <p
                            data-column-menu-error
                            class="hidden px-3
                                   pb-1 pt-2
                                   text-xs text-red-600
                                   dark:text-red-400"
                        ></p>

                    </div>
                </div>

            </div>

        @endif

    </div>


    <div class="space-y-3">
        {{ $slot }}
    </div>

</div>