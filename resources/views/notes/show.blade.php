@extends('layouts.app')

@section('content')
<div>
    <article class="prose prose-invert mb-4">
        <div>{{ $note->body }}</div>
    </article>

    <div class="flex justify-end space-x-2">
        @if(method_exists($note, 'canBeShared') && $note->canBeShared())
            <a href="/share/note/{{ $note->id }}/confirm" class="text-sm text-slate-300 hover:underline">Share</a>
        @endif
    </div>
</div>
@endsection
