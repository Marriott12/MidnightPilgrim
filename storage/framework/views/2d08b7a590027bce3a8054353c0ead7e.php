<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Midnight Pilgrim</title>
    <link rel="manifest" href="/manifest.json">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            font-size: 16px;
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            background: #0a0a0a;
            color: #888;
            line-height: 1.8;
            font-weight: 300;
            -webkit-font-smoothing: antialiased;
        }

        .container {
            max-width: 700px;
            margin: 0 auto;
            padding: 6rem 2rem 8rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: -0.02em;
            color: #c4c4c4;
            margin-bottom: 0.5rem;
        }

        .subtitle {
            font-size: 1rem;
            color: #666;
            margin-bottom: 3rem;
            font-weight: 300;
        }

        .last-note {
            font-size: 0.9rem;
            color: #555;
            margin-bottom: 2rem;
        }

        .last-note a {
            color: #777;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: all 0.2s;
        }

        .last-note a:hover {
            color: #999;
            border-bottom-color: #444;
        }

        .entry {
            font-size: 0.9375rem;
            margin-bottom: 6rem;
        }

        .entry a {
            color: #777;
            text-decoration: none;
            border-bottom: 1px solid transparent;
            transition: all 0.2s;
        }

        .entry a:hover {
            color: #999;
            border-bottom-color: #444;
        }

        .divider {
            height: 1px;
            background: #1a1a1a;
            margin: 3rem 0;
        }

        .modes {
            text-align: center;
            margin: 3rem 0;
            font-size: 0.9rem;
            color: #555;
        }

        .modes a {
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
            padding: 0 0.5rem;
        }

        .modes a:hover {
            color: #999;
        }

        section {
            margin-top: 3rem;
            line-height: 1.6;
        }

        section p {
            color: #555;
        }

        .install-btn {
            display: inline-block;
            margin-top: 2rem;
            padding: 0.5rem 1.25rem;
            background: transparent;
            border: 1px solid #1a1a1a;
            color: #555;
            font-size: 0.8125rem;
            font-family: inherit;
            font-weight: 300;
            cursor: pointer;
            transition: all 0.2s;
        }

        .install-btn:hover {
            border-color: #262626;
            color: #666;
        }

        .install-btn.hidden {
            display: none;
        }

        ::selection {
            background: #222;
            color: #eee;
        }

        @media (max-width: 640px) {
            .container {
                padding: 4rem 1.5rem 6rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .subtitle {
                font-size: 0.95rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Midnight Pilgrim</h1>
        <p class="subtitle">A quiet space for writing</p>

        <?php if(isset($lastNote)): ?>
            <p class="last-note">
                Last: <a href="/view/notes/<?php echo e($lastNote['slug']); ?>"><?php echo e($lastNote['title']); ?></a> · <?php echo e($lastNote['date']); ?>

            </p>
        <?php endif; ?>

        <div style="margin: 4rem 0 6rem;">
            <p class="entry"><a href="/write" style="font-size: 1.125rem; color: #999;">Write</a></p>
        </div>

        <div class="modes">
            <a href="/read">Read</a> · 
            <a href="/conversation">Conversation</a> · 
            <a href="/adjacent-view">Adjacent</a> · 
            <a href="/sit">Sit</a>
        </div>

        <div class="divider"></div>

        <section style="font-size: 0.875rem; color: #555;">
            <p style="margin-bottom: 1rem;">Local-first. Your notes are markdown files on your machine.</p>
            <p style="margin-bottom: 1rem;">Silence is valid. No tracking, no notifications, no engagement metrics.</p>
            <p>Private. Mental health check-ins stay local and isolated.</p>
        </section>

        <div style="text-align: center; margin-top: 6rem;">
            <button id="installBtn" class="install-btn hidden">Install locally</button>
        </div>
    </div>

    <script>
        // PWA Install - Quiet, optional
        let deferredPrompt;
        
        window.addEventListener('beforeinstallprompt', (e) => {
            e.preventDefault();
            deferredPrompt = e;
            document.getElementById('installBtn').classList.remove('hidden');
        });

        document.getElementById('installBtn').addEventListener('click', async () => {
            if (!deferredPrompt) return;
            deferredPrompt.prompt();
            const { outcome } = await deferredPrompt.userChoice;
            deferredPrompt = null;
            if (outcome === 'accepted') {
                document.getElementById('installBtn').classList.add('hidden');
            }
        });
        
        // Service worker registration
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }
    </script>
</body>
</html>
<?php /**PATH C:\wamp64\www\MidnightPilgrim\resources\views/welcome.blade.php ENDPATH**/ ?>