<?php

namespace App\Http\Controllers;

use App\Services\TaskCriteriaService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class TaskCriteriaController extends Controller
{
    public function __construct(
        private readonly TaskCriteriaService $criteria
    ) {
    }

    public function update(
        Request $request,
        string $scope
    ): JsonResponse {
        $this->validateScope($scope);

        $validated = $request->validate([
            'priority' => [
                'nullable',
                Rule::in(['Low', 'Medium', 'High']),
            ],
            'label' => [
                'nullable',
                Rule::in([
                    'API', 'Backend', 'Frontend', 'UI/UX', 'Database',
                ]),
            ],
            'sortField' => [
                'nullable',
                Rule::in([
                    'title', 'description', 'priority',
                    'labels', 'story_points', 'time_log',
                ]),
            ],
            'sortDirection' => [
                'nullable',
                Rule::in(['asc', 'desc']),
            ],
        ]);

        return response()->json([
            'success' => true,
            'criteria' => $this->criteria->put(
                $request,
                $scope,
                $validated
            ),
        ]);
    }

    public function destroy(
        Request $request,
        string $scope
    ): JsonResponse {
        $this->validateScope($scope);

        return response()->json([
            'success' => true,
            'criteria' => $this->criteria->clear(
                $request,
                $scope
            ),
        ]);
    }

    private function validateScope(string $scope): void
    {
        abort_unless(
            $scope === 'backlog'
                || preg_match('/^board_[1-9][0-9]*$/', $scope),
            404
        );
    }
}
