<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use ZipArchive;
use App\Models\Note;
use App\Models\Quote;
use App\Models\DailyThought;

class ExportController extends Controller
{
    public function export()
    {
        $exportDir = storage_path('app/exports');
        if (! is_dir($exportDir)) {
            mkdir($exportDir, 0777, true);
        }

        $zipPath = $exportDir . '/midnight_pilgrim_export_' . time() . '.zip';
        $zip = new ZipArchive();
        if ($zip->open($zipPath, ZipArchive::CREATE) !== true) {
            abort(500, 'Could not create export archive');
        }

        // Notes
        foreach (Note::all() as $n) {
            $name = 'notes/' . ($n->slug ?? 'note') . '.md';
            $content = ($n->body ?? '') . "\n";
            $zip->addFromString($name, $content);
        }

        // Quotes
        foreach (Quote::all() as $q) {
            $name = 'quotes/' . ($q->slug ?? 'quote') . '.md';
            $content = ($q->body ?? '') . "\n";
            $zip->addFromString($name, $content);
        }

        // Daily thoughts
        foreach (DailyThought::all() as $d) {
            $name = 'thoughts/' . ($d->id ?? 'thought') . '.md';
            $content = ($d->body ?? '') . "\n";
            $zip->addFromString($name, $content);
        }

        $zip->close();

        return response()->download($zipPath)->deleteFileAfterSend(true);
    }
}
