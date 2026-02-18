<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * Session Model - CONVERSATION IDENTITY
 * 
 * ADAPTIVE CONVERSATIONAL SYSTEM
 * ------------------------------
 * Sessions track emotional patterns and adapt conversation style.
 * 
 * Privacy-conscious architecture:
 * - Uses soft fingerprinting (hashed IP + user agent)
 * - Stores metrics, not full conversations
 * - Can be deleted on user request
 * 
 * Mode: quiet (minimal) or company (gentle)
 * Status: active (resumable) or closed (finished)
 */
class Session extends Model
{
    protected $table = 'conversation_sessions';
    
    protected $fillable = [
        'uuid',
        'user_profile_id',
        'fingerprint',
        'mode',
        'status',
        'session_intensity',
        'absolutist_count',
        'self_criticism_count',
        'detected_topics',
        'emotional_tone',
        'message_count',
        'last_message_at',
        'vagueness_count',
        'abstraction_count',
        'avoidance_detected_count',
        'topics_avoided',
        'grandiosity_detected',
        'self_mythologizing_detected',
        'escalation_tone',
    ];

        // Encrypt session_intensity, emotional_tone, detected_topics
        public function setSessionIntensityAttribute($value)
        {
            $this->attributes['session_intensity'] = \App\Support\Encryption::encrypt($value);
        }
        public function getSessionIntensityAttribute($value)
        {
            return $value ? (float) \App\Support\Encryption::decrypt($value) : null;
        }
        public function setEmotionalToneAttribute($value)
        {
            $this->attributes['emotional_tone'] = \App\Support\Encryption::encrypt($value);
        }
        public function getEmotionalToneAttribute($value)
        {
            return $value ? (float) \App\Support\Encryption::decrypt($value) : null;
        }
        public function setDetectedTopicsAttribute($value)
        {
            $this->attributes['detected_topics'] = \App\Support\Encryption::encrypt(json_encode($value));
        }
        public function getDetectedTopicsAttribute($value)
        {
            return $value ? json_decode(\App\Support\Encryption::decrypt($value), true) : [];
        }

    protected $casts = [
        'detected_topics' => 'array',
        'topics_avoided' => 'array',
        'session_intensity' => 'float',
        'emotional_tone' => 'float',
        'absolutist_count' => 'integer',
        'self_criticism_count' => 'integer',
        'message_count' => 'integer',
        'vagueness_count' => 'integer',
        'abstraction_count' => 'integer',
        'avoidance_detected_count' => 'integer',
        'grandiosity_detected' => 'boolean',
        'self_mythologizing_detected' => 'boolean',
        'last_message_at' => 'datetime',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    /**
     * Get the user profile for this session
     */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Get all messages in this session
     */
    public function messages(): HasMany
    {
        return $this->hasMany(Message::class);
    }

    /**
     * Get emotional snapshot for this session
     */
    public function emotionalSnapshot(): BelongsTo
    {
        return $this->belongsTo(EmotionalSnapshot::class);
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
