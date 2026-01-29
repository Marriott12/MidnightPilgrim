<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Quote;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class QuoteEngine
{
    /**
     * Extract manually-marked quotes from a note.
     * Only lines starting with '>' are considered.
     */
    public function extractFromNote(Note $note): array
    {
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
     * Validate quote length and integrity.
     */
    protected function isValidQuote(string $text): bool
    {
        $wordCount = str_word_count($text);

        return $wordCount >= 6 && $wordCount <= 40;
    }

    /**
     * Store quote as Markdown + metadata.
     */
    protected function storeQuote(Note $note, string $text): Quote
    {
        $slug = Str::slug(Str::limit($text, 50, ''));
        $date = now()->format('Y-m-d');

        $path = "quotes/{$date}--{$slug}.md";

        $markdown = $this->buildMarkdown($note, $text);

        Storage::disk('local')->put($path, $markdown);

        return Quote::create([
            'slug' => $slug,
            'source_note_id' => $note->id,
            'path' => $path,
            'confidence' => 'medium',
        ]);
    }

    /**
     * Build quote markdown with traceability.
     */
    protected function buildMarkdown(Note $note, string $text): string
    {
        $yaml = "---\n";
        $yaml .= "source_note: {$note->slug}\n";
        $yaml .= "source_type: {$note->type}\n";
        $yaml .= "date_extracted: " . now()->toDateString() . "\n";
        $yaml .= "confidence: medium\n";
        $yaml .= "---\n\n";

        return $yaml . $text . "\n";
    }
}
