@extends('layouts.app')

@section('content')
<div>
    <h2 class="text-lg font-medium mb-2">Share this {{ ucfirst($type) }}</h2>
    <p class="text-sm text-slate-300 mb-4">Once shared, this item may be read outside your private space.</p>

    <div class="mb-4 bg-black/30 p-4 rounded">
        <div>{{ $item->body ?? ($item->excerpt ?? '') }}</div>
    </div>

    <form method="POST" action="/share/{{ $type }}/{{ $item->id }}">
        <?php echo csrf_field(); ?>
        <input type="hidden" name="confirm" value="yes" />
        <div class="flex justify-end">
            <button type="submit" class="bg-slate-700 px-4 py-2 rounded">Make Shareable</button>
        </div>
    </form>
</div>
@endsection
