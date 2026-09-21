<?php

namespace App\Services;

use Illuminate\Http\Request;

class TaskCriteriaService
{
    private const PRIORITIES = ['Low', 'Medium', 'High'];

    private const LABELS = [
        'API', 'Backend', 'Frontend', 'UI/UX', 'Database',
    ];

    private const SORT_LABELS = [
        'title' => 'Title',
        'description' => 'Description',
        'priority' => 'Priority',
        'labels' => 'Labels',
        'story_points' => 'Story Points',
        'time_log' => 'Time Log',
    ];

    public function get(Request $request, string $scope): array
    {
        return $this->normalise(
            (array) $request->session()->get(
                $this->sessionKey($request, $scope),
                []
            )
        );
    }

    public function put(
        Request $request,
        string $scope,
        array $criteria
    ): array {
        $criteria = $this->normalise($criteria);

        $request->session()->put(
            $this->sessionKey($request, $scope),
            $this->storedValues($criteria)
        );

        return $criteria;
    }

    public function clear(Request $request, string $scope): array
    {
        $request->session()->forget(
            $this->sessionKey($request, $scope)
        );

        return $this->normalise([]);
    }

    private function normalise(array $values): array
    {
        $priority = in_array(
            $values['priority'] ?? '',
            self::PRIORITIES,
            true
        ) ? $values['priority'] : '';

        $label = in_array(
            $values['label'] ?? '',
            self::LABELS,
            true
        ) ? $values['label'] : '';

        $sortField = array_key_exists(
            $values['sortField'] ?? '',
            self::SORT_LABELS
        ) ? $values['sortField'] : '';

        $sortDirection = in_array(
            $values['sortDirection'] ?? '',
            ['asc', 'desc'],
            true
        ) ? $values['sortDirection'] : '';

        if ($sortField === '' || $sortDirection === '') {
            $sortField = '';
            $sortDirection = '';
        }

        return [
            'priority' => $priority,
            'label' => $label,
            'sortField' => $sortField,
            'sortDirection' => $sortDirection,
            'active' => $priority !== ''
                || $label !== ''
                || $sortField !== '',
            'tags' => [
                'priority' => $priority,
                'label' => $label,
                'sort' => [
                    $sortField !== ''
                        ? self::SORT_LABELS[$sortField]
                        : '',
                    $sortDirection,
                ],
            ],
        ];
    }

    private function storedValues(array $criteria): array
    {
        return [
            'priority' => $criteria['priority'],
            'label' => $criteria['label'],
            'sortField' => $criteria['sortField'],
            'sortDirection' => $criteria['sortDirection'],
        ];
    }

    private function sessionKey(
        Request $request,
        string $scope
    ): string {
        return sprintf(
            'task_criteria.%d.%s',
            $request->user()->id,
            $scope
        );
    }
}
