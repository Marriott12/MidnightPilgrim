<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;

class PreviewController extends Controller
{
    /**
     * Render markdown for live preview
     */
    public function preview(Request $request)
    {
        $content = $request->input('content', '');
        $html = Str::markdown($content);
        
        return response()->json(['html' => $html]);
    }
}
