<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\AssistantService;

class PilgrimReflect extends Command
{
    protected $signature = 'pilgrim:reflect {input?}';
    protected $description = 'Ritual: reflect (may surface a reference)';

    public function handle(AssistantService $assistant)
    {
        $input = $this->argument('input') ?? '';
        $response = $assistant->handle($input, 'reflect');
        if ($response) {
            $this->line($response);
        } else {
            $this->info('Silence.');
        }
        return 0;
    }
}
