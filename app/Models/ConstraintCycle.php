<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * ConstraintCycle Model - ARTISTIC GROWTH ENGINE
 * 
 * Manages the rotating weekly constraints for poetry development.
 * Week 1: Concrete imagery only
 * Week 2: No metaphors
 * Week 3: One sustained central metaphor
 * Week 4: Second person POV
 */
class ConstraintCycle extends Model
{
    protected $fillable = [
        'user_profile_id',
        'discipline_contract_id',
        'week_number',
        'constraint_type',
        'constraint_description',
        'completed',
    ];

    protected $casts = [
        'week_number' => 'integer',
        'completed' => 'boolean',
    ];

    public function userProfile(): BelongsTo
    {
        return $this->belongsTo(UserProfile::class);
    }

    public function disciplineContract(): BelongsTo
    {
        return $this->belongsTo(DisciplineContract::class);
    }

    /**
     * Get the constraint type for a given week number
     */
    public static function getConstraintForWeek(int $weekNumber): array
    {
        $cycle = ($weekNumber - 1) % 4;

        return match($cycle) {
            0 => [
                'type' => 'concrete_imagery',
                'description' => 'Concrete imagery only. No abstractions. Every line must contain physical, sensory details.',
            ],
            1 => [
                'type' => 'no_metaphors',
                'description' => 'No metaphors allowed. Direct language only. Say exactly what you mean.',
            ],
            2 => [
                'type' => 'sustained_metaphor',
                'description' => 'One sustained central metaphor throughout the entire poem. Develop it fully.',
            ],
            3 => [
                'type' => 'second_person',
                'description' => 'Second person POV only. Address "you" throughout. No first person.',
            ],
        };
    }

    /**
     * Mark constraint as completed
     */
    public function complete(): void
    {
        $this->completed = true;
        $this->save();
    }
}
