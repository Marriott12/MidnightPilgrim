<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Quote extends Model
{
    protected $fillable = [
        'slug',
        'source_note_id',
        'path',
        'confidence', // low | medium | high
        'visibility', // private | reflective | shareable
    ];

    protected $attributes = [
        'visibility' => 'private',
    ];

    public function canBeShared(): bool
    {
        return true;
    }
}
