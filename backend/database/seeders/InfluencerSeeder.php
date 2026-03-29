<?php

namespace Database\Seeders;

use App\Data\DefaultInfluencers;
use App\Models\Influencer;
use Illuminate\Database\Seeder;

class InfluencerSeeder extends Seeder
{
    public function run(): void
    {
        $userId = 1;
        $now = now();

        $records = array_map(function (array $data) use ($userId, $now) {
            return array_merge($data, [
                'user_id'       => $userId,
                'tags'          => isset($data['tags']) ? json_encode($data['tags']) : null,
                'games_covered' => isset($data['games_covered']) ? json_encode($data['games_covered']) : null,
                'created_at'    => $now,
                'updated_at'    => $now,
            ]);
        }, DefaultInfluencers::all());

        foreach (array_chunk($records, 50) as $chunk) {
            Influencer::insert($chunk);
        }
    }
}
