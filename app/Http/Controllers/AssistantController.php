<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AssistantService;

class AssistantController
{
    protected AssistantService $assistant;

    public function __construct(AssistantService $assistant)
    {
        $this->assistant = $assistant;
    }

    public function handle(Request $request)
    {
        $data = $request->validate([
            'input' => 'required|string|max:2000',
            'mode' => 'nullable|string|in:listen,reflect,ask',
        ]);

        $response = $this->assistant->handle($data['input'], $data['mode'] ?? 'listen');

        return response()->json(['response' => $response]);
    }
}
