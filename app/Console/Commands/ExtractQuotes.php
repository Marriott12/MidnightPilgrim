<?php
namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Services\QuoteEngine;

class ExtractQuotes extends Command
{
    protected $signature = 'pilgrim:extract-quotes {noteSlug}';
    protected $description = 'Extract intentional quotes from a poem or note';

    public function handle(QuoteEngine $engine)
    {
        $note = Note::where('slug', $this->argument('noteSlug'))->first();

        if (!$note) {
            $this->error('Note not found.');
            return 1;
        }

        $quotes = $engine->extractFromNote($note);

        $this->info(count($quotes) . ' quote(s) extracted.');
        return 0;
    }
}
