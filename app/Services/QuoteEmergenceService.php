<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;

/**
 * QuoteEmergenceService - COVENANT-COMPLIANT REPETITION DETECTION
 * 
 * PURPOSE: Detect sentences that repeat across writings as "emergent quotes"
 * 
 * CAPABILITIES:
 * - Find sentences that appear in multiple notes
 * - Exact match only (no fuzzy matching or interpretation)
 * - Exclude mental health/sit entries (isolation enforced)
 * - No AI, no sentiment, no scoring
 * 
 * COVENANT COMPLIANCE:
 * - Deterministic: Same input always produces same output
 * - Explainable: Results are literal repetitions
 * - No engagement optimization or persuasion
 * - Mental health content isolation enforced
 */
class QuoteEmergenceService
{
    /**
     * Find sentences that appear in multiple notes
     * 
     * @param int $minOccurrences Minimum times sentence must appear
     * @return array Array of [sentence, count, sources]
     */
    public function findRepeatedSentences(int $minOccurrences = 2): array
    {
        $vaultPath = storage_path('app/vault');
        
        if (!is_dir($vaultPath)) {
            return [];
        }
        
        $sentences = [];
        $sources = [];
        
        // Scan all markdown files in vault
        $files = glob($vaultPath . '/*.md');
        
        foreach ($files as $file) {
            $slug = basename($file, '.md');
            $content = file_get_contents($file);
            
            // Skip mental health/sit entries (enforce isolation)
            if ($this->isMentalHealthEntry($content)) {
                continue;
            }
            
            // Extract sentences (simple split by . ! ?)
            $extracted = $this->extractSentences($content);
            
            foreach ($extracted as $sentence) {
                $normalized = $this->normalizeSentence($sentence);
                
                if (!isset($sentences[$normalized])) {
                    $sentences[$normalized] = 0;
                    $sources[$normalized] = [];
                }
                
                $sentences[$normalized]++;
                $sources[$normalized][] = [
                    'slug' => $slug,
                    'original' => $sentence
                ];
            }
        }
        
        // Filter to only repeated sentences
        $repeated = [];
        
        foreach ($sentences as $sentence => $count) {
            if ($count >= $minOccurrences) {
                $repeated[] = [
                    'sentence' => $sources[$sentence][0]['original'], // Use first occurrence
                    'count' => $count,
                    'sources' => array_map(fn($s) => $s['slug'], $sources[$sentence])
                ];
            }
        }
        
        // Sort by occurrence count (descending)
        usort($repeated, fn($a, $b) => $b['count'] - $a['count']);
        
        return $repeated;
    }
    
    /**
     * Extract sentences from markdown content
     */
    protected function extractSentences(string $content): array
    {
        // Remove frontmatter
        $content = preg_replace('/^---\s*\n.*?\n---\s*\n/s', '', $content);
        
        // Remove markdown headers
        $content = preg_replace('/^#+\s+.+$/m', '', $content);
        
        // Split by sentence-ending punctuation
        $sentences = preg_split('/[.!?]+\s+/', $content, -1, PREG_SPLIT_NO_EMPTY);
        
        $valid = [];
        
        foreach ($sentences as $sentence) {
            $sentence = trim(strip_tags($sentence));
            
            // Skip very short or very long sentences
            $wordCount = str_word_count($sentence);
            if ($wordCount >= 6 && $wordCount <= 40) {
                $valid[] = $sentence;
            }
        }
        
        return $valid;
    }
    
    /**
     * Normalize sentence for comparison (case-insensitive, trim whitespace)
     */
    protected function normalizeSentence(string $sentence): string
    {
        return strtolower(trim($sentence));
    }
    
    /**
     * Check if content is mental health/sit entry (enforce isolation)
     */
    protected function isMentalHealthEntry(string $content): bool
    {
        // Check for mental health markers in frontmatter or content
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
}
