<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

/**
 * Session Model - CONVERSATION IDENTITY
 * 
 * SILENCE-FIRST ARCHITECTURE
 * --------------------------
 * Sessions are anonymous, ephemeral, and local-first.
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * - Link sessions to IP addresses
 * - Track engagement metrics
 * - Browse chat history across sessions
 * - Gamify streaks or sentiment
 * - Store analytics
 * 
 * Mode: quiet (minimal) or company (gentle)
 * Status: active (resumable) or closed (finished)
 */
class Session extends Model
{
    protected $table = 'conversation_sessions';
    
    protected $fillable = [
        'uuid',
        'mode',
        'status',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get all messages in this session
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Check if session is active
     */
    public function isActive(): bool
    {
        return $this->status === 'active';
    }

    /**
     * Close this session
     */
    public function close(): void
    {
        $this->update(['status' => 'closed']);
    }

    /**
     * Get recent messages (for context)
     */
    public function getRecentMessages(int $limit = 10): \Illuminate\Support\Collection
    {
        return $this->messages()
            ->orderBy('created_at', 'desc')
            ->limit($limit)
            ->get()
            ->reverse()
            ->values();
    }

    /**
     * Hard delete session and all messages
     * Used when starting fresh
     */
    public function obliterate(): void
    {
        $this->messages()->delete();
        $this->delete();
    }
}
