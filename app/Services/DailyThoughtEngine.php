<?php

namespace App\Services;

use App\Models\DailyThought;
use App\Policies\SilencePolicy;

class DailyThoughtEngine
{
    /**
     * Generate a daily thought placeholder.
     *
     * @return DailyThought|null
     */
    public function generate(): ?DailyThought
    {
        $policy = app(SilencePolicy::class);
        if (! $policy->allowDailyThought()) {
            return null;
        }
        $thought = new DailyThought();
        $thought->title = 'Daily Thought';
        $thought->slug = 'daily-thought-' . now()->format('Ymd');
        $thought->body = '';
        $thought->mood = null;
        $thought->date_generated = now();

        // Not saving automatically; caller may persist.
        return $thought;
    }
}
