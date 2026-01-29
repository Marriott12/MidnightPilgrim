@extends('layouts.app')

@section('content')
<div class="text-center">
    <p class="text-sm text-slate-300">This is a quiet place. You don’t have to do anything.</p>
    <div class="mt-6">
        <button id="beginBtn" class="bg-slate-700 px-6 py-3 rounded">Begin</button>
    </div>
</div>

<script>
document.getElementById('beginBtn').addEventListener('click', function(){
    try { localStorage.setItem('midnight_pilgrim_first_run', (new Date()).toISOString()); } catch(e) {}
    // send user to blank note creation
    window.location.href = '/notes/new';
});
</script>

@endsection
