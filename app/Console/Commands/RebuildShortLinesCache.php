<?php

namespace App\Console\Commands;

use App\Models\ShortLine;
use Illuminate\Console\Command;

class RebuildShortLinesCache extends Command
{
    protected $signature = 'conversation:rebuild-cache';
    protected $description = 'Rebuild the short lines cache from existing notes';

    public function handle()
    {
        $this->info('Rebuilding short lines cache...');
        
        $count = ShortLine::rebuildCache();
        
        $this->info("✓ Rebuilt cache with {$count} lines");
        
        return 0;
    }
}
