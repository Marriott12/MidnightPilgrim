<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\MentalHealthCompanionService;

class CheckInController
{
    protected MentalHealthCompanionService $mh;

    public function __construct(MentalHealthCompanionService $mh)
    {
        $this->mh = $mh;
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'mood' => 'required|string|max:100',
            'intensity' => 'required|integer|min:1|max:5',
            'note' => 'nullable|string|max:2000',
        ]);

        $checkin = $this->mh->storeCheckIn($data['mood'], (int) $data['intensity'], $data['note'] ?? null);

        return response()->json(['check_in' => $checkin]);
    }
}
