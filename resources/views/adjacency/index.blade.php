@extends('layouts.app')

@section('content')
<div>
    <h2 class="text-lg font-medium mb-2">You have returned often to:</h2>

    @if(empty($groups))
        <p class="text-slate-400">Quiet. Nothing repeats often yet.</p>
    @else
        <ul class="list-disc pl-6 mb-4">
        @foreach($groups as $g)
            <li class="mb-1">{{ $g['term'] }}</li>
        @endforeach
        </ul>

        <div class="text-sm text-slate-300">
            @foreach($groups as $g)
                <div class="mb-2">
                    <strong>{{ $g['count'] }}</strong> occurrences — {{ count(array_filter($g['refs'], fn($r)=> $r['type']==='note')) }} notes, {{ count(array_filter($g['refs'], fn($r)=> $r['type']==='quote')) }} poems, {{ count(array_filter($g['refs'], fn($r)=> $r['type']==='thought')) }} thoughts
                </div>
            @endforeach
        </div>
    @endif
</div>
@endsection
