<!doctype html>
<html lang="en" class="dark">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <meta name="theme-color" content="#0f172a">
    <link rel="manifest" href="/manifest.json">
    <title>Midnight Pilgrim</title>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js" defer></script>
    <script>
        // Register service worker and install prompt
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('/sw.js').catch(()=>{});
            });
        }
        window.addEventListener('beforeinstallprompt', (e) => {
            window.deferredPrompt = e;
        });
    </script>
    <link href="https://cdn.jsdelivr.net/npm/tailwindcss@3.4.0/dist/tailwind.min.css" rel="stylesheet">
    <style>html,body{height:100%} body{background:#0b1220;color:#e6eef8;font-family:Inter,ui-sans-serif,system-ui,-apple-system,'Segoe UI',Roboto,'Helvetica Neue',Arial}</style>
</head>
<body class="min-h-screen flex items-center justify-center">
    <?php $rhythm = app(\App\Services\RhythmResolver::class)->determine(request()->input('q') ?? request()->input('input') ?? null); ?>
    <div class="w-full max-w-md mx-4" x-data="{dismissed:false}">
        <div class="bg-black/40 rounded-xl p-6" :class="{ 'py-12 px-8': '<?php echo $rhythm; ?>' === 'vigil', 'py-6 px-4': '<?php echo $rhythm; ?>' === 'pulse' }">
            <div class="flex items-center justify-between mb-4">
                <h1 class="text-center text-2xl font-light tracking-tight">Midnight Pilgrim</h1>
                <nav class="text-sm text-slate-300 space-x-4">
                    <a href="/adjacency" class="hover:underline">Adjacency</a>
                    <a href="/waystone" class="hover:underline">Waystone</a>
                    <a href="/download" class="hover:underline">Download</a>
                    <a href="/support" class="hover:underline">Support</a>
                </nav>
            </div>

            <div class="mb-4">
                <?php echo $__env->yieldContent('content'); ?>
            </div>

            <div class="flex items-center justify-between mt-6">
                <a href="/support" class="text-sm text-slate-300 hover:underline">Support</a>
                <?php echo $__env->make('components.presence', array_diff_key(get_defined_vars(), ['__data' => 1, '__path' => 1]))->render(); ?>
            </div>
        </div>
    </div>
</body>
</html>
<?php /**PATH C:\wamp64\www\MidnightPilgrim\resources\views/layouts/app.blade.php ENDPATH**/ ?>