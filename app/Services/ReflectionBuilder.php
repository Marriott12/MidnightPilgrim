<?php

namespace App\Services;

use App\Models\Note;
use App\Models\Quote;
use Illuminate\Support\Str;

class ReflectionBuilder
{
    protected AdjacencyEngine $adjacency;

    public function __construct()
    {
        $this->adjacency = app(AdjacencyEngine::class);
    }

    /**
     * Build a reflection array for a Note or Quote.
     * Returns ['source' => [...], 'adjacent' => [...]]
     */
    public function build($item, int $limit = 3): array
    {
        $source = $this->normalizeSource($item);

        $adjacent = collect();

        if ($source['type'] === 'note' && $source['model'] instanceof Note) {
            $adjacent = $this->adjacency->findAdjacent($source['model'], $limit)->take($limit);
        } elseif ($source['type'] === 'quote' && $source['model'] instanceof Quote) {
            // If the quote links to a source note, use it for adjacency
            if ($source['model']->source_note_id) {
                $note = Note::find($source['model']->source_note_id);
                if ($note) {
                    $adjacent = $this->adjacency->findAdjacent($note, $limit)->take($limit);
                }
            }
        }

        return [
            'source' => $source,
            'adjacent' => $adjacent->map(function ($n) {
                return [
                    'slug' => $n->slug ?? null,
                    'title' => $n->title ?? null,
                ];
            })->values()->all(),
        ];
    }

    protected function normalizeSource($item): array
    {
        if ($item instanceof Note) {
            return ['type' => 'note', 'model' => $item, 'slug' => $item->slug ?? null, 'title' => $item->title ?? null];
        }

        if ($item instanceof Quote) {
            return ['type' => 'quote', 'model' => $item, 'slug' => $item->slug ?? null, 'title' => null];
        }

        // Try to resolve by slug
        if (is_string($item)) {
            $note = Note::where('slug', $item)->first();
            if ($note) {
                return ['type' => 'note', 'model' => $note, 'slug' => $note->slug ?? null, 'title' => $note->title ?? null];
            }
        }

        return ['type' => 'unknown', 'model' => null, 'slug' => null, 'title' => null];
    }

    /**
     * Store reflection as Markdown with YAML frontmatter if requested.
     * Returns the path written or null if skipped (already exists).
     */
    public function storeAsMarkdown(array $reflection, bool $force = false): ?string
    {
        $source = $reflection['source'];
        $slug = $source['slug'] ?? Str::slug($source['title'] ?? 'reflection');
        $date = now()->format('Y-m-d');

        $dir = storage_path('app/reflections');
        if (! is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $filename = $dir . DIRECTORY_SEPARATOR . $slug . '-' . $date . '.md';
        if (file_exists($filename) && ! $force) {
            return null;
        }

        $front = [
            'date' => $date,
            'source' => $slug,
            'related' => array_column($reflection['adjacent'], 'slug'),
        ];

        $yaml = "---\n" . yaml_emit($front) . "---\n\n";

        $body = '';
        if ($source['type'] === 'note' && $source['model']) {
            $body = $source['model']->body ?? '';
        }

        $adjList = '';
        foreach ($reflection['adjacent'] as $adj) {
            $adjList .= '- ' . ($adj['slug'] ?? '') . PHP_EOL;
        }

        $content = $yaml . $body . PHP_EOL . PHP_EOL . "Related:\n" . $adjList;

        file_put_contents($filename, $content);

        // Make read-only where supported
        @chmod($filename, 0444);

        return $filename;
    }
}
