@extends('layouts.app')

@section('content')
<div class="max-w-2xl mx-auto mt-10 p-6 bg-slate-800 rounded shadow">
    <h2 class="text-xl font-bold mb-4 text-slate-100">Welcome to Midnight Pilgrim</h2>
    <p class="text-slate-300 mb-6">This is a quiet place. No notifications, no pressure, no tracking. You are in control. <br>Start by writing, reading, or exploring patterns. You can enable local analytics, plugins, or cloud sync at any time in settings.</p>
    <ul class="list-disc list-inside text-slate-200 mb-6">
        <li>All data is local-first and private by default</li>
        <li>Opt-in analytics and sync are available</li>
        <li>No engagement metrics, no social features</li>
        <li>Mental health data is always private</li>
    </ul>
    <div class="mt-6">
        <a href="/write" class="bg-slate-700 px-6 py-3 rounded text-slate-100">Begin Writing</a>
    </div>
</div>
@endsection
