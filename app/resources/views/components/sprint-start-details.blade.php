<div x-data="{ open: false }" class="shrink-0">
    @if($board->status == 0)
        <button
            type="button"
            @click="open = true"
            class="inline-flex h-10 items-center gap-2 rounded-xl
                   bg-indigo-600 px-4 text-sm font-semibold text-white
                   shadow-sm transition hover:bg-indigo-500
                   focus:outline-none focus:ring-4 focus:ring-indigo-200
                   dark:focus:ring-indigo-950"
        >
            <span class="material-symbols-rounded text-[19px]">play_arrow</span>
            Start sprint
        </button>
    @else
        <div
            class="inline-flex h-10 items-center gap-2 rounded-xl
                   border border-emerald-200 bg-emerald-50 px-3.5
                   text-sm font-semibold text-emerald-700
                   dark:border-emerald-900 dark:bg-emerald-950/50
                   dark:text-emerald-300"
            title="This sprint is currently active"
        >
            <span class="relative flex h-2.5 w-2.5">
                <span class="absolute inline-flex h-full w-full animate-ping rounded-full bg-emerald-400 opacity-60"></span>
                <span class="relative inline-flex h-2.5 w-2.5 rounded-full bg-emerald-500"></span>
            </span>
            Sprint active
            <span class="font-normal text-emerald-600/80 dark:text-emerald-400/80">
                ·
                @if($daysLeft > 1)
                    {{ $daysLeft }} days left
                @elseif($daysLeft === 1)
                    1 day left
                @elseif($daysLeft === 0)
                    ends today
                @else
                    overdue
                @endif
            </span>
        </div>
    @endif

    @if($board->status == 0)
        <div
            x-cloak
            x-show="open"
            @keydown.escape.window="open = false"
            class="fixed inset-0 z-[160] overflow-y-auto"
            role="dialog"
            aria-modal="true"
            aria-labelledby="start-sprint-title"
        >
            <button
                type="button"
                @click="open = false"
                class="fixed inset-0 bg-gray-950/60 backdrop-blur-[3px]"
                aria-label="Close start sprint dialog"
            ></button>

            <div class="relative flex min-h-full items-center justify-center p-4 sm:p-6">
                <div
                    x-show="open"
                    x-transition:enter="transition ease-out duration-200"
                    x-transition:enter-start="opacity-0 translate-y-3 scale-95"
                    x-transition:enter-end="opacity-100 translate-y-0 scale-100"
                    x-transition:leave="transition ease-in duration-150"
                    x-transition:leave-start="opacity-100 translate-y-0 scale-100"
                    x-transition:leave-end="opacity-0 translate-y-2 scale-95"
                    @click.stop
                    class="w-full max-w-lg overflow-hidden rounded-3xl
                           border border-white/70 bg-white shadow-2xl
                           dark:border-gray-700 dark:bg-gray-900"
                >
                    <div
                        class="relative overflow-hidden border-b border-gray-100
                               bg-gradient-to-br from-indigo-600 to-violet-600
                               px-6 py-6 text-white dark:border-gray-800"
                    >
                        <div class="absolute -right-8 -top-10 h-32 w-32 rounded-full bg-white/10"></div>
                        <div class="relative flex items-start justify-between gap-4">
                            <div class="flex items-center gap-3">
                                <div class="flex h-11 w-11 items-center justify-center rounded-2xl bg-white/15 ring-1 ring-white/20">
                                    <span class="material-symbols-rounded text-[24px]">sprint</span>
                                </div>
                                <div>
                                    <p class="text-xs font-semibold uppercase tracking-widest text-indigo-100">Ready to focus?</p>
                                    <h2 id="start-sprint-title" class="mt-1 text-xl font-bold">Start {{ $board->name }}</h2>
                                </div>
                            </div>
                            <button
                                type="button"
                                @click="open = false"
                                class="flex h-9 w-9 items-center justify-center rounded-xl text-indigo-100 transition hover:bg-white/15 hover:text-white"
                                aria-label="Close"
                            >
                                <span class="material-symbols-rounded text-[21px]">close</span>
                            </button>
                        </div>
                    </div>

                    <form method="POST" action="{{ route('boards.startSprint') }}" class="space-y-5 p-6">
                        @csrf
                        <input type="hidden" name="board_id" value="{{ $board->id }}">

                        <div>
                            <label for="sprint-goal-{{ $board->id }}" class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                Sprint goal
                            </label>
                            <p class="mt-1 text-xs leading-5 text-gray-500 dark:text-gray-400">
                                Give the team one clear outcome to work toward.
                            </p>
                            <textarea
                                id="sprint-goal-{{ $board->id }}"
                                name="sprint_goal"
                                rows="3"
                                maxlength="255"
                                required
                                placeholder="What should this sprint accomplish?"
                                class="mt-2 w-full resize-none rounded-2xl border border-gray-200
                                       bg-gray-50 px-4 py-3 text-sm text-gray-900 shadow-inner
                                       placeholder:text-gray-400 focus:border-indigo-400
                                       focus:bg-white focus:ring-4 focus:ring-indigo-100
                                       dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                       dark:focus:border-indigo-600 dark:focus:bg-gray-900
                                       dark:focus:ring-indigo-950"
                            ></textarea>
                        </div>

                        <div>
                            <label for="sprint-end-{{ $board->id }}" class="text-sm font-semibold text-gray-800 dark:text-gray-200">
                                End date
                            </label>
                            <div class="relative mt-2">
                                <span class="material-symbols-rounded pointer-events-none absolute left-3.5 top-1/2 -translate-y-1/2 text-[20px] text-gray-400">event</span>
                                <input
                                    id="sprint-end-{{ $board->id }}"
                                    type="date"
                                    name="end_date"
                                    min="{{ now()->toDateString() }}"
                                    required
                                    class="w-full rounded-2xl border border-gray-200 bg-white
                                           py-3 pl-11 pr-4 text-sm text-gray-900 shadow-sm
                                           focus:border-indigo-400 focus:ring-4 focus:ring-indigo-100
                                           dark:border-gray-700 dark:bg-gray-800 dark:text-white
                                           dark:focus:border-indigo-600 dark:focus:ring-indigo-950"
                                >
                            </div>
                        </div>

                        <div class="flex items-center justify-end gap-2 border-t border-gray-100 pt-5 dark:border-gray-800">
                            <button
                                type="button"
                                @click="open = false"
                                class="rounded-xl px-4 py-2.5 text-sm font-semibold text-gray-500 transition hover:bg-gray-100 hover:text-gray-700 dark:text-gray-400 dark:hover:bg-gray-800 dark:hover:text-gray-200"
                            >
                                Cancel
                            </button>
                            <button
                                type="submit"
                                class="inline-flex items-center gap-2 rounded-xl bg-indigo-600 px-4 py-2.5 text-sm font-semibold text-white shadow-sm transition hover:bg-indigo-500 focus:outline-none focus:ring-4 focus:ring-indigo-200 dark:focus:ring-indigo-950"
                            >
                                <span class="material-symbols-rounded text-[19px]">play_arrow</span>
                                Start sprint
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    @endif
</div>
