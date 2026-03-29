<?php

namespace App\Observers;

use App\Data\DefaultInfluencers;
use App\Models\Influencer;
use App\Models\User;

class UserObserver
{
    private const COLUMNS = [
        'user_id', 'name', 'type', 'channel_url', 'email', 'instagram',
        'twitter', 'discord', 'country', 'language', 'niche', 'subscribers',
        'status', 'notes', 'avg_views', 'engagement_rate', 'last_contact_date',
        'games_covered', 'tags', 'created_at', 'updated_at',
    ];

    public function created(User $user): void
    {
        $now = now();
        $records = [];

        foreach (DefaultInfluencers::all() as $data) {
            $record = ['user_id' => $user->id, 'created_at' => $now, 'updated_at' => $now];

            foreach (self::COLUMNS as $col) {
                if (in_array($col, ['user_id', 'created_at', 'updated_at'])) continue;

                if ($col === 'tags') {
                    $record[$col] = isset($data['tags']) ? json_encode($data['tags']) : null;
                } elseif ($col === 'games_covered') {
                    $record[$col] = isset($data['games_covered']) ? json_encode($data['games_covered']) : null;
                } else {
                    $record[$col] = $data[$col] ?? null;
                }
            }

            $records[] = $record;
        }

        foreach (array_chunk($records, 50) as $chunk) {
            Influencer::insert($chunk);
        }
    }
}
