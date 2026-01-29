<?php

namespace App\Services;

use App\Models\Quote;
use App\Models\Note;
use App\Models\DailyThought;
use Illuminate\Support\Str;
use App\Policies\SilencePolicy;
use App\Services\TemporalMetadata;

class ReferenceResolver
{
    /**
     * ReferenceResolver hard constraints:
     * - Never implicitly surface private items (caller must pass explicitRequested=true).
     * - Return at most one reference per call.
     * - Fail quietly (return null) on any internal error.
     *
     * These constraints are part of Midnight Pilgrim's privacy and silence-first guarantees.
     */
    /**
     * Attempt to find one relevant reference for the given input text.
     * Returns an array with keys: type, slug, excerpt (max 2 lines), path/null
     * or null when nothing appropriate is found.
     */
    /**
     * @param string $input
     * @param bool $explicitRequested If true, allow returning private items when explicitly referenced
     * @param bool $includeTemporal If true, resolver may add a temporal phrase (caller should check SilencePolicy)
     *
     * @return array|null
     */
    public function resolve(string $input, bool $explicitRequested = false, bool $includeTemporal = false): ?array
    {
        $keywords = $this->extractKeywords($input);
        if (empty($keywords)) {
            return null;
        }

        $temporal = null;
        $temporalSvc = app(TemporalMetadata::class);
        $policy = app(SilencePolicy::class);

        // 1. Try Quotes (read file at path if available)
        $quote = Quote::inRandomOrder()->get()->first(function ($q) use ($keywords, $explicitRequested) {
            // enforce visibility: never return private unless explicitly requested
            if (isset($q->visibility) && $q->visibility === 'private' && ! $explicitRequested) {
                return false;
            }
            return $this->pathHasKeywords($q->path ?? null, $keywords);
        });
        if ($quote) {
            $excerpt = $this->excerptFromPath($quote->path, $keywords);
            if ($excerpt) {
                $result = [
                    'type' => 'quote',
                    'slug' => $quote->slug ?? 'quote',
                    'excerpt' => $excerpt,
                    'path' => $quote->path ?? null,
                ];
                if ($includeTemporal && isset($quote->created_at)) {
                    $when = $temporalSvc->describe($quote->created_at);
                    if ($when && $policy->allowTemporalForSlug($quote->slug ?? '')) {
                        $result['temporal'] = $when;
                    }
                }
                return $result;
            }
        }

        // 2. Try Notes
        $note = Note::inRandomOrder()->get()->first(function ($n) use ($keywords, $explicitRequested) {
            if (isset($n->visibility) && $n->visibility === 'private' && ! $explicitRequested) {
                return false;
            }
            return $this->pathHasKeywords($n->path ?? null, $keywords);
        });
        if ($note) {
            $excerpt = $this->excerptFromPath($note->path, $keywords);
            if ($excerpt) {
                $result = [
                    'type' => 'note',
                    'slug' => $note->slug ?? 'note',
                    'excerpt' => $excerpt,
                    'path' => $note->path ?? null,
                ];
                if ($includeTemporal && isset($note->created_at)) {
                    $when = $temporalSvc->describe($note->created_at);
                    if ($when && $policy->allowTemporalForSlug($note->slug ?? '')) {
                        $result['temporal'] = $when;
                    }
                }
                return $result;
            }
        }

        // 3. Try DailyThoughts (search body)
        $thought = DailyThought::latest()->get()->first(function ($t) use ($keywords, $explicitRequested) {
            if (isset($t->visibility) && $t->visibility === 'private' && ! $explicitRequested) {
                return false;
            }
            return $this->textHasKeywords($t->body ?? '', $keywords);
        });
        if ($thought) {
            $excerpt = $this->excerptFromText($thought->body ?? '', $keywords);
            if ($excerpt) {
                $result = [
                    'type' => 'daily_thought',
                    'slug' => $thought->slug ?? 'daily_thought',
                    'excerpt' => $excerpt,
                    'path' => null,
                ];
                if ($includeTemporal && isset($thought->date_generated)) {
                    $when = $temporalSvc->describe($thought->date_generated);
                    if ($when && $policy->allowTemporalForSlug($thought->slug ?? '')) {
                        $result['temporal'] = $when;
                    }
                }
                return $result;
            }
        }

        return null;
    }

    protected function extractKeywords(string $input): array
    {
        $words = preg_split('/[^\p{L}\p{N}]+/u', Str::lower($input));
        $words = array_filter($words, function ($w) {
            return $w !== '' && mb_strlen($w) >= 3;
        });
        // unique and preserve order
        $seen = [];
        $out = [];
        foreach ($words as $w) {
            if (! isset($seen[$w])) {
                $seen[$w] = true;
                $out[] = $w;
            }
            if (count($out) >= 8) break;
        }
        return $out;
    }

    protected function pathHasKeywords(?string $path, array $keywords): bool
    {
        if (empty($path) || ! is_string($path) || ! file_exists($path)) {
            return false;
        }
        $text = file_get_contents($path);
        return $this->textHasKeywords($text, $keywords);
    }

    protected function textHasKeywords(string $text, array $keywords): bool
    {
        $lower = Str::lower($text);
        foreach ($keywords as $k) {
            if (Str::contains($lower, $k)) {
                return true;
            }
        }
        return false;
    }

    protected function excerptFromPath(?string $path, array $keywords): ?string
    {
        if (empty($path) || ! file_exists($path)) {
            return null;
        }
        $text = file_get_contents($path);
        return $this->excerptFromText($text, $keywords);
    }

    protected function excerptFromText(string $text, array $keywords): ?string
    {
        $lines = preg_split('/\r?\n/', $text);
        foreach ($lines as $i => $line) {
            $lower = Str::lower($line);
            foreach ($keywords as $k) {
                if (Str::contains($lower, $k)) {
                    $excerptLines = [$line];
                    // include next non-empty line if exists to make up to 2 lines
                    if (isset($lines[$i + 1]) && trim($lines[$i + 1]) !== '') {
                        $excerptLines[] = $lines[$i + 1];
                    }
                    $excerpt = implode("\n", array_slice($excerptLines, 0, 2));
                    // Ensure max two lines
                    $excerpt = trim($excerpt);
                    // Return exact text only
                    return $excerpt !== '' ? $excerpt : null;
                }
            }
        }
        return null;
    }
}
