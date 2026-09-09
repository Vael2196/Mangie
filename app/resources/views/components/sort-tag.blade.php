@props([
    'scope',
    'key',
    'direction'
])

<div
    class="inline-flex items-center gap-1.5
           rounded-lg border border-gray-200
           bg-gray-100 px-2 py-1
           text-sm text-gray-700
           dark:border-gray-700 dark:bg-gray-800
           dark:text-gray-200"
>
    <button
        type="button"
        id="sort-tag-{{ $scope }}"
        class="flex h-5 w-5 items-center justify-center
               rounded-md text-gray-400 transition
               hover:bg-gray-200 hover:text-red-500
               dark:hover:bg-gray-700"
        title="Remove sorting"
    >
        <span class="material-symbols-rounded text-[15px]">
            close
        </span>
    </button>

    <span>{{ $key }}</span>

    <span class="material-symbols-rounded text-[16px]">
        {{ $direction === 'asc'
            ? 'arrow_upward'
            : 'arrow_downward' }}
    </span>
</div>


<script>
document.addEventListener('DOMContentLoaded', () => {
    const scope = @js($scope);

    const button = document.getElementById(
        `sort-tag-${scope}`
    );

    if (!button) {
        return;
    }

    button.addEventListener('click', () => {
        document.cookie =
            `${scope}_sort=; Max-Age=0; path=/; SameSite=Lax`;

        document.cookie =
            `${scope}_direction=; Max-Age=0; path=/; SameSite=Lax`;

        window.location.reload();
    });
});
</script>