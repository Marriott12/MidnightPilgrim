<?php

namespace App\Http\Requests;

use Illuminate\Foundation\Http\FormRequest;

class DeclarePlatformRequest extends FormRequest
{
    public function authorize()
    {
        return true;
    }

    public function rules()
    {
        return [
            'platform' => 'required|string|in:Medium,Substack,Twitter,Personal Blog',
            'timezone' => 'required|string',
            'start_date' => 'nullable|date',
        ];
    }
}
