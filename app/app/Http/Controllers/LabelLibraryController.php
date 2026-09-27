<?php

namespace App\Http\Controllers;

use App\Models\Board;
use App\Models\LabelLibrary;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Validation\Rule;

class LabelLibraryController extends Controller
{
    public function store(
        Request $request,
        Board $board
    ): JsonResponse {
        Gate::authorize('view', $board);

        $request->merge([
            'name' => trim((string) $request->input('name')),
        ]);

        $validated = $request->validate([
            'name' => [
                'required',
                'string',
                'max:80',
                Rule::unique('label_libraries', 'name')
                    ->where('user_id', $request->user()->id),
            ],
        ]);

        abort_if(
            $board->labels()->doesntExist(),
            422,
            'Add at least one label before saving a list.'
        );

        $library = DB::transaction(function () use (
            $request,
            $board,
            $validated
        ) {
            $library = $request->user()->labelLibraries()->create([
                'name' => $validated['name'],
            ]);

            foreach ($board->labels()->get() as $label) {
                $library->items()->create([
                    'name' => $label->name,
                    'color' => $label->color,
                    'position' => $label->position,
                ]);
            }

            return $library->load('items');
        }, 3);

        return response()->json([
            'success' => true,
            'library' => $this->libraryData($library),
        ], 201);
    }

    public function destroy(
        Request $request,
        LabelLibrary $labelLibrary
    ): JsonResponse {
        abort_unless(
            (int) $labelLibrary->user_id === (int) $request->user()->id,
            403
        );

        $id = (int) $labelLibrary->id;
        $labelLibrary->delete();

        return response()->json([
            'success' => true,
            'deleted_library_id' => $id,
        ]);
    }

    private function libraryData(LabelLibrary $library): array
    {
        return [
            'id' => (int) $library->id,
            'name' => $library->name,
            'items' => $library->items->map(fn ($item) => [
                'id' => (int) $item->id,
                'name' => $item->name,
                'color' => $item->color,
                'position' => (int) $item->position,
            ])->values(),
        ];
    }
}
