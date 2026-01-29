<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Note;

class NoteController extends Controller
{
    public function create()
    {
        return view('notes.create');
    }

    public function show($id)
    {
        $note = Note::findOrFail($id);
        return view('notes.show', ['note' => $note]);
    }

    /**
     * Store a new note. If `first_run` is present, preserve silence by
     * not triggering any assistant/reflection behavior.
     */
    public function store(Request $request)
    {
        $body = $request->input('body', '');
        $note = new Note();
        $note->slug = substr(sha1($body . time()), 0, 8);
        $note->body = $body;
        $note->path = null;
        $note->visibility = 'private';
        $note->save();

        // If first_run, intentionally remain silent: do not call AssistantService
        if ($request->has('first_run')) {
            return redirect('/notes/' . $note->id)->with('status', 'saved');
        }

        return redirect('/notes/' . $note->id)->with('status', 'saved');
    }
}
