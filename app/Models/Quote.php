<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * Quote Model - Immutable Foundation
 * 
 * PHASE 0: VISIBILITY COVENANT
 * ---------------------------
 * All quotes have visibility: private (default) | reflective | shareable
 * Quotes inherit visibility rules but can be elevated independently.
 * User must explicitly choose to share.
 * 
 * MIDNIGHT PILGRIM WILL NEVER:
 * - Automatically select "best" quotes
 * - Rank or score quotes by engagement
 * - Recommend quotes based on mood analysis
 * - Share quotes without explicit consent
 */
class Quote extends Model
{
    protected $fillable = [
        'slug',
        'body',
        'source_note_id',
        'path',
        'confidence',
        'visibility',
    ];

    /**
     * IMMUTABLE DEFAULTS
     * All new quotes are private by default.
     */
    protected $attributes = [
        'visibility' => 'private',
        'confidence' => 'manual',
    ];

    /**
     * VISIBILITY ENFORCEMENT
     */
    public function canBeShared(): bool
    {
        return true;
    }

    public function scopeShareableOnly($query)
    {
        return $query->where('visibility', 'shareable');
    }

    /**
     * STORAGE PATH ENFORCEMENT
     * Quotes belong in storage/quotes/ only.
     */
    public function getStorageDirectory(): string
    {
        return 'quotes';
    }

    /**
     * Extract the best line from a note using a simple scoring system.
     *
     * @param string $noteText
     * @return string|null
     */
    public static function extractBestQuoteLine(string $noteText): ?string
    {
        $lines = preg_split('/\r\n|\r|\n/', $noteText);
        if (!$lines) return null;

        // Filter out empty or trivial lines
        $filtered = array_filter(array_map('trim', $lines), function ($line) {
            return strlen($line) >= 10;
        });
        if (empty($filtered)) return null;

        // Score each line
        $scored = [];
        foreach ($filtered as $line) {
            $score = 0;
            $len = strlen($line);
            // Prefer lines ending with strong punctuation
            if (preg_match('/[.!?]$/', $line)) $score += 2;
            // Prefer lines of reasonable length (40-120 chars)
            if ($len >= 40 && $len <= 120) $score += 1;
            // Bonus for lines with commas or semicolons (complexity)
            if (strpos($line, ',') !== false || strpos($line, ';') !== false) $score += 1;
            // Slight penalty for lines that are too long
            if ($len > 140) $score -= 1;
            $scored[] = ['line' => $line, 'score' => $score];
        }
        if (empty($scored)) return null;

        // Find the highest score
        $maxScore = max(array_column($scored, 'score'));
        $topLines = array_filter($scored, function ($item) use ($maxScore) {
            return $item['score'] === $maxScore;
        });
        // Randomly pick among the top scoring lines
        $topLines = array_values($topLines);
        $chosen = $topLines[random_int(0, count($topLines) - 1)]['line'];
        return $chosen;
    }
}
