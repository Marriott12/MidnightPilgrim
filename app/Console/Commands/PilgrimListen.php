<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AssistantService;

class PilgrimListen extends Command
{
    protected $signature = 'pilgrim:listen {input?}';
    protected $description = 'Ritual: listen (stores input, respects silence)';

    public function handle(AssistantService $assistant)
    {
        $input = $this->argument('input') ?? '';
        $response = $assistant->handle($input, 'listen');
        $this->info('Done.');
        return 0;
    }
}
