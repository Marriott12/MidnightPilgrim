<?php

namespace App\Http\Controllers;

use App\Models\Note;
use App\Models\Quote;
use App\Models\DailyThought;
use App\Services\MarkdownIngestionService;
use App\Services\AdjacencyEngine;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

/**
 * ReadController - PHASE 2-5: SILENCE-FIRST READING
 * 
 * Provides quiet browsing of notes, quotes, and thoughts.
 * No feeds, dashboards, or metrics - just content.
 */
class ReadController extends Controller
{
    protected MarkdownIngestionService $ingestion;
    protected AdjacencyEngine $adjacency;

    public function __construct(
        MarkdownIngestionService $ingestion,
        AdjacencyEngine $adjacency
    ) {
        $this->ingestion = $ingestion;
        $this->adjacency = $adjacency;
    }

    /**
     * Show all content (notes, quotes, thoughts)
     */
    public function index()
    {
        // Read from markdown files (Phase 1)
        $notes = $this->ingestion->readVault();
        $quotes = $this->ingestion->readQuotes();
        $thoughts = $this->ingestion->readThoughts();

        // Combine and sort by date (most recent first)
        $items = collect(array_merge($notes, $quotes, $thoughts))
            ->sortByDesc('date')
            ->values()
            ->all();

        return view('read', compact('items'));
    }

    /**
     * Show adjacency clusters (Phase 4)
     */
    public function adjacent()
    {
        // Run adjacency engine (Phase 4)
        $result = $this->adjacency->run();
        
        $clusters = $result['clusters'] ?? [];

        return view('adjacent', compact('clusters'));
    }

    /**
     * Show single note/quote/thought
     */
    public function show(string $type, string $slug)
    {
        // Read from markdown storage
        $item = null;

        if ($type === 'notes') {
            $items = $this->ingestion->readVault();
        } elseif ($type === 'quotes') {
            $items = $this->ingestion->readQuotes();
        } elseif ($type === 'thoughts') {
            $items = $this->ingestion->readThoughts();
        } else {
            abort(404);
        }

        // Find by slug
        foreach ($items as $candidate) {
            if (($candidate['slug'] ?? '') === $slug) {
                $item = $candidate;
                break;
            }
        }

        if (!$item) {
            abort(404);
        }

        return view('show', compact('item', 'type'));
    }
}
