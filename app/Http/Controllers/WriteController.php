<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\MarkdownIngestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use Symfony\Component\Yaml\Yaml;

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

        // Use provided title or extract from first line or default to 'Untitled'
        $title = $request->input('title');
        if (empty($title)) {
            $title = trim(explode("\n", $body)[0]) ?: 'Untitled';
        }

        // Check for duplicate: same title and body content
        $existingNote = Note::where('title', $title)
            ->where('body', Str::limit($body, 500))
            ->where('created_at', '>=', now()->subMinutes(5))
            ->first();

        if ($existingNote) {
            return redirect('/read')->with('info', 'Note already saved.');
        }

        // Generate slug from first line
        $firstLine = Str::limit(explode("\n", $body)[0], 50, '');
        $slug = Str::slug($firstLine ?: 'untitled');

        // Ensure unique slug
        $date = now()->format('Y-m-d');
        $timestamp = now()->format('His');
        $filename = "{$date}--{$timestamp}--{$slug}.md";
        
        $path = "vault/{$filename}";

        // Build markdown with frontmatter
        $markdown = $this->buildMarkdown($title, $slug, $type, $visibility, $body);

        // Store as immutable markdown (Phase 1)
        Storage::disk('local')->put($path, $markdown);

        // Optional: Create database record for querying
        Note::create([
            'title' => $title,
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
     * @param string $title
     * @param string $slug
     * @param string $type
     * @param string $visibility
     * @param string $body
     * @return string
     */
    protected function buildMarkdown(string $title, string $slug, string $type, string $visibility, string $body): string
    {
        $yaml = "---\n";
        $yaml .= "title: {$title}\n";
        $yaml .= "slug: {$slug}\n";
        $yaml .= "type: {$type}\n";
        $yaml .= "date: " . now()->toIso8601String() . "\n";
        $yaml .= "visibility: {$visibility}\n";
        $yaml .= "---\n\n";

        return $yaml . $body . "\n";
    }

    /**
     * Show edit form for a note
     */
    public function edit(string $slug)
    {
        $note = Note::where('slug', $slug)->firstOrFail();
        
        // Read the full markdown file
        $markdown = Storage::disk('local')->get($note->path);
        
        // Parse frontmatter to get full body
        $body = $markdown;
        if (preg_match('/^---\s*\n(.*?)\n---\s*\n(.*)$/s', $markdown, $matches)) {
            $body = $matches[2];
        }
        
        return view('write', [
            'note' => $note,
            'body' => $body,
            'isEditing' => true
        ]);
    }

    /**
     * Update an existing note
     */
    public function update(Request $request, string $slug)
    {
        $request->validate([
            'body' => 'required|string|min:1',
            'type' => 'nullable|in:note,poem',
            'visibility' => 'nullable|in:private,reflective,shareable',
        ]);

        $note = Note::where('slug', $slug)->firstOrFail();
        
        $body = $request->input('body');
        $type = $request->input('type', $note->type);
        $visibility = $request->input('visibility', $note->visibility);
        
        // Use provided title or extract from first line
        $title = $request->input('title');
        if (empty($title)) {
            $title = trim(explode("\n", $body)[0]) ?: 'Untitled';
        }

        // Update markdown file with new content
        $markdown = $this->buildMarkdown($title, $slug, $type, $visibility, $body);
        Storage::disk('local')->put($note->path, $markdown);

        // Update database record
        $note->update([
            'title' => $title,
            'type' => $type,
            'body' => Str::limit($body, 500),
            'visibility' => $visibility,
        ]);

        return redirect('/view/notes/' . $slug)->with('success', 'Updated quietly.');
    }

    /**
     * Delete a note
     */
    public function destroy(string $slug)
    {
        $note = Note::where('slug', $slug)->firstOrFail();
        
        // Delete markdown file
        Storage::disk('local')->delete($note->path);
        
        // Delete database record
        $note->delete();

        return redirect('/read')->with('success', 'Deleted quietly.');
    }
}
