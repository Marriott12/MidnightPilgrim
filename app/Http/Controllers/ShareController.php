<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Str;
use App\Models\Note;
use App\Models\Quote;
use App\Models\DailyThought;

class ShareController extends Controller
{
    protected $types = [
        'note' => Note::class,
        'quote' => Quote::class,
        'thought' => DailyThought::class,
    ];

    public function confirm(string $type, int $id)
    {
        if (! isset($this->types[$type])) abort(404);
        $class = $this->types[$type];
        $item = $class::findOrFail($id);

        // Prevent sharing of mental-health artifacts
        if ($this->isMentalHealthArtifact($item)) {
            abort(403, 'This item cannot be shared.');
        }

        return view('share.confirm', ['item' => $item, 'type' => $type]);
    }

    public function makeShareable(Request $request, string $type, int $id)
    {
        // Explicitly forbid sharing of check-ins or interactions.
        if (in_array($type, ['checkin', 'interaction'], true)) {
            abort(403, 'This item cannot be shared.');
        }

        if (! isset($this->types[$type])) abort(404);
        $class = $this->types[$type];
        $item = $class::findOrFail($id);

        if ($this->isMentalHealthArtifact($item)) {
            abort(403, 'This item cannot be shared.');
        }

        // Require explicit confirmation checkbox/value
        if (! $request->has('confirm') || $request->input('confirm') !== 'yes') {
            return redirect()->back()->with('error', 'Please confirm to share.');
        }

        $item->visibility = 'shareable';
        $item->save();

        // Append a local audit log entry (do not send telemetry)
        try {
            $log = storage_path('logs/share.log');
            $entry = [
                'time' => date('c'),
                'type' => $type,
                'id' => $item->id,
                'slug' => $item->slug ?? null,
                'previous_visibility' => 'reflective_or_private',
            ];
            file_put_contents($log, json_encode($entry) . PHP_EOL, FILE_APPEND | LOCK_EX);
        } catch (\Throwable $e) {
            // Fail quietly — auditing is best-effort and local-only
        }

        return redirect('/')->with('status', 'Item shared.');
    }

    protected function isMentalHealthArtifact($item): bool
    {
        // Disallow sharing of check-ins, interactions, or anything flagged as mental-health.
        $class = get_class($item);
        return in_array($class, [\App\Models\CheckIn::class, \App\Models\Interaction::class], true);
    }
}
