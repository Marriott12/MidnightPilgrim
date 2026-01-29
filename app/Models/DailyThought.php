<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DailyThought extends Model
{
    protected $table = 'daily_thoughts';

    protected $fillable = [
        'title',
        'slug',
        'body',
        'mood',
        'date_generated',
        'visibility',
    ];

    protected $casts = [
        'date_generated' => 'datetime',
    ];

    protected $attributes = [
        'visibility' => 'private',
    ];

    public function canBeShared(): bool
    {
        return true;
    }
}
