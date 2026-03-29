<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Column;
use App\Models\TaskCard;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class TaskCardController extends Controller
{
    public function store(Request $request, Column $column): JsonResponse
    {
        abort_if($column->board->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'position' => 'nullable|integer|min:0',
        ]);

        $validated['position'] = $validated['position'] ?? $column->taskCards()->count();
        $validated['user_id'] = $request->user()->id;

        $card = $column->taskCards()->create($validated);

        return response()->json($card, 201);
    }

    public function show(Request $request, TaskCard $taskCard): JsonResponse
    {
        abort_if($taskCard->column->board->user_id !== $request->user()->id, 403);
        return response()->json($taskCard);
    }

    public function update(Request $request, TaskCard $taskCard): JsonResponse
    {
        abort_if($taskCard->column->board->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'title' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'category' => 'nullable|string|max:100',
            'priority' => 'nullable|in:low,medium,high,urgent',
            'due_date' => 'nullable|date',
            'labels' => 'nullable|array',
            'position' => 'nullable|integer|min:0',
            'column_id' => 'nullable|integer|exists:columns,id',
            'is_completed' => 'nullable|boolean',
        ]);

        $taskCard->update($validated);
        return response()->json($taskCard);
    }

    public function destroy(Request $request, TaskCard $taskCard): JsonResponse
    {
        abort_if($taskCard->column->board->user_id !== $request->user()->id, 403);
        $taskCard->delete();
        return response()->json(null, 204);
    }

    public function move(Request $request, TaskCard $taskCard): JsonResponse
    {
        abort_if($taskCard->column->board->user_id !== $request->user()->id, 403);

        $request->validate([
            'column_id' => 'required|integer|exists:columns,id',
            'position' => 'required|integer|min:0',
            'cards' => 'nullable|array',
        ]);

        $taskCard->update([
            'column_id' => $request->column_id,
            'position' => $request->position,
        ]);

        if ($request->cards) {
            foreach ($request->cards as $item) {
                TaskCard::where('id', $item['id'])->update(['position' => $item['position']]);
            }
        }

        return response()->json($taskCard->fresh());
    }

    public function uploadAttachment(Request $request, TaskCard $taskCard): JsonResponse
    {
        abort_if($taskCard->column->board->user_id !== $request->user()->id, 403);

        $request->validate([
            'file' => 'required|file|max:10240',
        ]);

        $path = $request->file('file')->store('task-attachments', 'public');
        $attachments = $taskCard->attachments ?? [];
        $attachments[] = [
            'path' => $path,
            'name' => $request->file('file')->getClientOriginalName(),
            'url' => Storage::url($path),
        ];

        $taskCard->update(['attachments' => $attachments]);

        return response()->json($taskCard->fresh());
    }
}
