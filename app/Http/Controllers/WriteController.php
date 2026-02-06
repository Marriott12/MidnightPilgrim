<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\MarkdownIngestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * WriteController - PHASE 2-5: SILENCE-FIRST WRITING
 * 
 * Minimal interface for creating notes and poems.
 * No metrics, no prompts - like opening a notebook at night.
 */
class WriteController extends Controller
{
    /**
     * Show write form
     */
    public function create()
    {
        return view('write');
    }

    /**
     * Store note/poem as immutable markdown
     * 
     * PHASE 1: Markdown-first storage
     * PHASE 0: Default visibility is private
     */
    public function store(Request $request)
    {
        $request->validate([
            'body' => 'required|string|min:1',
            'type' => 'nullable|in:note,poem',
            'visibility' => 'nullable|in:private,reflective,shareable',
        ]);

        $body = $request->input('body');
        $type = $request->input('type', 'note');
        $visibility = $request->input('visibility', 'private'); // Phase 0 default

        // Generate slug from first line
        $firstLine = Str::limit(explode("\n", $body)[0], 50, '');
        $slug = Str::slug($firstLine ?: 'untitled');

        // Ensure unique slug
        $date = now()->format('Y-m-d');
        $timestamp = now()->format('His');
        $filename = "{$date}--{$timestamp}--{$slug}.md";
        
        $path = "vault/{$filename}";

        // Build markdown with frontmatter
        $markdown = $this->buildMarkdown($slug, $type, $visibility, $body);

        // Store as immutable markdown (Phase 1)
        Storage::disk('local')->put($path, $markdown);

        // Optional: Create database record for querying
        Note::create([
            'slug' => $slug,
            'type' => $type,
            'path' => $path,
            'body' => Str::limit($body, 500), // Store excerpt only
            'visibility' => $visibility,
        ]);

        return redirect('/read')->with('success', 'Saved quietly.');
    }

    /**
     * Build markdown file with YAML frontmatter
     * 
     * @param string $slug
     * @param string $type
     * @param string $visibility
     * @param string $body
     * @return string
     */
    protected function buildMarkdown(string $slug, string $type, string $visibility, string $body): string
    {
        $yaml = "---\n";
        $yaml .= "slug: {$slug}\n";
        $yaml .= "type: {$type}\n";
        $yaml .= "date: " . now()->toIso8601String() . "\n";
        $yaml .= "visibility: {$visibility}\n";
        $yaml .= "---\n\n";

        return $yaml . $body . "\n";
    }
}
