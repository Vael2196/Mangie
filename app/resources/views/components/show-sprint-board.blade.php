@php
    $defaultBackground = strtolower(
        config('mangie.default_board_background', '#eef2ff')
    );
    $backgroundColor = strtolower(
        $board->background_color ?: $defaultBackground
    );
    $backgroundImage = $board->background_image_url;
    $hasCustomColour = !$backgroundImage
        && $backgroundColor !== $defaultBackground;
    $backgroundMode = $backgroundImage
        ? 'image'
        : ($hasCustomColour ? 'colour' : 'default');

    $backgroundStyle = match ($backgroundMode) {
        'image' => sprintf(
            'background-color: %s; background-image: linear-gradient(rgba(15, 23, 42, 0.18), rgba(15, 23, 42, 0.30)), url(%s);',
            $backgroundColor,
            json_encode($backgroundImage)
        ),
        'colour' => "background-color: {$backgroundColor};",
        default => '',
    };
@endphp

<div
    id="sprint-card-{{ $board->id }}"
    data-board-id="{{ $board->id }}"
    data-board-card-background="{{ $backgroundMode }}"
    class="group relative flex min-h-44 flex-col overflow-hidden
           rounded-2xl border border-gray-200 bg-white shadow-sm
           transition duration-200 hover:-translate-y-1
           hover:border-indigo-200 hover:shadow-lg
           dark:border-gray-800 dark:bg-gray-900
           dark:hover:border-indigo-800"
>
    <div
        @class([
            'relative flex flex-1 flex-col bg-center bg-cover p-5',
            'bg-white dark:bg-gray-900' => $backgroundMode === 'default',
        ])
        @if ($backgroundStyle !== '')
            style="{{ $backgroundStyle }}"
        @endif
    >
        <div
            class="absolute inset-x-0 top-0 h-1
                   bg-gradient-to-r from-indigo-500 to-blue-500"
        ></div>

        <div
            @class([
                'flex items-start justify-between gap-4',
                'rounded-xl border border-white/60 bg-white/90 p-3 shadow-sm backdrop-blur-sm dark:border-gray-700/70 dark:bg-gray-900/90' => $backgroundMode !== 'default',
            ])
        >
            <div class="min-w-0">
                <p
                    class="text-xs font-semibold uppercase tracking-wider
                           text-indigo-500"
                >
                    Sprint
                </p>

                <h2
                    class="mt-1 truncate text-lg font-bold
                           text-gray-900 dark:text-white"
                >
                    {{ $board->name }}
                </h2>
            </div>

            @if ($board->completed == 1)
                <span
                    class="inline-flex shrink-0 items-center gap-1 rounded-full
                           bg-green-50 px-2.5 py-1 text-xs font-semibold
                           text-green-700 dark:bg-green-950/40
                           dark:text-green-400"
                >
                    <span class="material-symbols-rounded text-[15px]">
                        check_circle
                    </span>
                    Completed
                </span>
            @elseif ($board->status == 1)
                <span
                    class="inline-flex shrink-0 items-center gap-1 rounded-full
                           bg-indigo-50 px-2.5 py-1 text-xs font-semibold
                           text-indigo-700 dark:bg-indigo-950/50
                           dark:text-indigo-300"
                >
                    <span class="h-1.5 w-1.5 rounded-full bg-indigo-500"></span>
                    Active
                </span>
            @else
                <span
                    class="shrink-0 rounded-full bg-gray-100 px-2.5 py-1
                           text-xs font-semibold text-gray-500
                           dark:bg-gray-800 dark:text-gray-400"
                >
                    Not started
                </span>
            @endif
        </div>
    </div>

    <div
        class="flex items-center justify-between border-t border-gray-100
               bg-white px-5 py-4 dark:border-gray-800 dark:bg-gray-900"
    >
        <a
            href="{{ route('boards.show', $board->id) }}"
            class="inline-flex items-center gap-1 text-sm font-semibold
                   text-indigo-600 transition hover:text-indigo-500
                   dark:text-indigo-400"
        >
            Open sprint

            <span
                class="material-symbols-rounded text-[18px]
                       transition group-hover:translate-x-0.5"
            >
                arrow_forward
            </span>
        </a>

        @can('delete', $board)
            <form
                method="POST"
                action="{{ route('boards.destroy', $board->id) }}"
                onsubmit="return confirm('Are you sure you want to delete this board?')"
            >
                @csrf
                @method('delete')

                <button
                    type="submit"
                    title="Delete sprint"
                    class="flex h-8 w-8 items-center justify-center rounded-lg
                           text-gray-400 transition hover:bg-red-50
                           hover:text-red-600 dark:hover:bg-red-950/30
                           dark:hover:text-red-400"
                >
                    <span class="material-symbols-rounded text-[19px]">
                        delete
                    </span>
                </button>
            </form>
        @endcan
    </div>
</div>
