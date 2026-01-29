<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;
use App\Models\Quote;
use App\Models\DailyThought;

class WaystoneController extends Controller
{
    /**
     * Public-facing index: only shareable items.
     */
    public function index()
    {
        $notes = Note::where('visibility', 'shareable')->get();
        $quotes = Quote::where('visibility', 'shareable')->get();
        $thoughts = DailyThought::where('visibility', 'shareable')->get();

        return view('waystone.index', compact('notes','quotes','thoughts'));
    }

    public function philosophy()
    {
        return view('waystone.philosophy');
    }

    public function download()
    {
        return view('waystone.download');
    }

    public function silence()
    {
        return view('waystone.silence');
    }
}
