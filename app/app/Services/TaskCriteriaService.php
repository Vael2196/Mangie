<?php

namespace App\Services;

class TaskCriteriaService
{
    public function get(string $scope): array
    {
        $readCookie = function (string $name) use ($scope): string {
            $value = $_COOKIE["{$scope}_{$name}"] ?? '';

            return rawurldecode($value);
        };

        $priority = $readCookie('priority');
        $label = $readCookie('label');
        $sortField = $readCookie('sort');
        $sortDirection = $readCookie('direction');

        $validPriorities = [
            'Low',
            'Medium',
            'High',
        ];

        $validLabels = [
            'API',
            'Backend',
            'Frontend',
            'UI/UX',
            'Database',
        ];

        $sortLabels = [
            'title' => 'Title',
            'description' => 'Description',
            'priority' => 'Priority',
            'labels' => 'Labels',
            'story_points' => 'Story Points',
            'time_log' => 'Time Log',
        ];

        if (!in_array($priority, $validPriorities, true)) {
            $priority = '';
        }

        if (!in_array($label, $validLabels, true)) {
            $label = '';
        }

        if (!array_key_exists($sortField, $sortLabels)) {
            $sortField = '';
        }

        if (!in_array($sortDirection, ['asc', 'desc'], true)) {
            $sortDirection = '';
        }

        return [
            'priority' => $priority,
            'label' => $label,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,

            'tags' => [
                'priority' => $priority,
                'label' => $label,
                'sort' => [
                    $sortField !== ''
                        ? $sortLabels[$sortField]
                        : '',
                    $sortDirection,
                ],
            ],
        ];
    }
}
