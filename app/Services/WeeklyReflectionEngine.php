<?php

namespace App\Services;

use App\Models\CheckIn;
use App\Models\Quote;
use App\Models\Note;
use Carbon\Carbon;

class WeeklyReflectionEngine
{
    /**
     * Produce one gentle observation for the past week's check-ins/notes/quotes.
     * This is intentionally non-judgmental and not analytical.
     */
    public function surfaceWeeklyReflection(): string
    {
        $start = Carbon::now()->subDays(7)->startOfDay();

        $commonMood = CheckIn::where('created_at', '>=', $start)
            ->selectRaw('mood, count(*) as cnt')
            ->groupBy('mood')
            ->orderByDesc('cnt')
            ->first();

        if ($commonMood && $commonMood->mood) {
            return "This week carries notes of " . $commonMood->mood . ".";
        }

        // Fallback gentle observation
        return "This week has offered moments of quiet reflection.";
    }
}
