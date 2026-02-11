<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

/**
 * ShortLine Model - RANDOM REFLECTION CACHE
 * 
 * Stores weighted lines extracted from notes for the Random button.
 * No metadata, no labels, just the line itself.
 */
class ShortLine extends Model
{
    protected $table = 'short_lines_cache';

    protected $fillable = [
        'note_id',
        'line',
        'weight',
    ];

    protected $casts = [
        'weight' => 'integer',
    ];

    /**
     * Get a weighted random line
     */
    public static function getWeightedRandom(): ?string
    {
        $lines = self::all();
        
        if ($lines->isEmpty()) {
            return null;
        }

        $totalWeight = $lines->sum('weight');
        $random = rand(1, (int)$totalWeight);
        $currentWeight = 0;

        foreach ($lines as $line) {
            $currentWeight += $line->weight;
            if ($random <= $currentWeight) {
                return $line->line;
            }
        }

        return $lines->first()->line;
    }

    /**
     * Rebuild cache from notes
     */
    public static function rebuildCache(): int
    {
        self::truncate();
        
        $notes = Note::all();
        $count = 0;

        foreach ($notes as $note) {
            // Skip empty content
            if (empty($note->content)) {
                continue;
            }
            
            $lines = self::extractShortLines($note->content);
            
            foreach ($lines as $line) {
                self::create([
                    'note_id' => $note->id,
                    'line' => $line,
                    'weight' => 1, // Equal weight for now
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Extract short meaningful lines from content
     * (Sentences under ~100 chars, not questions, not empty)
     */
    private static function extractShortLines(string $content): array
    {
        // Remove markdown headers
        $content = preg_replace('/^#+\s+.+$/m', '', $content);
        
        // Split into sentences
        $sentences = preg_split('/[.!]\s+/', $content);
        
        $shortLines = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            
            // Keep sentences 20-100 chars, not questions
            if (strlen($sentence) >= 20 
                && strlen($sentence) <= 100 
                && !str_ends_with($sentence, '?')
                && !empty($sentence)) {
                $shortLines[] = $sentence;
            }
        }

        return $shortLines;
    }
}
