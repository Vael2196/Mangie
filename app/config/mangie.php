<?php

return [
    'product_backlog_board_id' =>
        env(
            'PRODUCT_BACKLOG_BOARD_ID',
            1
        ),

    'default_board_background' => '#eef2ff',

    'standard_labels' => [
        ['name' => 'API', 'color' => '#3b82f6'],
        ['name' => 'Backend', 'color' => '#8b5cf6'],
        ['name' => 'Frontend', 'color' => '#14b8a6'],
        ['name' => 'UI/UX', 'color' => '#ec4899'],
        ['name' => 'Database', 'color' => '#f97316'],
    ],

    'task_colors' => [
        'gray' => '#f1f5f9', 'blue' => '#dbeafe', 'green' => '#dcfce7',
        'yellow' => '#fef9c3', 'orange' => '#ffedd5', 'red' => '#fee2e2',
        'purple' => '#f3e8ff', 'pink' => '#fce7f3',
    ],

    'label_colors' => [
        '#ef4444',
        '#f97316',
        '#eab308',
        '#22c55e',
        '#14b8a6',
        '#3b82f6',
        '#6366f1',
        '#8b5cf6',
        '#ec4899',
        '#64748b',
    ],
];
