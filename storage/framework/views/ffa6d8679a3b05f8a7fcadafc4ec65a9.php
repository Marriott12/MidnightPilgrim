<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title><?php echo e($item['title'] ?? $item['slug']); ?> &mdash; Midnight Pilgrim</title>
    <link rel="manifest" href="/manifest.json">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #c4c4c4;
            line-height: 1.6;
            min-height: 100vh;
        }

        nav {
            padding: 1.5rem;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #666;
            text-decoration: none;
            font-size: 0.9rem;
            transition: color 0.2s;
        }

        nav a:hover {
            color: #999;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        .meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 2rem;
            padding-bottom: 1rem;
            border-bottom: 1px solid #1a1a1a;
        }

        .content {
            color: #999;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .content p {
            margin-bottom: 1rem;
        }

        .back {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #1a1a1a;
        }

        @media (max-width: 640px) {
            .container {
                padding: 2rem 1rem;
            }
        }

        ::selection {
            background: #222;
            color: #eee;
        }
    </style>
</head>
<body>
    <nav>
        <div>
            <a href="/" style="color: #999; font-weight: 500;">Midnight Pilgrim</a>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <a href="/write">Write</a>
            <a href="/read">Read</a>
            <a href="/adjacent-view">Adjacent</a>
            <a href="/sit">Sit</a>
        </div>
    </nav>

    <div class="container">
        <div class="meta">
            <?php echo e($item['date'] ?? 'Recent'); ?> &middot; <?php echo e(ucfirst($type)); ?>

            
            <?php if($type === 'notes'): ?>
                <div style="float: right; display: flex; gap: 1rem;">
                    <a href="/notes/<?php echo e($item['slug']); ?>/edit" style="color: #8b8baf; font-size: 0.85rem;">Edit</a>
                    <form action="/notes/<?php echo e($item['slug']); ?>" method="POST" style="display: inline;" onsubmit="return confirm('Delete this note quietly?');">
                        <?php echo csrf_field(); ?>
                        <?php echo method_field('DELETE'); ?>
                        <button type="submit" style="background: none; border: none; color: #666; font-size: 0.85rem; cursor: pointer; padding: 0; font-family: inherit;">Delete</button>
                    </form>
                </div>
            <?php endif; ?>
        </div>

        <div class="content">
            <?php echo nl2br(e($item['body'])); ?>

        </div>

        <div class="back">
            <a href="/read" style="color: #666;">&larr; Back to Read</a>
        </div>
    </div>

    <script>
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
<?php /**PATH C:\wamp64\www\MidnightPilgrim\resources\views/show.blade.php ENDPATH**/ ?>