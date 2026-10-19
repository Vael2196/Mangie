<?php

return [
    'frequencies' => [
        'instantly' => ['label' => 'Instantly', 'minutes' => 0],
        '30_minutes' => ['label' => 'Every 30 minutes', 'minutes' => 30],
        '1_hour' => ['label' => 'Every hour', 'minutes' => 60],
        '3_hours' => ['label' => 'Every 3 hours', 'minutes' => 180],
        '5_hours' => ['label' => 'Every 5 hours', 'minutes' => 300],
        '12_hours' => ['label' => 'Every 12 hours', 'minutes' => 720],
        '1_day' => ['label' => 'Once a day', 'minutes' => 1440],
        '1_week' => ['label' => 'Once a week', 'minutes' => 10080],
        'never' => ['label' => 'Never', 'minutes' => null],
    ],
];
