<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Message Model - CONVERSATION ARTIFACT
 * 
 * SILENCE-FIRST ARCHITECTURE
 * --------------------------
 * Messages belong to sessions and are deleted when session ends.
 * No browsing, no sentiment analysis, no engagement tracking.
 * 
 * Role: user or assistant
 * Content: plain text only
 */
class Message extends Model
{
    protected $fillable = [
        'session_id',
        'role',
        'content',
        'created_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
    ];

    public $timestamps = false;

    /**
     * Get the session this message belongs to
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }

    /**
     * Check if this is a user message
     */
    public function isUser(): bool
    {
        return $this->role === 'user';
    }

    /**
     * Check if this is an assistant message
     */
    public function isAssistant(): bool
    {
        return $this->role === 'assistant';
    }
}
