<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

/**
 * PoemRevision Model - REVISION TRACKING
 * 
 * Tracks every version of a poem to verify structured revision.
 */
class PoemRevision extends Model
{
    protected $fillable = [
        'poem_id',
        'version_number',
        'content',
        'changes_made',
        'revision_type',
    ];

    protected $casts = [
        'version_number' => 'integer',
    ];

    public function poem(): BelongsTo
    {
        return $this->belongsTo(Poem::class);
    }

    /**
     * Calculate diff percentage from previous version
     */
    public function calculateChangedPercentage(): float
    {
        $previous = PoemRevision::where('poem_id', $this->poem_id)
            ->where('version_number', $this->version_number - 1)
            ->first();

        if (!$previous) {
            return 0.0;
        }

        similar_text($previous->content, $this->content, $percent);
        return 100 - $percent;
    }
}
