<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use ZipArchive;

class ExportController extends Controller
{
    /**
     * Download a single note as markdown file
     */
    public function downloadNote($slug)
    {
        $path = "vault/{$slug}.md";
        
        if (!Storage::exists($path)) {
            abort(404, 'Note not found');
        }
        
        return Storage::download($path, "{$slug}.md");
    }
    
    /**
     * Export entire vault as ZIP file
     */
    public function exportVault()
    {
        $zipName = 'midnight-pilgrim-vault-' . date('Y-m-d') . '.zip';
        $zipPath = storage_path('app/' . $zipName);
        
        $zip = new ZipArchive();
        
        if ($zip->open($zipPath, ZipArchive::CREATE | ZipArchive::OVERWRITE) !== true) {
            abort(500, 'Could not create ZIP file');
        }
        
        // Add all vault files
        $vaultFiles = Storage::files('vault');
        foreach ($vaultFiles as $file) {
            $zip->addFile(storage_path('app/' . $file), 'vault/' . basename($file));
        }
        
        // Add all quote files
        $quoteFiles = Storage::files('quotes');
        foreach ($quoteFiles as $file) {
            $zip->addFile(storage_path('app/' . $file), 'quotes/' . basename($file));
        }
        
        // Add all thought files
        $thoughtFiles = Storage::files('thoughts');
        foreach ($thoughtFiles as $file) {
            $zip->addFile(storage_path('app/' . $file), 'thoughts/' . basename($file));
        }
        
        $zip->close();
        
        return response()->download($zipPath, $zipName)->deleteFileAfterSend(true);
    }
}
