<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Services\ReflectionBuilder;

class ReflectNote extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'pilgrim:reflect {slug}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate a reflection for a note slug and store as Markdown (once per day).';

    public function handle(ReflectionBuilder $builder)
    {
        $slug = $this->argument('slug');

        $note = Note::where('slug', $slug)->first();
        if (! $note) {
            $this->error('Note not found: ' . $slug);
            return 1;
        }

        $reflection = $builder->build($note, 3);
        $path = $builder->storeAsMarkdown($reflection, false);

        if ($path === null) {
            $this->info('Reflection for today already exists.');
            return 0;
        }

        $this->info('Reflection written: ' . $path);
        return 0;
    }
}
