<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Interaction extends Model
{
    protected $table = 'interactions';

    protected $fillable = [
        'input_text',
        'response_text',
        'mode',
    ];

    protected $casts = [
        // keep casts minimal; extend later if meta is added
    ];
}
