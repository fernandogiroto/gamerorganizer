<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use App\Models\Column;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class ColumnController extends Controller
{
    public function store(Request $request, Board $board): JsonResponse
    {
        abort_if($board->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'color' => 'nullable|string|max:20',
            'position' => 'nullable|integer|min:0',
        ]);

        $validated['position'] = $validated['position'] ?? $board->columns()->count();
        $column = $board->columns()->create($validated);

        return response()->json($column, 201);
    }

    public function update(Request $request, Column $column): JsonResponse
    {
        abort_if($column->board->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'color' => 'nullable|string|max:20',
            'position' => 'nullable|integer|min:0',
        ]);

        $column->update($validated);
        return response()->json($column);
    }

    public function destroy(Request $request, Column $column): JsonResponse
    {
        abort_if($column->board->user_id !== $request->user()->id, 403);
        $column->delete();
        return response()->json(null, 204);
    }

    public function reorder(Request $request, Board $board): JsonResponse
    {
        abort_if($board->user_id !== $request->user()->id, 403);

        $request->validate([
            'columns' => 'required|array',
            'columns.*.id' => 'required|integer',
            'columns.*.position' => 'required|integer|min:0',
        ]);

        foreach ($request->columns as $item) {
            Column::where('id', $item['id'])->where('board_id', $board->id)
                ->update(['position' => $item['position']]);
        }

        return response()->json(['message' => 'Colunas reordenadas']);
    }
}
