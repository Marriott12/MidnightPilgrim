<?php

namespace App\Services;

use Illuminate\Http\Request;

class RhythmResolver
{
    // Night hours considered vigil by default (inclusive)
    protected array $vigilHours = [22, 23, 0, 1, 2, 3, 4, 5];

    /**
     * Determine a suggested rhythm for given input/request.
     * Returns 'vigil' or 'pulse'. This is a suggestion only and must not
     * change behavior without explicit user consent.
     *
     * @param string|null $input
     * @param bool $explicitRequested
     * @param Request|null $request
     * @return string
     */
    public function determine(?string $input = null, bool $explicitRequested = false, ?Request $request = null): string
    {
        // explicit intent phrases override heuristics
        if ($input) {
            $lower = mb_strtolower($input);
            $explicitPulsePhrases = ['just checking in', 'quick check', 'quickly', 'just a check', 'checking in'];
            foreach ($explicitPulsePhrases as $p) {
                if (str_contains($lower, $p)) {
                    return 'pulse';
                }
            }
            $explicitVigilPhrases = ['late', 'long-form', 'long form', 'poem', 'poetry', 'reflect'];
            foreach ($explicitVigilPhrases as $p) {
                if (str_contains($lower, $p)) {
                    return 'vigil';
                }
            }
        }

        // request time check
        $hour = null;
        if ($request && method_exists($request, 'hour')) {
            try { $hour = (int) $request->hour(); } catch (\Throwable $e) { $hour = null; }
        }
        if ($hour === null) {
            $hour = (int) date('G');
        }

        if (in_array($hour, $this->vigilHours, true)) {
            return 'vigil';
        }

        // long-form input -> vigil
        if ($input !== null && mb_strlen(trim($input)) > 250) {
            return 'vigil';
        }

        // default to pulse for short inputs during day
        return 'pulse';
    }

    /**
     * Convenience: return true when suggested rhythm is pulse
     */
    public function isPulse(?string $input = null, bool $explicitRequested = false, ?Request $request = null): bool
    {
        return $this->determine($input, $explicitRequested, $request) === 'pulse';
    }
}
