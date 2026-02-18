@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 p-6 bg-slate-800 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-slate-100">Plugin & Extension Manager (Experimental)</h2>
    <p class="text-slate-300 mb-6">This is an experimental interface for managing local plugins and extensions. All plugins run locally and must respect privacy boundaries. No plugin may access mental health data or export content without explicit user consent.</p>
    <div id="plugin-list">
        <p class="text-slate-400">No plugins installed. Plugin API documentation coming soon.</p>
    </div>
</div>
<script>
// Placeholder: In production, list and manage plugins/extensions here
</script>
@endsection
