<?php

namespace App\Http\Controllers;

use App\Events\BoardUpdated;
use App\Models\Board;
use App\Support\Realtime\RealtimePayload;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;

class BoardAppearanceController extends Controller
{
    public function update(
        Request $request,
        Board $board
    ): JsonResponse {
        Gate::authorize('update', $board);

        $validated = $request->validate([
            'mode' => [
                'required',
                Rule::in(['color', 'image', 'default']),
            ],
            'background_color' => [
                'nullable',
                'required_if:mode,color',
                'regex:/^#[0-9a-fA-F]{6}$/',
            ],
            'background_image' => [
                'nullable',
                'required_if:mode,image',
                'image',
                'mimes:jpg,jpeg,png,webp',
                'max:8192',
            ],
        ]);

        $newImagePath = null;

        if ($validated['mode'] === 'image') {
            $newImagePath = $request
                ->file('background_image')
                ->store('board-backgrounds', 'public');
        }

        try {
            [$board, $oldImagePath] = DB::transaction(
                function () use (
                    $board,
                    $validated,
                    $newImagePath
                ) {
                    $board = Board::query()
                        ->whereKey($board->id)
                        ->lockForUpdate()
                        ->firstOrFail();

                    $oldImagePath = $board->background_image_path;

                    if ($validated['mode'] === 'color') {
                        $board->background_color = strtolower(
                            $validated['background_color']
                        );
                        $board->background_image_path = null;
                    } elseif ($validated['mode'] === 'image') {
                        $board->background_image_path = $newImagePath;
                    } else {
                        $board->background_color = config(
                            'mangie.default_board_background',
                            '#eef2ff'
                        );
                        $board->background_image_path = null;
                    }

                    $board->version = (int) $board->version + 1;
                    $board->save();

                    return [$board->refresh(), $oldImagePath];
                },
                3
            );
        } catch (\Throwable $exception) {
            if ($newImagePath) {
                Storage::disk('public')->delete($newImagePath);
            }

            throw $exception;
        }

        if (
            $oldImagePath
            && $oldImagePath !== $board->background_image_path
        ) {
            Storage::disk('public')->delete($oldImagePath);
        }

        $event = new BoardUpdated(
            RealtimePayload::board(
                $board,
                $request->user(),
                ['appearance_changed' => true]
            ),
            (int) $board->project_id,
            (int) $board->id
        );

        broadcast($event)->toOthers();

        return response()->json($event->response());
    }
}
