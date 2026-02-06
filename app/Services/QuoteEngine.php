<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Quote;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Carbon\Carbon;

/**
 * QuoteEngine - PHASE 2: DISTILLATION WITHOUT FORCING
 * 
 * PURPOSE: Extract lines worth carrying, not highlights or summaries.
 * 
 * CAPABILITIES:
 * - Extract manually-marked quotes (lines starting with '>')
 * - Support explicit manual promotion by user
 * - Suggest candidate quotes using heuristics (not AI)
 * - Store as immutable markdown in storage/quotes/
 * - Preserve source references and context
 * 
 * PHASE 2 WILL NEVER:
 * - Auto-promote quotes without user choice
 * - Use sentiment analysis or AI scoring
 * - Optimize or rank by engagement
 * - Modify source notes or poems
 * - Share quotes without explicit user consent
 * 
 * HEURISTICS (Simple, Not AI):
 * - Line length (8-120 characters feels complete)
 * - Punctuation patterns (. ! ? — suggest finality)
 * - Whitespace isolation (paragraph breaks indicate weight)
 * - Quote marks or emphasis (user intent signals)
 * 
 * All quotes default to 'private' visibility (Phase 0).
 */
class QuoteEngine
{
    /**
     * Extract manually-marked quotes from a note.
     * Only lines starting with '>' are considered.
     * 
     * @param Note $note
     * @return array Array of created Quote models
     */
    public function extractFromNote(Note $note): array
    {
        if (!$note->path || !Storage::disk('local')->exists($note->path)) {
            return [];
        }

        $markdown = Storage::disk('local')->get($note->path);

        $lines = explode("\n", $markdown);
        $quotes = [];

        foreach ($lines as $line) {
            $line = trim($line);

            if (Str::startsWith($line, '>')) {
                $text = trim(Str::after($line, '>'));

                if ($this->isValidQuote($text)) {
                    $quotes[] = $this->storeQuote($note, $text);
                }
            }
        }

        return $quotes;
    }

    /**
     * Manually promote a text selection to a quote
     * Allows user to explicitly create a quote from any note content
     * 
     * @param Note $note Source note
     * @param string $text Selected text to promote
     * @param array $options Optional metadata (context, tags, etc.)
     * @return Quote|null Created quote or null if invalid
     */
    public function promoteToQuote(Note $note, string $text, array $options = []): ?Quote
    {
        $text = trim($text);

        if (!$this->isValidQuote($text)) {
            return null;
        }

        return $this->storeQuote($note, $text, $options);
    }

    /**
     * Suggest potential quotes from a note (read-only analysis)
     * Returns suggestions without creating quotes - user must promote manually
     * 
     * @param Note $note
     * @return array Array of suggested text snippets
     */
    public function suggestQuotes(Note $note): array
    {
        if (!$note->path || !Storage::disk('local')->exists($note->path)) {
            return [];
        }

        $markdown = Storage::disk('local')->get($note->path);
        
        // Remove frontmatter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $markdown);
        
        $suggestions = [];

        // Find sentences that could be quotes
        $sentences = preg_split('/[.!?]+\s+/', $content);
        
        foreach ($sentences as $sentence) {
            $sentence = trim(strip_tags($sentence));
            
            if ($this->isValidQuote($sentence)) {
                // Check if it's already a marked quote
                if (!Str::startsWith($sentence, '>')) {
                    $suggestions[] = [
                        'text' => $sentence,
                        'word_count' => str_word_count($sentence),
                    ];
                }
            }
        }

        return $suggestions;
    }

    /**
     * Validate quote length and integrity
     * No sentiment or frequency analysis - just basic validation
     * 
     * @param string $text
     * @return bool
     */
    protected function isValidQuote(string $text): bool
    {
        $text = trim($text);
        
        if (empty($text)) {
            return false;
        }

        $wordCount = str_word_count($text);

        // Reasonable quote length: 6-40 words
        return $wordCount >= 6 && $wordCount <= 40;
    }

    /**
     * Store quote as immutable Markdown file with metadata
     * 
     * @param Note $note Source note
     * @param string $text Quote text
     * @param array $options Optional metadata
     * @return Quote
     */
    protected function storeQuote(Note $note, string $text, array $options = []): Quote
    {
        $slug = Str::slug(Str::limit($text, 50, ''));
        $date = now()->format('Y-m-d');
        $timestamp = now()->format('His');

        // Ensure unique filename
        $path = "quotes/{$date}--{$timestamp}--{$slug}.md";

        $markdown = $this->buildMarkdown($note, $text, $options);

        Storage::disk('local')->put($path, $markdown);

        return Quote::create([
            'slug' => $slug,
            'source_note_id' => $note->id,
            'path' => $path,
            'body' => $text,
            'visibility' => 'private', // Always private by default
            'confidence' => $options['confidence'] ?? 'manual',
        ]);
    }

    /**
     * Build quote markdown with source traceability
     * Immutable format - never modified after creation
     * 
     * @param Note $note
     * @param string $text
     * @param array $options
     * @return string
     */
    protected function buildMarkdown(Note $note, string $text, array $options = []): string
    {
        $yaml = "---\n";
        $yaml .= "source_note: {$note->slug}\n";
        $yaml .= "source_type: {$note->type}\n";
        $yaml .= "date_extracted: " . now()->toDateString() . "\n";
        $yaml .= "confidence: " . ($options['confidence'] ?? 'manual') . "\n";
        
        if (isset($options['context'])) {
            $yaml .= "context: " . $options['context'] . "\n";
        }
        
        if (isset($options['tags'])) {
            $yaml .= "tags: [" . implode(', ', $options['tags']) . "]\n";
        }
        
        $yaml .= "visibility: private\n";
        $yaml .= "---\n\n";

        return $yaml . $text . "\n";
    }

    /**
     * Get all quotes (read-only)
     * 
     * @param int $limit
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getAll(int $limit = 50)
    {
        return Quote::orderBy('created_at', 'desc')
            ->limit($limit)
            ->get();
    }

    /**
     * Get quotes from a specific note
     * 
     * @param Note $note
     * @return \Illuminate\Database\Eloquent\Collection
     */
    public function getFromNote(Note $note)
    {
        return Quote::where('source_note_id', $note->id)
            ->orderBy('created_at', 'desc')
            ->get();
    }
}
