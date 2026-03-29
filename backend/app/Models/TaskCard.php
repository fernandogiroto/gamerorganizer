<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TaskCard extends Model
{
    protected $fillable = [
        'column_id', 'user_id', 'title', 'description', 'category',
        'priority', 'due_date', 'attachments', 'labels', 'position', 'is_completed',
    ];

    protected $casts = [
        'attachments' => 'array',
        'labels' => 'array',
        'due_date' => 'date',
        'is_completed' => 'boolean',
    ];

    public function column(): BelongsTo
    {
        return $this->belongsTo(Column::class);
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }
}
