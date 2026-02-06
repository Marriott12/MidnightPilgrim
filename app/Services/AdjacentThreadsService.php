<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

/**
 * AdjacentThreadsService - COVENANT-COMPLIANT PROXIMITY DETECTION
 * 
 * PURPOSE: Find nearby writings based on shared words and temporal proximity
 * 
 * CAPABILITIES:
 * - Find notes written near in time
 * - Find notes sharing significant words (excluding stopwords)
 * - Lightweight scoring (no AI, no sentiment)
 * - Exclude mental health content (isolation enforced)
 * 
 * COVENANT COMPLIANCE:
 * - Deterministic: Same input = same output
 * - Explainable: "Written X days apart, share Y words"
 * - No engagement optimization
 * - Mental health isolation enforced
 */
class AdjacentThreadsService
{
    /**
     * Find notes adjacent to a given note
     * 
     * @param string $slug The note to find adjacents for
     * @param int $limit Max number of adjacent notes
     * @return array Array of adjacent notes with scores
     */
    public function findAdjacent(string $slug, int $limit = 5): array
    {
        $vaultPath = storage_path('app/vault');
        $targetFile = $vaultPath . '/' . $slug . '.md';
        
        if (!file_exists($targetFile)) {
            return [];
        }
        
        $targetContent = file_get_contents($targetFile);
        $targetTime = filemtime($targetFile);
        
        // Skip if mental health entry
        if ($this->isMentalHealthEntry($targetContent)) {
            return [];
        }
        
        $targetWords = $this->extractSignificantWords($targetContent);
        
        $candidates = [];
        $files = glob($vaultPath . '/*.md');
        
        foreach ($files as $file) {
            $candidateSlug = basename($file, '.md');
            
            // Skip self
            if ($candidateSlug === $slug) {
                continue;
            }
            
            $content = file_get_contents($file);
            
            // Skip mental health entries
            if ($this->isMentalHealthEntry($content)) {
                continue;
            }
            
            $time = filemtime($file);
            $words = $this->extractSignificantWords($content);
            
            // Calculate scores
            $sharedWords = array_intersect($targetWords, $words);
            $sharedCount = count($sharedWords);
            
            // Time proximity (days apart)
            $daysDiff = abs(($time - $targetTime) / 86400);
            $timeScore = max(0, 10 - $daysDiff); // Closer in time = higher score
            
            // Word similarity
            $wordScore = $sharedCount;
            
            // Combined score (favor word similarity)
            $score = ($wordScore * 3) + $timeScore;
            
            if ($score > 0) {
                // Extract title
                preg_match('/^#\s+(.+)$/m', $content, $matches);
                $title = $matches[1] ?? ucfirst(str_replace('-', ' ', $candidateSlug));
                
                $candidates[] = [
                    'slug' => $candidateSlug,
                    'title' => $title,
                    'score' => $score,
                    'shared_words' => array_values($sharedWords),
                    'days_apart' => round($daysDiff, 1),
                    'date' => date('F j', $time)
                ];
            }
        }
        
        // Sort by score descending
        usort($candidates, fn($a, $b) => $b['score'] - $a['score']);
        
        return array_slice($candidates, 0, $limit);
    }
    
    /**
     * Extract significant words (exclude stopwords, markdown syntax)
     */
    protected function extractSignificantWords(string $content): array
    {
        // Remove frontmatter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        
        // Remove headers
        $content = preg_replace('/^#+\s+/m', '', $content);
        
        // Convert to lowercase, strip tags
        $text = Str::lower(strip_tags($content));
        
        // Split into words
        $words = preg_split('/[^\p{L}\p{N}]+/u', $text, -1, PREG_SPLIT_NO_EMPTY);
        
        // Filter: min 4 chars, not stopword
        $stopwords = $this->stopwords();
        $significant = [];
        
        foreach ($words as $word) {
            if (mb_strlen($word) >= 4 && !in_array($word, $stopwords)) {
                $significant[] = $word;
            }
        }
        
        return array_unique($significant);
    }
    
    /**
     * Check if content is mental health entry
     */
    protected function isMentalHealthEntry(string $content): bool
    {
        $markers = [
            'type: sit',
            'type: check-in',
            'visibility: private-health',
            'mental_health: true'
        ];
        
        foreach ($markers as $marker) {
            if (stripos($content, $marker) !== false) {
                return true;
            }
        }
        
        return false;
    }
    
    /**
     * Common stopwords to exclude
     */
    protected function stopwords(): array
    {
        return [
            'the', 'be', 'to', 'of', 'and', 'a', 'in', 'that', 'have', 'i',
            'it', 'for', 'not', 'on', 'with', 'he', 'as', 'you', 'do', 'at',
            'this', 'but', 'his', 'by', 'from', 'they', 'we', 'say', 'her', 'she',
            'or', 'an', 'will', 'my', 'one', 'all', 'would', 'there', 'their',
            'what', 'so', 'up', 'out', 'if', 'about', 'who', 'get', 'which',
            'when', 'make', 'can', 'like', 'time', 'just', 'him', 'know', 'take',
            'people', 'into', 'year', 'your', 'good', 'some', 'could', 'them',
            'see', 'other', 'than', 'then', 'now', 'look', 'only', 'come', 'its',
            'over', 'think', 'also', 'back', 'after', 'use', 'two', 'how', 'our',
            'work', 'first', 'well', 'way', 'even', 'new', 'want', 'because', 'any',
            'these', 'give', 'day', 'most', 'us', 'is', 'was', 'are', 'been', 'has',
            'had', 'were', 'said', 'did', 'having', 'may', 'should', 'could', 'would'
        ];
    }
}
