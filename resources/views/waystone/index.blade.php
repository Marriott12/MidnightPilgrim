@extends('layouts.app')

@section('content')
<div>
    <h2 class="text-lg font-medium mb-4">Waystone — Shared Thoughts</h2>

    @if($quotes->isEmpty() && $notes->isEmpty() && $thoughts->isEmpty())
        <p class="text-slate-400">There is nothing shared yet.</p>
    @endif

    @foreach($quotes as $q)
        <div class="mb-4">
            <blockquote class="italic">{{ $q->body ?? '' }}</blockquote>
        </div>
    @endforeach

    @foreach($notes as $n)
        <div class="mb-4">
            <div>{{ $n->body ?? '' }}</div>
        </div>
    @endforeach

    @foreach($thoughts as $t)
        <div class="mb-4">
            <div class="text-sm text-slate-300">{{ $t->body ?? '' }}</div>
        </div>
    @endforeach
</div>
@endsection
