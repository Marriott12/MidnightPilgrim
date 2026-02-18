@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 p-6 bg-slate-800 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-slate-100">AI Fallback Settings</h2>
    <p class="text-slate-300 mb-6">Configure how Midnight Pilgrim responds if the main AI service is unavailable. You can enable a local LLM (if installed), use cached responses, or default to silence. No data is sent to third parties without your explicit consent.</p>
    <form id="ai-fallback-form">
        <label class="block mb-2 text-slate-200">
            <input type="checkbox" name="enable_local_llm" /> Enable local LLM fallback (if available)
        </label>
        <label class="block mb-2 text-slate-200">
            <input type="checkbox" name="enable_cache" /> Use cached responses if AI is offline
        </label>
        <label class="block mb-2 text-slate-200">
            <input type="checkbox" name="fallback_to_silence" checked /> Fallback to silence (default)
        </label>
        <button class="mt-4 px-4 py-2 bg-slate-700 rounded text-slate-100">Save Settings</button>
    </form>
</div>
<script>
// Placeholder: In production, save and load fallback settings here
</script>
@endsection
