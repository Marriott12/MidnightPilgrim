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
     * Rebuild cache from notes with improved extraction
     */
    public static function rebuildCache(): int
    {
        self::truncate();
        
        $notes = Note::all();
        $count = 0;

        foreach ($notes as $note) {
            // Use 'body' field, not 'content'
            if (empty($note->body)) {
                continue;
            }
            
            $lines = self::extractShortLines($note->body, $note);
            
            foreach ($lines as $lineData) {
                self::create([
                    'note_id' => $note->id,
                    'line' => $lineData['text'],
                    'weight' => $lineData['weight'],
                ]);
                $count++;
            }
        }

        return $count;
    }

    /**
     * Extract short meaningful lines with intelligent weighting
     */
    private static function extractShortLines(string $content, Note $note): array
    {
        // Remove markdown headers and code blocks
        $content = preg_replace('/^#+\s+.+$/m', '', $content);
        $content = preg_replace('/```[\\s\\S]*?```/', '', $content);
        
        // Split into sentences
        $sentences = preg_split('/[.!;—]+\\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        $shortLines = [];
        foreach ($sentences as $sentence) {
            $sentence = trim($sentence);
            $length = strlen($sentence);
            $wordCount = str_word_count($sentence);
            
            // Keep sentences 20-100 chars, 4-20 words, not questions
            if ($length < 20 || $length > 100) {
                continue;
            }
            
            if ($wordCount < 4 || $wordCount > 20) {
                continue;
            }
            
            if (str_ends_with($sentence, '?')) {
                continue;
            }
            
            if (empty($sentence)) {
                continue;
            }
            
            // Calculate weight based on quality indicators
            $weight = 1;
            
            // Favor lines with punctuation variety (em dash, colon, semicolon)
            if (str_contains($sentence, '—') || str_contains($sentence, ':')) {
                $weight += 2;
            }
            
            // Favor lines from reflective notes
            if ($note->visibility === 'reflective') {
                $weight += 3;
            }
            
            // Favor lines from poems
            if ($note->type === 'poem') {
                $weight += 2;
            }
            
            // Detect rhythm/parallelism (repeated words, similar structure)
            if (preg_match('/(\\b\\w+\\b).*\\1/', $sentence)) {
                $weight += 1;
            }
            
            // Favor recent notes slightly
            $daysSinceCreation = now()->diffInDays($note->created_at);
            if ($daysSinceCreation <= 7) {
                $weight += 2;
            } elseif ($daysSinceCreation <= 30) {
                $weight += 1;
            }
            
            // Perfect length sweet spot (6-12 words)
            if ($wordCount >= 6 && $wordCount <= 12) {
                $weight += 1;
            }
            
            $shortLines[] = [
                'text' => $sentence,
                'weight' => max(1, $weight), // Minimum weight of 1
            ];
        }

        return $shortLines;
    }
}
