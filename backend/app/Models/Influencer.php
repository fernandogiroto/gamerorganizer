<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Influencer extends Model
{
    protected $fillable = [
        'user_id', 'name', 'type', 'channel_url', 'email',
        'instagram', 'twitter', 'discord', 'country', 'language',
        'niche', 'subscribers', 'status', 'notes', 'avg_views',
        'engagement_rate', 'last_contact_date', 'games_covered', 'tags',
    ];

    protected $casts = [
        'games_covered' => 'array',
        'tags' => 'array',
        'last_contact_date' => 'date',
        'subscribers' => 'integer',
        'avg_views' => 'float',
        'engagement_rate' => 'float',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
