<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ReferenceResolver;

class PilgrimRemember extends Command
{
    protected $signature = 'pilgrim:remember {query}';
    protected $description = 'Ritual: remember (attempt to find an explicit reference by slug/title)';

    public function handle(ReferenceResolver $resolver)
    {
        $q = $this->argument('query');
        $ref = $resolver->resolve($q, true, true);
        if ($ref) {
            $this->line($ref['excerpt']);
            if (isset($ref['temporal'])) {
                $this->line('You wrote this ' . $ref['temporal'] . '.');
            }
        } else {
            $this->info('No matching reference found.');
        }
        return 0;
    }
}
