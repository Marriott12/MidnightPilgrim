<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\DailyThoughtEngine;

class GenerateDailyThought extends Command
{
    protected $signature = 'pilgrim:generate-daily-thought';
    protected $description = 'Generate and store a daily thought.';

    public function handle(DailyThoughtEngine $engine)
    {
        $thought = $engine->generate();

        if ($thought) {
            $this->info('Daily thought generated.');
            return 0;
        }

        $this->error('No thought generated.');
        return 1;
    }
}
