<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\Note;
use App\Models\Quote;
use App\Models\DailyThought;

class PilgrimMemoryAudit extends Command
{
    protected $signature = 'pilgrim:memory-audit';
    protected $description = 'List counts of private vs reflective vs shareable items (no content)';

    public function handle()
    {
        $models = [
            'notes' => Note::class,
            'quotes' => Quote::class,
            'daily_thoughts' => DailyThought::class,
        ];

        foreach ($models as $label => $class) {
            try {
                $total = $class::count();
                $private = $class::where('visibility', 'private')->count();
                $reflective = $class::where('visibility', 'reflective')->count();
                $shareable = $class::where('visibility', 'shareable')->count();
            } catch (\Throwable $e) {
                $this->line(ucfirst(str_replace('_', ' ', $label)) . ': (unavailable)');
                continue;
            }

            $this->line(ucfirst(str_replace('_', ' ', $label)) . ': total=' . $total . ' private=' . $private . ' reflective=' . $reflective . ' shareable=' . $shareable);
        }

        return 0;
    }
}
