<?php

namespace App\Services;

use Illuminate\Support\Str;

class AdjacencyEngine
{
    protected array $sources = [];

    public function __construct()
    {
        // default data locations (markdown-first)
        $this->sources = [
            'notes' => storage_path('app/vault'),
            'quotes' => storage_path('app/quotes'),
            'thoughts' => storage_path('app/thoughts'),
        ];
    }

    /**
     * Scan files and return recurring terms grouped with references.
     * This service is read-only and never writes to content.
     * Output: ['clusters' => [ ['term'=>'night','count'=>4,'references'=>[...]], ... ]]
     */
    public function run(int $minOccurrences = 2, array $includeTypes = ['notes','quotes','thoughts']): array
    {
        // Try cache first
        $cache = $this->readCache();
        if ($cache !== null) {
            return $cache;
        }

        $counts = [];
        $refs = [];

        $stop = $this->stopwords();

        foreach ($includeTypes as $type) {
            if (empty($this->sources[$type]) || ! is_dir($this->sources[$type])) {
                continue;
            }

            $dir = $this->sources[$type];
            $files = glob($dir . '/*.md');
            foreach ($files as $f) {
                try {
                    $text = file_get_contents($f);
                } catch (\Throwable $e) { continue; }

                $terms = $this->extractTerms($text, $stop);
                $unique = array_unique($terms);
                foreach ($unique as $t) {
                    $counts[$t] = ($counts[$t] ?? 0) + 1;
                    $refs[$t][] = [
                        'type' => rtrim($type, 's'),
                        'path' => $f,
                        'slug' => basename($f, '.md'),
                        'excerpt' => $this->excerptForTerm($text, $t),
                        'date' => $this->extractDate($f),
                    ];
                }
            }
        }

        // collect terms meeting minOccurrences
        $clusters = [];
        foreach ($counts as $term => $c) {
            if ($c >= $minOccurrences) {
                $clusters[] = [
                    'term' => $term,
                    'count' => $c,
                    'references' => $refs[$term] ?? [],
                ];
            }
        }

        // sort by count desc then term
        usort($clusters, function($a,$b){
            if ($a['count'] === $b['count']) return strcmp($a['term'],$b['term']);
            return $b['count'] - $a['count'];
        });

        $result = ['clusters' => $clusters];

        // write cache
        $this->writeCache($result);

        return $result;
    }

    protected function cachePath(): string
    {
        return storage_path('app/cache/adjacency.json');
    }

    protected function cacheTtlSeconds(): int
    {
        return 60 * 60; // 1 hour
    }

    protected function readCache(): ?array
    {
        $path = $this->cachePath();
        if (! file_exists($path)) return null;
        try {
            $data = json_decode(file_get_contents($path), true);
            if (! is_array($data)) return null;
            $metaPath = $path . '.meta';
            if (! file_exists($metaPath)) return null;
            $meta = json_decode(file_get_contents($metaPath), true);
            if (! isset($meta['ts'])) return null;
            if (time() - $meta['ts'] > $this->cacheTtlSeconds()) return null;
            return $data;
        } catch (\Throwable $e) {
            return null;
        }
    }

    protected function writeCache(array $out): void
    {
        $dir = dirname($this->cachePath());
        if (! is_dir($dir)) @mkdir($dir, 0777, true);
        try {
            file_put_contents($this->cachePath(), json_encode($out));
            file_put_contents($this->cachePath() . '.meta', json_encode(['ts' => time()]));
        } catch (\Throwable $e) {
            // fail quietly
        }
    }

    protected function extractTerms(string $text, array $stop): array
    {
        $lower = Str::lower(strip_tags($text));
        // split into words and 2-word phrases
        $words = preg_split('/[^\p{L}\p{N}]+/u', $lower);
        $words = array_filter($words, fn($w) => $w !== '' && mb_strlen($w) >= 3 && ! in_array($w, $stop));
        $out = [];
        $len = count($words);
        for ($i=0;$i<$len;$i++) {
            $out[] = $words[$i];
            if ($i+1 < $len) {
                $bigram = $words[$i] . ' ' . $words[$i+1];
                if (! in_array($bigram, $stop)) $out[] = $bigram;
            }
        }
        return $out;
    }

    protected function excerptForTerm(string $text, string $term): string
    {
        $lines = preg_split('/\r?\n/', $text);
        foreach ($lines as $line) {
            if (Str::contains(Str::lower($line), $term)) {
                return trim($line);
            }
        }
        return substr(trim(strip_tags($text)), 0, 120);
    }

    /**
     * Extract date from filename (Phase 4)
     * 
     * @param string $filepath
     * @return string
     */
    protected function extractDate(string $filepath): string
    {
        $basename = basename($filepath, '.md');
        
        // Try to extract YYYY-MM-DD from filename
        if (preg_match('/(\d{4}-\d{2}-\d{2})/', $basename, $matches)) {
            return $matches[1];
        }
        
        // Fallback to file modification time
        return date('Y-m-d', filemtime($filepath));
    }

    protected function stopwords(): array
    {
        return [
            'the','and','you','that','with','this','from','have','not','for','was','but','are','his','her','she','him','they','their','will','one','all','about','what','when','where'
        ];
    }
}
