<?php

namespace App\Services;

use App\Models\Note;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;

class NoteService
{
    public function create(array $data, string $content): Note
    {
        $slug = Str::slug($data['title']);
        $date = now()->format('Y-m-d');

        $path = "vault/{$date}-{$slug}.md";

        $markdown = $this->buildMarkdown($data, $content);

        Storage::disk('local')->put($path, $markdown);

        return Note::create([
            'title' => $data['title'],
            'slug'  => $slug,
            'type'  => $data['type'] ?? 'idea',
            'mood'  => $data['mood'] ?? null,
            'tags'  => $data['tags'] ?? null,
            'path'  => $path,
        ]);
    }

    protected function buildMarkdown(array $data, string $content): string
    {
        $frontMatter = [
            'title' => $data['title'],
            'type'  => $data['type'] ?? 'idea',
            'mood'  => $data['mood'] ?? null,
            'tags'  => $data['tags'] ?? null,
            'date'  => now()->toIso8601String(),
        ];

        $yaml = "---\n";
        foreach ($frontMatter as $key => $value) {
            if ($value !== null) {
                $yaml .= "{$key}: {$value}\n";
            }
        }
        $yaml .= "---\n\n";

        return $yaml . $content . "\n";
    }
}
