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

class Note extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'body',
        'source_note_id',
        'path',
        'confidence', // low | medium | high
        'visibility', // private | reflective | shareable
    ];

    protected $attributes = [
        'visibility' => 'private',
        'type' => 'idea',
        'body' => '',
    ];

    /**
     * Whether this item may be marked shareable.
     * By default notes may be shared; mental-health artifacts are handled elsewhere.
     */
    public function canBeShared(): bool
    {
        return true;
    }
}
