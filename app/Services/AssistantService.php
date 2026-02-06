<?php

namespace App\Services;

use App\Models\Interaction;
use App\Models\Quote;
use App\Models\DailyThought;
use App\Policies\SilencePolicy;
use App\Services\MentalHealthCompanionService;
use App\Services\ReferenceResolver;

class AssistantService
{
    protected SilencePolicy $policy;
    protected MentalHealthCompanionService $mh;
    protected ReferenceResolver $resolver;

    public function __construct()
    {
        $this->policy = app(SilencePolicy::class);
        $this->mh = app(MentalHealthCompanionService::class);
        $this->resolver = app(ReferenceResolver::class);
    }

    /**
     * Handle user input and persist an Interaction.
     * Returns a short response string or null (silence treated as success).
     *
     * @param string $input
     * @param string $mode
     * @return string|null
     */
    public function handle(string $input, string $mode = 'listen'): ?string
    {
        // Hard constraints (do not remove):
        // - Silence-first: prefer returning null (silence) over an assistant-generated response.
        // - One-surface-per-interaction: at most one existing reference may be surfaced.
        // - Private memories must never be surfaced implicitly.
        // - No automatic escalation: do not call external helpers without explicit user consent.

        // Fail-closed: any exception in resolution or MH companion should result in silence (null).
        // These constraints help keep the experience calm and predictable.

        $mode = $this->normalizeMode($mode);

        // Pause behavior: explicit pause or empty input -> store and return silence
        $trim = trim($input);
        if ($trim === '' || in_array(strtolower($trim), ['pause', 'enough'], true)) {
            try {
                Interaction::create([
                    'input_text' => $input,
                    'response_text' => null,
                    'mode' => $mode,
                ]);
            } catch (\Throwable $e) {
                // fail-closed: ignore persistence errors and remain silent
            }

            return null;
        }
        // Always store the user input (silence-first default)
        $interaction = null;
        try {
            $interaction = Interaction::create([
                'input_text' => $input,
                'response_text' => null,
                'mode' => $mode,
            ]);
        } catch (\Throwable $e) {
            // fail-closed: if persistence fails, proceed without persisting
            $interaction = null;
        }

        // Attempt to resolve a reference first (do not include temporal by default)
        $includeTemporal = false;
        if (! $this->policy->recommendSilence($mode) && $this->policy->allowTemporalToday()) {
            $includeTemporal = true;
        }

        try {
            $reference = $this->resolver->resolve($input, false, $includeTemporal);

            // If emotional language is present, allow mental-health companion to handle
            if ($this->mh->detectEmotionalLanguage($input)) {
                // MH companion must not escalate — it may return a single, non-directive sentence.
                $mhResponse = $this->mh->respondToInputWithReferencePriority($input);
                $response = $mhResponse;
            } elseif ($reference) {
            // Check silence policy and resurfacing rules before returning
            $slug = $reference['slug'] ?? null;
            if ($slug && $this->policy->allowSurfaceReference($slug)) {
                $excerpt = $reference['excerpt'];
                $response = 'From "' . $slug . '": "' . str_replace("\n", ' ', $excerpt) . '"';
                // attach optional temporal phrase if resolver supplied and allowed
                if (isset($reference['temporal']) && $reference['temporal']) {
                    $response .= ' — You wrote this ' . $reference['temporal'] . '.';
                }
            } else {
                $response = null;
            }
            } else {
                $response = null; // silence is the default
            }
        } catch (\Throwable $e) {
            // Silence on error; do not surface internal errors to users.
            $response = null;
        }

        // persist interaction with response (which may be null)
        if ($interaction) {
            try {
                $interaction->response_text = $response;
                $interaction->save();
            } catch (\Throwable $e) {
                // ignore persistence errors
            }
        }

        return $response;
    }

    protected function normalizeMode(string $mode): string
    {
        $mode = strtolower(trim($mode));
        if (in_array($mode, ['listen', 'reflect', 'ask'], true)) {
            return $mode;
        }

        return 'listen';
    }

    protected function reflectOne(): string
    {
        $quote = Quote::inRandomOrder()->first();
        if ($quote) {
            $text = $this->extractTextFromModel($quote);
            if (!empty($text)) {
                return $this->truncateTwoSentences($text);
            }
        }

        $thought = DailyThought::latest()->first();
        if ($thought) {
            $text = $this->extractTextFromModel($thought);
            if (!empty($text)) {
                return $this->truncateTwoSentences($text);
            }
        }

        return '[no quote or thought available]';
    }

    protected function askClarifyingQuestion(string $input): string
    {
        $sample = trim($input);
        if (strlen($sample) > 60) {
            $sample = substr($sample, 0, 57) . '...';
        }

        return "Could you clarify what you mean by '" . addslashes($sample) . "'?";
    }

    protected function extractTextFromModel($model): ?string
    {
        foreach (['text', 'body', 'content', 'quote', 'description'] as $attr) {
            if (isset($model->{$attr}) && is_string($model->{$attr}) && $model->{$attr} !== '') {
                return $model->{$attr};
            }
        }

        return null;
    }

    protected function truncateTwoSentences(string $text): string
    {
        $sentences = preg_split('/(?<=[.!?])\\s+/', trim($text));
        if (!$sentences) {
            return trim($text);
        }

        $firstTwo = array_slice($sentences, 0, 2);
        $result = implode(' ', $firstTwo);

        return trim($result);
    }
}

