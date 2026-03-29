<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Game extends Model
{
    protected $fillable = [
        'user_id', 'name', 'description', 'cover_image', 'genre', 'engine',
        'status', 'release_date', 'steam_url', 'steam_app_id', 'itch_url',
        'epic_url', 'gog_url', 'android_url', 'ios_url', 'website_url',
        'steam_data', 'steam_data_updated_at', 'custom_links', 'tags',
    ];

    protected $casts = [
        'steam_data' => 'array',
        'custom_links' => 'array',
        'tags' => 'array',
        'release_date' => 'date',
        'steam_data_updated_at' => 'datetime',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
