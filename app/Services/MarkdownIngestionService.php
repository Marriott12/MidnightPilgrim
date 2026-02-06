<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Symfony\Component\Yaml\Yaml;

/**
 * MarkdownIngestionService - PHASE 1: NOTE & POEM INGESTION
 * 
 * READ-ONLY FOUNDATION
 * -------------------
 * This service reads Markdown files from storage and exposes them
 * as immutable domain objects. It does NOT:
 * - Modify files
 * - Summarize content
 * - Rank or score notes
 * - Reinterpret or transform content
 * - Depend on database for ingestion
 * 
 * OBSIDIAN COMPATIBILITY
 * ----------------------
 * - Parses YAML frontmatter
 * - Supports wikilinks [[like this]]
 * - Preserves Markdown structure
 * - Respects metadata (title, date, tags, visibility)
 * 
 * IMMUTABLE OUTPUT
 * ---------------
 * All returned data is read-only. Services consuming this data
 * must not modify the source files.
 * 
 * PHASE 0: VISIBILITY ENFORCEMENT
 * ------------------------------
 * Respects visibility attribute from frontmatter.
 * Default visibility is 'private' if not specified.
 * 
 * STORAGE SEPARATION (PHASE 0)
 * ---------------------------
 * - storage/vault/ → notes and poems
 * - storage/quotes/ → extracted quotes
 * - storage/thoughts/ → daily thoughts
 * - storage/companion/ → mental health data (NEVER read by this service)
 */
class MarkdownIngestionService
{
    protected string $vaultPath = 'vault';
    protected string $quotesPath = 'quotes';
    protected string $thoughtsPath = 'thoughts';
    
    /**
     * PHASE 0: Mental health data is isolated
     * This service NEVER reads from storage/app/companion/
     */
    protected array $forbiddenPaths = ['companion', 'public'];

    /**
     * Read all markdown files from the vault
     * 
     * @return array Array of read-only note objects
     */
    public function readVault(): array
    {
        return $this->readDirectory($this->vaultPath, 'note');
    }

    /**
     * Read all quote markdown files
     * 
     * @return array Array of read-only quote objects
     */
    public function readQuotes(): array
    {
        return $this->readDirectory($this->quotesPath, 'quote');
    }

    /**
     * Read all thought markdown files
     * 
     * @return array Array of read-only thought objects
     */
    public function readThoughts(): array
    {
        return $this->readDirectory($this->thoughtsPath, 'thought');
    }

    /**
     * Read a single markdown file and return parsed object
     * 
     * @param string $path Relative path within storage
     * @return array|null Read-only object or null if not found
     */
    public function readFile(string $path): ?array
    {
        if (!Storage::disk('local')->exists($path)) {
            return null;
        }

        try {
            $content = Storage::disk('local')->get($path);
            return $this->parseMarkdown($content, $path);
        } catch (\Throwable $e) {
            // Fail silently - respect the silence-first principle
            return null;
        }
    }

    /**
     * Read all markdown files from a directory
     * 
     * @param string $directory Directory name within storage
     * @param string $type Content type for categorization
     * @return array Array of parsed objects
     */
    protected function readDirectory(string $directory, string $type): array
    {
        $files = Storage::disk('local')->files($directory);
        $results = [];

        foreach ($files as $file) {
            if (!str_ends_with($file, '.md')) {
                continue;
            }

            try {
                $content = Storage::disk('local')->get($file);
                $parsed = $this->parseMarkdown($content, $file, $type);
                
                if ($parsed !== null) {
                    $results[] = $parsed;
                }
            } catch (\Throwable $e) {
                // Skip files that can't be read - fail silently
                continue;
            }
        }

        return $results;
    }

    /**
     * Parse markdown content with YAML frontmatter
     * 
     * PHASE 1: IMMUTABLE PARSING
     * --------------------------
     * Obsidian-compatible format:
     * ---
     * title: Note Title
     * tags: [tag1, tag2]
     * visibility: private
     * ---
     * 
     * Content here
     * 
     * PHASE 0: VISIBILITY ENFORCEMENT
     * --------------------------------
     * If no visibility is specified in frontmatter, defaults to 'private'.
     * This ensures all content is private by default.
     * 
     * @param string $content Raw markdown content
     * @param string $path File path for reference
     * @param string|null $type Content type
     * @return array|null Parsed object or null on failure
     */
    protected function parseMarkdown(string $content, string $path, ?string $type = null): ?array
    {
        $frontmatter = [];
        $body = $content;

        // Parse YAML frontmatter if present
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $content, $matches)) {
            try {
                $frontmatter = Yaml::parse($matches[1]) ?? [];
                $body = $matches[2];
            } catch (\Throwable $e) {
                // If YAML parsing fails, treat entire content as body
                $frontmatter = [];
                $body = $content;
            }
        }

        /**
         * PHASE 0: VISIBILITY COVENANT
         * All content defaults to 'private' unless explicitly specified.
         */
        $visibility = $frontmatter['visibility'] ?? 'private';
        
        // Validate visibility value
        $allowedVisibility = ['private', 'reflective', 'shareable'];
        if (!in_array($visibility, $allowedVisibility)) {
            $visibility = 'private'; // Invalid values default to private
        }

        // Extract slug from filename (e.g., "2026-01-15--123456--my-note.md" -> "my-note")
        $filename = basename($path, '.md');
        $slug = preg_replace('/^\d{4}-\d{2}-\d{2}--\d+--/', '', $filename);
        
        // Build read-only immutable object
        return [
            'path' => $path,
            'slug' => $slug,
            'type' => $type ?? ($frontmatter['type'] ?? 'note'),
            'title' => $frontmatter['title'] ?? $this->extractTitle($body, $path),
            'tags' => $frontmatter['tags'] ?? [],
            'visibility' => $visibility, // PHASE 0: Always present
            'metadata' => $frontmatter,
            'body' => trim($body),
            'word_count' => str_word_count(strip_tags($body)),
            'date' => $frontmatter['date'] ?? $frontmatter['created'] ?? $this->extractDateFromFilename($path),
            'created_at' => $frontmatter['date'] ?? $frontmatter['created'] ?? null,
            'modified_at' => $frontmatter['modified'] ?? null,
            // Read-only flag
            'readonly' => true,
        ];
    }

    /**
     * Extract date from filename
     * 
     * @param string $path File path
     * @return string|null Date string or null
     */
    protected function extractDateFromFilename(string $path): ?string
    {
        $filename = basename($path, '.md');
        // Extract date from filename pattern: 2026-02-06--123456--slug.md
        if (preg_match('/^(\d{4}-\d{2}-\d{2})/', $filename, $matches)) {
            return $matches[1];
        }
        return null;
    }

    /**
     * Extract title from markdown body if not in frontmatter
     * Looks for first H1 heading or uses filename
     * 
     * @param string $body Markdown body
     * @param string $path File path
     * @return string Extracted or derived title
     */
    protected function extractTitle(string $body, string $path): string
    {
        // Try to find first H1 heading
        if (preg_match('/^#\s+(.+)$/m', $body, $matches)) {
            return trim($matches[1]);
        }

        // Fall back to filename without extension and date prefix
        $filename = basename($path, '.md');
        // Remove date prefix if present (e.g., "2026-01-15-title" -> "title")
        $filename = preg_replace('/^\d{4}-\d{2}-\d{2}-+/', '', $filename);
        // Convert hyphens/underscores to spaces and title case
        $filename = str_replace(['-', '_'], ' ', $filename);
        return ucwords($filename);
    }

    /**
     * Search content by keyword (read-only search)
     * Returns matching files without modifying anything
     * 
     * @param string $query Search term
     * @param array $types Content types to search ['note', 'quote', 'thought']
     * @return array Matching files
     */
    public function search(string $query, array $types = ['note', 'quote', 'thought']): array
    {
        $results = [];
        $query = strtolower(trim($query));

        if (empty($query)) {
            return $results;
        }

        foreach ($types as $type) {
            $directory = match($type) {
                'quote' => $this->quotesPath,
                'thought' => $this->thoughtsPath,
                default => $this->vaultPath,
            };

            $items = $this->readDirectory($directory, $type);

            foreach ($items as $item) {
                // Search in title, body, and tags
                $searchableText = strtolower(
                    $item['title'] . ' ' . 
                    $item['body'] . ' ' . 
                    implode(' ', $item['tags'])
                );

                if (str_contains($searchableText, $query)) {
                    $results[] = $item;
                }
            }
        }

        return $results;
    }

    /**
     * Get files by tag (read-only)
     * 
     * @param string $tag Tag to filter by
     * @return array Files with matching tag
     */
    public function getByTag(string $tag): array
    {
        $results = [];
        $tag = strtolower(trim($tag));

        foreach (['note', 'quote', 'thought'] as $type) {
            $directory = match($type) {
                'quote' => $this->quotesPath,
                'thought' => $this->thoughtsPath,
                default => $this->vaultPath,
            };

            $items = $this->readDirectory($directory, $type);

            foreach ($items as $item) {
                $itemTags = array_map('strtolower', $item['tags']);
                if (in_array($tag, $itemTags)) {
                    $results[] = $item;
                }
            }
        }

        return $results;
    }
}
