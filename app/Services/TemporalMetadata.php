<?php

namespace App\Services;

use Carbon\Carbon;

class TemporalMetadata
{
    /**
     * Return a gentle human-readable temporal descriptor for a given date.
     * Examples: "earlier this week", "last month", "two winters ago", "a year ago"
     */
    public function describe(?\DateTimeInterface $dt): ?string
    {
        if (! $dt) return null;

        $now = Carbon::now();
        $date = Carbon::instance($dt);

        $diffDays = $date->diffInDays($now);
        $diffYears = $date->diffInYears($now);

        if ($diffDays === 0) {
            return 'today';
        }
        if ($diffDays <= 3) {
            return 'earlier this week';
        }
        if ($diffDays <= 14) {
            return 'earlier this month';
        }
        if ($diffDays <= 45) {
            return 'a few weeks ago';
        }
        if ($diffDays <= 120) {
            return 'last month';
        }
        if ($diffYears === 1) {
            return 'a year ago';
        }

        // seasonal phrasing for older items
        if ($diffYears <= 2) {
            return 'two winters ago';
        }
        if ($diffYears <= 5) {
            return $diffYears . ' years ago';
        }

        return 'long ago';
    }
}
