@extends('layouts.app')

@section('content')
<div>
    <form method="POST" action="/notes" class="space-y-3">
        <?php echo csrf_field(); ?>
        <textarea name="body" rows="10" placeholder="Write your first note..." class="w-full bg-transparent border border-slate-700 rounded p-4 text-sm"></textarea>
        <div class="flex justify-end">
            <input type="hidden" name="first_run" value="1" />
            <button type="submit" class="bg-slate-700 px-4 py-2 rounded">Save</button>
        </div>
    </form>
</div>

@endsection
