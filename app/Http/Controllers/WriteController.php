<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Services\MarkdownIngestionService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
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
            'write_only' => 'nullable|boolean',
            'no_archive' => 'nullable|boolean',
        ]);

        $body = $request->input('body');
        $type = $request->input('type', 'note');
        $visibility = $request->input('visibility', 'private'); // Phase 0 default
        $writeOnly = (bool) $request->input('write_only', false);
        $noArchive = (bool) $request->input('no_archive', false);

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

        // Build markdown with frontmatter (including silence flags)
        $markdown = $this->buildMarkdown($title, $slug, $type, $visibility, $body, $writeOnly, $noArchive);

        try {
            // Store as immutable markdown (Phase 1)
            $stored = Storage::disk('local')->put($path, $markdown);
            
            if (!$stored) {
                return redirect()->back()
                    ->withInput()
                    ->withErrors(['error' => 'Failed to save markdown file.']);
            }

            // Optional: Create database record for querying
            $note = Note::create([
                'title' => $title,
                'slug' => $slug,
                'type' => $type,
                'path' => $path,
                'body' => Str::limit($body, 500), // Store excerpt only
                'visibility' => $visibility,
                'write_only' => $writeOnly,
                'no_archive' => $noArchive,
            ]);

            // PHASE 2: Auto-extract quotes UNLESS write_only mode is enabled
            if (!$writeOnly) {
                $quoteEngine = app(\App\Services\QuoteEngine::class);
                $quoteEngine->extractFromNote($note);
            }

            return redirect('/read')->with('success', 'Saved quietly.');
            
        } catch (\Exception $e) {
            Log::error('Note save failed: ' . $e->getMessage(), [
                'title' => $title,
                'slug' => $slug,
                'exception' => $e,
            ]);
            
            return redirect()->back()
                ->withInput()
                ->withErrors(['error' => 'Failed to save note: ' . $e->getMessage()]);
        }
    }

    /**
     * Build markdown file with YAML frontmatter
     * 
     * @param string $title
     * @param string $slug
     * @param string $type
     * @param string $visibility
     * @param string $body
     * @param bool $writeOnly
     * @param bool $noArchive
     * @return string
     */
    protected function buildMarkdown(
        string $title, 
        string $slug, 
        string $type, 
        string $visibility, 
        string $body, 
        bool $writeOnly = false, 
        bool $noArchive = false
    ): string {
        $yaml = "---\n";
        $yaml .= "title: {$title}\n";
        $yaml .= "slug: {$slug}\n";
        $yaml .= "type: {$type}\n";
        $yaml .= "date: " . now()->toIso8601String() . "\n";
        $yaml .= "visibility: {$visibility}\n";
        
        // Add silence feature flags if enabled
        if ($writeOnly) {
            $yaml .= "write_only: true\n";
        }
        if ($noArchive) {
            $yaml .= "no_archive: true\n";
        }
        
        $yaml .= "---\n\n";

        return $yaml . $body . "\n";
    }

    /**
     * Show edit form for a note
     */
    public function edit(string $slug)
    {
        // Use MarkdownIngestionService to find the note
        $ingestionService = app(MarkdownIngestionService::class);
        $items = $ingestionService->readVault();
        
        $note = null;
        foreach ($items as $item) {
            if (($item['slug'] ?? '') === $slug) {
                $note = $item;
                break;
            }
        }
        
        if (!$note) {
            abort(404, 'Note not found');
        }
        
        // Create a simple object for compatibility with the view
        $noteObject = (object) [
            'slug' => $note['slug'],
            'title' => $note['title'] ?? '',
            'type' => $note['type'] ?? 'note',
            'visibility' => $note['visibility'] ?? 'private',
        ];
        
        return view('write', [
            'note' => $noteObject,
            'body' => $note['body'] ?? '',
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
            'write_only' => 'nullable|boolean',
            'no_archive' => 'nullable|boolean',
        ]);

        // Find the markdown file
        $vaultFiles = Storage::files('vault');
        $filePath = null;
        
        foreach ($vaultFiles as $file) {
            if (basename($file, '.md') === $slug || strpos($file, "--{$slug}.md") !== false) {
                $filePath = $file;
                break;
            }
        }
        
        if (!$filePath) {
            abort(404, 'Note file not found');
        }
        
        $body = $request->input('body');
        $type = $request->input('type', 'note');
        $visibility = $request->input('visibility', 'private');
        $writeOnly = (bool) $request->input('write_only', false);
        $noArchive = (bool) $request->input('no_archive', false);
        
        // Use provided title or extract from first line
        $title = $request->input('title');
        if (empty($title)) {
            $title = trim(explode("\n", $body)[0]) ?: 'Untitled';
        }

        // Update markdown file with new content (including silence flags)
        $markdown = $this->buildMarkdown($title, $slug, $type, $visibility, $body, $writeOnly, $noArchive);
        Storage::disk('local')->put($filePath, $markdown);

        // Update database record if it exists
        $note = Note::where('slug', $slug)->first();
        if ($note) {
            $note->update([
                'title' => $title,
                'type' => $type,
                'body' => Str::limit($body, 500),
                'visibility' => $visibility,
                'write_only' => $writeOnly,
                'no_archive' => $noArchive,
            ]);
            
            // PHASE 2: Auto-extract quotes UNLESS write_only mode is enabled
            if (!$writeOnly) {
                $quoteEngine = app(\App\Services\QuoteEngine::class);
                $quoteEngine->extractFromNote($note);
            }
        }

        return redirect('/view/notes/' . $slug)->with('success', 'Updated quietly.');
    }

    /**
     * Delete a note
     */
    public function destroy(string $slug)
    {
        // Find the markdown file
        $vaultFiles = Storage::files('vault');
        $filePath = null;
        
        foreach ($vaultFiles as $file) {
            if (basename($file, '.md') === $slug || strpos($file, "--{$slug}.md") !== false) {
                $filePath = $file;
                break;
            }
        }
        
        if (!$filePath) {
            abort(404, 'Note file not found');
        }
        
        // Delete markdown file
        Storage::disk('local')->delete($filePath);
        
        // Delete database record if it exists
        Note::where('slug', $slug)->delete();

        return redirect('/read')->with('success', 'Deleted quietly.');
    }
}
