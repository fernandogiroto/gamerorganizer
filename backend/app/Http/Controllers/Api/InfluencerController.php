<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Influencer;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;

class InfluencerController extends Controller
{
    public function index(Request $request): JsonResponse
    {
        $query = $request->user()->influencers();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('niche', 'like', "%{$search}%")
                  ->orWhere('channel_url', 'like', "%{$search}%");
            });
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('country')) {
            $query->where('country', $request->country);
        }

        if ($request->filled('language')) {
            $query->where('language', $request->language);
        }

        if ($request->filled('niche')) {
            $query->where('niche', 'like', "%{$request->niche}%");
        }

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('min_subscribers')) {
            $query->where('subscribers', '>=', $request->min_subscribers);
        }

        $influencers = $query->orderBy('name')->paginate(20);

        return response()->json($influencers);
    }

    public function store(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|string|max:50',
            'channel_url' => 'nullable|url',
            'email' => 'nullable|email',
            'instagram' => 'nullable|string',
            'twitter' => 'nullable|string',
            'discord' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'niche' => 'nullable|string|max:255',
            'subscribers' => 'nullable|integer|min:0',
            'status' => 'nullable|in:prospect,contacted,negotiating,active,inactive',
            'notes' => 'nullable|string',
            'avg_views' => 'nullable|numeric|min:0',
            'engagement_rate' => 'nullable|numeric|min:0|max:100',
            'last_contact_date' => 'nullable|date',
            'games_covered' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $influencer = $request->user()->influencers()->create($validated);

        return response()->json($influencer, 201);
    }

    public function show(Request $request, Influencer $influencer): JsonResponse
    {
        abort_if($influencer->user_id !== $request->user()->id, 403);
        return response()->json($influencer);
    }

    public function update(Request $request, Influencer $influencer): JsonResponse
    {
        abort_if($influencer->user_id !== $request->user()->id, 403);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|string|max:50',
            'channel_url' => 'nullable|url',
            'email' => 'nullable|email',
            'instagram' => 'nullable|string',
            'twitter' => 'nullable|string',
            'discord' => 'nullable|string',
            'country' => 'nullable|string|max:100',
            'language' => 'nullable|string|max:50',
            'niche' => 'nullable|string|max:255',
            'subscribers' => 'nullable|integer|min:0',
            'status' => 'nullable|in:prospect,contacted,negotiating,active,inactive',
            'notes' => 'nullable|string',
            'avg_views' => 'nullable|numeric|min:0',
            'engagement_rate' => 'nullable|numeric|min:0|max:100',
            'last_contact_date' => 'nullable|date',
            'games_covered' => 'nullable|array',
            'tags' => 'nullable|array',
        ]);

        $influencer->update($validated);
        return response()->json($influencer);
    }

    public function destroy(Request $request, Influencer $influencer): JsonResponse
    {
        abort_if($influencer->user_id !== $request->user()->id, 403);
        $influencer->delete();
        return response()->json(null, 204);
    }

    public function stats(Request $request): JsonResponse
    {
        $user = $request->user();
        return response()->json([
            'total' => $user->influencers()->count(),
            'by_type' => $user->influencers()->selectRaw('type, count(*) as count')->groupBy('type')->pluck('count', 'type'),
            'by_status' => $user->influencers()->selectRaw('status, count(*) as count')->groupBy('status')->pluck('count', 'status'),
            'by_country' => $user->influencers()->selectRaw('country, count(*) as count')->groupBy('country')->orderByDesc('count')->limit(10)->pluck('count', 'country'),
            'total_reach' => $user->influencers()->sum('subscribers'),
        ]);
    }
}
