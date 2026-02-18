@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 p-6 bg-slate-800 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-slate-100">Local Analytics Dashboard (Opt-In)</h2>
    <p class="text-slate-300 mb-6">This dashboard is private and local-only. No data ever leaves your device. Enable analytics in settings to view usage patterns, writing streaks, and emotional trends. No engagement metrics, no tracking, no sharing.</p>
    <div id="analytics-content">
        <p class="text-slate-400">Analytics are currently disabled. Enable in settings to view your data.</p>
    </div>
</div>
<script>
// Placeholder: In production, fetch and render local analytics here
</script>
@endsection
