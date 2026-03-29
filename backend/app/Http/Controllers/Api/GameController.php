<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Game;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;

class GameController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $games = $request->user()->games()->orderBy('name')->get();
        return response()->json($games);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'engine' => 'nullable|string|max:100',
            'status' => 'nullable|in:development,beta,released,abandoned',
            'release_date' => 'nullable|date',
            'steam_url' => 'nullable|url',
            'steam_app_id' => 'nullable|string',
            'itch_url' => 'nullable|url',
            'epic_url' => 'nullable|url',
            'gog_url' => 'nullable|url',
            'android_url' => 'nullable|url',
            'ios_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'custom_links' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        // Auto-extract Steam App ID from URL if not provided
        if (!empty($validated['steam_url']) && empty($validated['steam_app_id'])) {
            preg_match('/\/app\/(\d+)/', $validated['steam_url'], $matches);
            if (!empty($matches[1])) {
                $validated['steam_app_id'] = $matches[1];
            }
        }

        $game = $request->user()->games()->create($validated);

        // Fetch Steam data if app ID is available
        if ($game->steam_app_id) {
            $game = $this->fetchSteamData($game);
        }

        return response()->json($game, 201);
    }

    public function show(Request $request, Game $game): JsonResponse
    {
        abort_if($game->user_id !== $request->user()->id, 403);
        return response()->json($game);
    }

    public function update(Request $request, Game $game): JsonResponse
    {
        abort_if($game->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'description' => 'nullable|string',
            'genre' => 'nullable|string|max:100',
            'engine' => 'nullable|string|max:100',
            'status' => 'nullable|in:development,beta,released,abandoned',
            'release_date' => 'nullable|date',
            'steam_url' => 'nullable|url',
            'steam_app_id' => 'nullable|string',
            'itch_url' => 'nullable|url',
            'epic_url' => 'nullable|url',
            'gog_url' => 'nullable|url',
            'android_url' => 'nullable|url',
            'ios_url' => 'nullable|url',
            'website_url' => 'nullable|url',
            'custom_links' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $game->update($validated);
        return response()->json($game->fresh());
    }

    public function destroy(Request $request, Game $game): JsonResponse
    {
        abort_if($game->user_id !== $request->user()->id, 403);
        $game->delete();
        return response()->json(null, 204);
    }

    public function refreshSteamData(Request $request, Game $game): JsonResponse
    {
        abort_if($game->user_id !== $request->user()->id, 403);

        if (!$game->steam_app_id) {
            return response()->json(['message' => 'Jogo não possui Steam App ID'], 422);
        }

        $game = $this->fetchSteamData($game);
        return response()->json($game);
    }

    private function fetchSteamData(Game $game): Game
    {
        try {
            $response = Http::timeout(10)->get("https://store.steampowered.com/api/appdetails", [
                'appids' => $game->steam_app_id,
                'cc' => 'br',
                'l' => 'portuguese',
            ]);

            if ($response->successful()) {
                $data = $response->json();
                $appData = $data[$game->steam_app_id] ?? null;

                if ($appData && $appData['success']) {
                    $game->update([
                        'steam_data' => $appData['data'],
                        'steam_data_updated_at' => now(),
                    ]);
                }
            }
        } catch (\Exception $e) {
            // Silently fail — Steam API is best-effort
        }

        return $game->fresh();
    }

    public function steamSearch(Request $request): JsonResponse
    {
        $request->validate(['term' => 'required|string|min:2']);

        try {
            $response = Http::timeout(10)->get("https://store.steampowered.com/api/storesearch/", [
                'term' => $request->term,
                'cc' => 'br',
                'l' => 'portuguese',
            ]);

            if ($response->successful()) {
                return response()->json($response->json());
            }
        } catch (\Exception $e) {
            // fall through
        }

        return response()->json(['items' => []]);
    }
}
