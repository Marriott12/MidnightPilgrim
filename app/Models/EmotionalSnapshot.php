<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * EmotionalSnapshot Model - SESSION METRICS
 * 
 * Captures emotional state at the end of each session.
 * Used to track patterns over time without retaining full conversation content.
 */
class EmotionalSnapshot extends Model
{
    public $timestamps = false;

    protected $fillable = [
        'user_profile_id',
        'session_id',
        'intensity',
        'tone',
        'absolutist_count',
        'self_criticism_count',
        'topics',
        'hour_of_day',
    ];

        // Encrypt intensity, tone, topics
        public function setIntensityAttribute($value)
        {
            $this->attributes['intensity'] = \App\Support\Encryption::encrypt($value);
        }
        public function getIntensityAttribute($value)
        {
            return $value ? (float) \App\Support\Encryption::decrypt($value) : null;
        }
        public function setToneAttribute($value)
        {
            $this->attributes['tone'] = \App\Support\Encryption::encrypt($value);
        }
        public function getToneAttribute($value)
        {
            return $value ? (float) \App\Support\Encryption::decrypt($value) : null;
        }
        public function setTopicsAttribute($value)
        {
            $this->attributes['topics'] = \App\Support\Encryption::encrypt(json_encode($value));
        }
        public function getTopicsAttribute($value)
        {
            return $value ? json_decode(\App\Support\Encryption::decrypt($value), true) : [];
        }

    protected $casts = [
        'topics' => 'array',
        'intensity' => 'float',
        'tone' => 'float',
        'absolutist_count' => 'integer',
        'self_criticism_count' => 'integer',
        'hour_of_day' => 'integer',
        'created_at' => 'datetime',
    ];

    /**
     * Get the user profile for this snapshot
     */
    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    /**
     * Get the session for this snapshot
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(Session::class);
    }
}
