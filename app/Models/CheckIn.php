<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CheckIn extends Model
{
    protected $table = 'check_ins';

    protected $fillable = [
        'mood',
        'intensity',
        'note',
    ];

    protected $casts = [
        'intensity' => 'integer',
    ];
}
