<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Notification extends Model
{
    protected $fillable = [
        'item_id',
        'title',
        'message',
        'is_read',
        'notification_date'
    ];

    protected $casts = [
        'is_read' => 'boolean',
        'notification_date' => 'datetime'
    ];

    public function item(): BelongsTo
    {
        return $this->belongsTo(Item::class);
    }
} 