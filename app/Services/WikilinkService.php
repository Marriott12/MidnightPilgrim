<?php

namespace App\Services;

use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class WikilinkService
{
    /**
     * Parse wikilinks [[note title]] and convert to HTML links
     */
    public function parseWikilinks(string $content): string
    {
        // Match [[note title]] pattern
        return preg_replace_callback('/\[\[([^\]]+)\]\]/', function ($matches) {
            $title = $matches[1];
            $slug = $this->findNoteByTitle($title);
            
            if ($slug) {
                return '<a href="/view/notes/' . $slug . '" class="wikilink">' . $title . '</a>';
            }
            
            // No matching note found - render as plain text with indicator
            return '<span class="wikilink-missing" title="Note not found">' . $title . '</span>';
        }, $content);
    }
    
    /**
     * Find note slug by title
     */
    protected function findNoteByTitle(string $title): ?string
    {
        $vaultFiles = Storage::files('vault');
        
        foreach ($vaultFiles as $file) {
            $content = Storage::get($file);
            
            // Parse frontmatter
            if (preg_match('/^---\s*\n(.*?)\n---/s', $content, $matches)) {
                $frontmatter = $matches[1];
                
                if (preg_match('/^title:\s*(.+)$/m', $frontmatter, $titleMatch)) {
                    $noteTitle = trim($titleMatch[1]);
                    
                    if (strcasecmp($noteTitle, $title) === 0) {
                        return basename($file, '.md');
                    }
                }
            }
        }
        
        return null;
    }
    
    /**
     * Find all notes that link to the given note title
     */
    public function findBacklinks(string $noteTitle): array
    {
        $backlinks = [];
        $vaultFiles = Storage::files('vault');
        
        foreach ($vaultFiles as $file) {
            $content = Storage::get($file);
            
            // Check if this note contains a wikilink to our note
            if (preg_match_all('/\[\[([^\]]+)\]\]/', $content, $matches)) {
                foreach ($matches[1] as $linkedTitle) {
                    if (strcasecmp($linkedTitle, $noteTitle) === 0) {
                        // Extract note info
                        $slug = basename($file, '.md');
                        $title = $slug;
                        
                        // Try to get actual title from frontmatter
                        if (preg_match('/^---\s*\n(.*?)\n---/s', $content, $fmMatches)) {
                            $frontmatter = $fmMatches[1];
                            if (preg_match('/^title:\s*(.+)$/m', $frontmatter, $titleMatch)) {
                                $title = trim($titleMatch[1]);
                            }
                        }
                        
                        $backlinks[] = [
                            'slug' => $slug,
                            'title' => $title,
                        ];
                        break; // Only add each note once
                    }
                }
            }
        }
        
        return $backlinks;
    }
}
