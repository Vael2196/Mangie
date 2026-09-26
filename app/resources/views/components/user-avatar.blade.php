@props([
    'user',
    'size' => 'md',
    'ring' => false,
])

@php
    $sizeClasses = [
        'xs' => 'h-6 w-6 text-[9px]',
        'sm' => 'h-8 w-8 text-[11px]',
        'md' => 'h-9 w-9 text-xs',
        'lg' => 'h-12 w-12 text-sm',
        'xl' => 'h-20 w-20 text-xl',
        '2xl' => 'h-28 w-28 text-3xl',
    ];

    $parts = preg_split('/\s+/', trim($user->name ?? 'User')) ?: [];
    $initials = collect($parts)
        ->filter()
        ->take(2)
        ->map(fn ($part) => mb_strtoupper(mb_substr($part, 0, 1)))
        ->implode('');

    $initials = $initials !== '' ? $initials : 'U';
@endphp

<span
    {{ $attributes->class([
        'relative inline-flex shrink-0 items-center justify-center overflow-hidden rounded-full bg-indigo-100 font-bold text-indigo-700 dark:bg-indigo-950 dark:text-indigo-300',
        $sizeClasses[$size] ?? $sizeClasses['md'],
        'ring-2 ring-white dark:ring-gray-900' => $ring,
    ]) }}
    data-user-avatar-id="{{ $user->id }}"
    data-user-avatar-name="{{ $user->name }}"
>
    <img
        data-user-avatar-image
        src="{{ $user->avatar_url ?? '' }}"
        alt="{{ $user->name }}"
        class="h-full w-full object-cover {{ $user->avatar_url ? '' : 'hidden' }}"
    >

    <span
        data-user-avatar-fallback
        aria-hidden="true"
        class="{{ $user->avatar_url ? 'hidden' : '' }}"
    >
        {{ $initials }}
    </span>
</span>
