<?php
/**
 * Midnight Pilgrim – Local‑First Knowledge Engine
 * Version: 0.1 (Foundation)
 * Stack: Laravel (API‑first), SQLite, Markdown
 * Philosophy: Silence > Noise | Traceability > Automation
 */

// This file acts as an architectural anchor and bootstrap reference.
// Actual implementation is split across the structure below.

/*
PROJECT STRUCTURE
-----------------
midnight-pilgrim/
├── app/
│   ├── Models/
│   │   ├── Note.php
│   │   ├── Quote.php
│   │   ├── DailyThought.php
│   │   └── Interaction.php
│   ├── Services/
│   │   ├── NoteService.php
│   │   ├── QuoteEngine.php
│   │   ├── DailyThoughtEngine.php
│   │   └── AssistantService.php
│   ├── Console/
│   │   └── Commands/
│   │       └── GenerateDailyThought.php
│   └── Policies/
│       └── SilencePolicy.php
│
├── database/
│   ├── migrations/
│   └── database.sqlite
│
├── storage/
│   ├── vault/            // Markdown notes live here (Obsidian‑compatible)
│   ├── quotes/
│   └── thoughts/
│
├── routes/
│   └── api.php
│
└── README.md
*/

/*
CORE PRINCIPLE
--------------
Laravel is not the brain.
Markdown is the brain.
Laravel is the steward.
*/

// Nothing executes here intentionally.
// Midnight Pilgrim begins when you write your first note.


namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Note Model - Immutable Foundation
 * 
 * PHASE 0: VISIBILITY COVENANT
 * ---------------------------
 * All notes have visibility: private (default) | reflective | shareable
 * Visibility may only be changed explicitly by the user, never automatically.
 * 
 * MIDNIGHT PILGRIM WILL NEVER INCLUDE:
 * - Analytics or tracking of note creation/access
 * - Notifications about notes
 * - Social feeds or sharing to external platforms
 * - Engagement metrics (views, likes, streaks)
 * - Recommendation algorithms
 * - Sentiment analysis or AI scoring
 * - Automated content modification
 * - Gamification elements
 */
class Note extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'body',
        'source_note_id',
        'path',
        'type',
        'mood',
        'tags',
        'confidence',
        'visibility',
    ];

    protected $casts = [
        'tags' => 'array',
    ];

    /**
     * IMMUTABLE DEFAULTS
     * All new notes are private by default.
     * Visibility changes require explicit user action.
     */
    protected $attributes = [
        'visibility' => 'private',
        'type' => 'idea',
        'body' => '',
    ];

    /**
     * VISIBILITY ENFORCEMENT
     * Notes can be shared, but only with explicit user consent.
     * This is NOT a mental health artifact.
     */
    public function canBeShared(): bool
    {
        return true;
    }

    /**
     * VISIBILITY SCOPES
     * Enforce access boundaries for different contexts.
     */
    public function scopePrivateOnly($query)
    {
        return $query->where('visibility', 'private');
    }

    public function scopeReflectiveOrHigher($query)
    {
        return $query->whereIn('visibility', ['reflective', 'shareable']);
    }

    public function scopeShareableOnly($query)
    {
        return $query->where('visibility', 'shareable');
    }

    /**
     * STORAGE PATH ENFORCEMENT
     * Notes belong in storage/vault/ only.
     */
    public function getStorageDirectory(): string
    {
        return 'vault';
    }
}
