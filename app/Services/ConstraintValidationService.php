<?php

namespace App\Services;

/**
 * ConstraintValidationService - CONSTRAINT ENFORCEMENT
 * 
 * Actually validates poems against weekly constraints.
 * Returns violations with specific line numbers.
 */
class ConstraintValidationService
{
    /**
     * Validate poem against constraint
     */
    public function validate(string $content, string $constraintType): array
    {
        return match($constraintType) {
            'concrete_imagery' => $this->validateConcreteImagery($content),
            'no_metaphors' => $this->validateNoMetaphors($content),
            'sustained_metaphor' => $this->validateSustainedMetaphor($content),
            'second_person' => $this->validateSecondPerson($content),
            default => [],
        };
    }

    /**
     * Validate: Concrete imagery only
     */
    private function validateConcreteImagery(string $content): array
    {
        $violations = [];
        $lines = explode("\n", $content);
        
        // Abstract words to flag
        $abstractWords = [
            'meaning', 'purpose', 'existence', 'reality', 'truth', 'essence',
            'nature', 'being', 'consciousness', 'soul', 'spirit', 'journey',
            'destiny', 'fate', 'universe', 'energy', 'vibration', 'feeling',
            'emotion', 'thought', 'idea', 'concept', 'notion', 'sense',
            'belief', 'hope', 'fear', 'love', 'hate', 'desire', 'dream'
        ];

        foreach ($lines as $lineNum => $line) {
            $line = strtolower(trim($line));
            if (empty($line)) continue;

            foreach ($abstractWords as $word) {
                if (preg_match('/\b' . preg_quote($word, '/') . '\b/', $line)) {
                    $violations[] = [
                        'line' => $lineNum + 1,
                        'content' => trim($lines[$lineNum]),
                        'issue' => "Contains abstract word: '{$word}'",
                        'severity' => 'high'
                    ];
                    break; // One violation per line
                }
            }
        }

        return $violations;
    }

    /**
     * Validate: No metaphors
     */
    private function validateNoMetaphors(string $content): array
    {
        $violations = [];
        $lines = explode("\n", $content);

        // Metaphor indicators
        $metaphorPatterns = [
            '/\bis\s+(?:a|an|the)\s+(?!physical|actual|real)/i',
            '/\blike\s+(?:a|an|the)/i',
            '/\bas\s+(?:if|though)/i',
            '/\bas\s+(?:a|an|the)\s+(?!example|instance)/i',
            '/\bbecomes?\s+(?:a|an|the)/i',
            '/\bturns?\s+into/i',
        ];

        foreach ($lines as $lineNum => $line) {
            if (empty(trim($line))) continue;

            foreach ($metaphorPatterns as $pattern) {
                if (preg_match($pattern, $line)) {
                    $violations[] = [
                        'line' => $lineNum + 1,
                        'content' => trim($line),
                        'issue' => 'Possible metaphorical construction detected',
                        'severity' => 'high'
                    ];
                    break;
                }
            }
        }

        return $violations;
    }

    /**
     * Validate: Sustained central metaphor
     */
    private function validateSustainedMetaphor(string $content): array
    {
        $violations = [];
        $lines = explode("\n", $content);
        $nonEmptyLines = array_filter($lines, fn($l) => !empty(trim($l)));

        if (count($nonEmptyLines) < 3) {
            return [[
                'line' => 0,
                'content' => 'Entire poem',
                'issue' => 'Poem too short to establish sustained metaphor',
                'severity' => 'critical'
            ]];
        }

        // This is a simplified check - full implementation would need NLP
        // Check for metaphor establishment in first third
        $firstThird = array_slice($nonEmptyLines, 0, max(1, count($nonEmptyLines) / 3));
        $hasMetaphorStart = false;

        foreach ($firstThird as $line) {
            if (preg_match('/\b(is|like|as|becomes?)\s+/', $line)) {
                $hasMetaphorStart = true;
                break;
            }
        }

        if (!$hasMetaphorStart) {
            $violations[] = [
                'line' => 1,
                'content' => 'First section',
                'issue' => 'No clear metaphor established in opening',
                'severity' => 'high'
            ];
        }

        return $violations;
    }

    /**
     * Validate: Second person POV
     */
    private function validateSecondPerson(string $content): array
    {
        $violations = [];
        $lines = explode("\n", $content);

        // First person pronouns
        $firstPersonPatterns = [
            '/\bI\b/',
            '/\bme\b/',
            '/\bmy\b/',
            '/\bmine\b/',
            '/\bmyself\b/',
            '/\bwe\b/',
            '/\bus\b/',
            '/\bour\b/',
            '/\bours\b/',
            '/\bourselves\b/',
        ];

        foreach ($lines as $lineNum => $line) {
            if (empty(trim($line))) continue;

            foreach ($firstPersonPatterns as $pattern) {
                if (preg_match($pattern . '/i', $line)) {
                    $violations[] = [
                        'line' => $lineNum + 1,
                        'content' => trim($line),
                        'issue' => 'Contains first-person pronoun (must be second-person only)',
                        'severity' => 'critical'
                    ];
                    break;
                }
            }
        }

        // Check if "you" appears at all
        $hasSecondPerson = false;
        foreach ($lines as $line) {
            if (preg_match('/\byou\b/i', $line)) {
                $hasSecondPerson = true;
                break;
            }
        }

        if (!$hasSecondPerson) {
            $violations[] = [
                'line' => 0,
                'content' => 'Entire poem',
                'issue' => 'No second-person pronouns found (must address "you")',
                'severity' => 'critical'
            ];
        }

        return $violations;
    }

    /**
     * Check if violations are critical (should reject submission)
     */
    public function hasCriticalViolations(array $violations): bool
    {
        foreach ($violations as $violation) {
            if ($violation['severity'] === 'critical') {
                return true;
            }
        }

        return count($violations) >= 3; // 3+ violations = reject
    }

    /**
     * Format violations for display
     */
    public function formatViolations(array $violations): string
    {
        if (empty($violations)) {
            return 'No violations detected.';
        }

        $formatted = "CONSTRAINT VIOLATIONS:\n\n";
        
        foreach ($violations as $v) {
            $formatted .= "Line {$v['line']}: {$v['issue']}\n";
            $formatted .= "  \"{$v['content']}\"\n\n";
        }

        return $formatted;
    }
}
