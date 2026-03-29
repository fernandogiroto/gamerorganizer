<?php

namespace App\Console\Commands;

use App\Data\DefaultInfluencers;
use App\Models\Influencer;
use App\Models\User;
use Illuminate\Console\Command;

class SeedInfluencersToExistingUsers extends Command
{
    protected $signature   = 'influencers:seed-existing';
    protected $description = 'Seed missing default influencers to all existing users';

    private const COLUMNS = [
        'user_id', 'name', 'type', 'channel_url', 'email', 'instagram',
        'twitter', 'discord', 'country', 'language', 'niche', 'subscribers',
        'status', 'notes', 'avg_views', 'engagement_rate', 'last_contact_date',
        'games_covered', 'tags', 'created_at', 'updated_at',
    ];

    public function handle(): int
    {
        $defaults     = collect(DefaultInfluencers::all());
        $defaultNames = $defaults->pluck('name')->all();
        $users        = User::all();

        $this->info("Seeding {$defaults->count()} influencers to {$users->count()} users...");
        $bar = $this->output->createProgressBar($users->count());
        $bar->start();

        $now   = now();
        $total = 0;

        foreach ($users as $user) {
            $existing = Influencer::where('user_id', $user->id)
                ->whereIn('name', $defaultNames)
                ->pluck('name')
                ->all();

            $missing = $defaults->reject(fn($d) => in_array($d['name'], $existing));

            if ($missing->isEmpty()) {
                $bar->advance();
                continue;
            }

            $records = [];
            foreach ($missing as $data) {
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

            $total += count($records);
            $bar->advance();
        }

        $bar->finish();
        $this->newLine();
        $this->info("Done! Inserted {$total} new influencer records.");

        return self::SUCCESS;
    }
}
