<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Board;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class BoardController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $boards = $request->user()->boards()->with(['columns.taskCards'])->get();
        return response()->json($boards);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        $board = $request->user()->boards()->create($validated);
        $board->load('columns.taskCards');

        return response()->json($board, 201);
    }

    public function show(Request $request, Board $board): JsonResponse
    {
        abort_if($board->user_id !== $request->user()->id, 403);
        $board->load('columns.taskCards');
        return response()->json($board);
    }

    public function update(Request $request, Board $board): JsonResponse
    {
        abort_if($board->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'color' => 'nullable|string|max:20',
        ]);

        $board->update($validated);
        return response()->json($board);
    }

    public function destroy(Request $request, Board $board): JsonResponse
    {
        abort_if($board->user_id !== $request->user()->id, 403);
        $board->delete();
        return response()->json(null, 204);
    }
}
