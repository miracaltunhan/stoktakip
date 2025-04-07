<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ChatMessage extends Model
{
    use HasFactory;

    protected $fillable = [
        'content',
        'is_bot'
    ];

    protected $casts = [
        'is_bot' => 'boolean'
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }
} 