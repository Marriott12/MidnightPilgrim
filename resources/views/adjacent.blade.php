<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Adjacent &mdash; Midnight Pilgrim</title>
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

        nav .current {
            color: #c4c4c4;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
        }

        /* Pattern view - no interpretation, just references */
        .intro {
            margin-bottom: 3rem;
            color: #666;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .cluster {
            margin-bottom: 3rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #1a1a1a;
        }

        .cluster:last-child {
            border-bottom: none;
        }

        .cluster-term {
            font-size: 1.1rem;
            color: #aaa;
            margin-bottom: 1.5rem;
            font-weight: 500;
        }

        .references {
            display: flex;
            flex-direction: column;
            gap: 1rem;
        }

        .reference {
            padding: 1rem;
            background: #0f0f0f;
            border-left: 2px solid #1a1a1a;
        }

        .reference-meta {
            font-size: 0.85rem;
            color: #555;
            margin-bottom: 0.5rem;
        }

        .reference-excerpt {
            color: #999;
            font-size: 0.95rem;
            line-height: 1.7;
        }

        .reference a {
            color: #888;
            text-decoration: none;
            transition: color 0.2s;
        }

        .reference a:hover {
            color: #c4c4c4;
        }

        /* Empty state */
        .empty {
            text-align: center;
            color: #444;
            padding: 4rem 1rem;
            font-size: 0.95rem;
        }

        /* Mobile */
        @media (max-width: 640px) {
            .container {
                padding: 2rem 1rem;
            }

            .cluster-term {
                font-size: 1rem;
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
            <span style="margin-left: 1.5rem; font-size: 0.85rem; color: #444;">{{ now()->format('F j') }}</span>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <a href="/write">Write</a>
            <a href="/read">Read</a>
            <a href="/adjacent-view" class="current">Adjacent</a>
            <a href="/sit">Sit</a>
        </div>
    </nav>

    <div class="container">
        <div class="intro">
            What have you been circling lately?<br>
            No labels, no summaries—just the patterns in your own words.
        </div>

        @forelse($clusters ?? [] as $cluster)
            <div class="cluster">
                <div class="cluster-term">{{ $cluster['term'] }}</div>
                
                <div class="references">
                    @foreach($cluster['references'] ?? [] as $ref)
                        <div class="reference" role="article">
                            <div class="reference-meta">
                                <a href="/{{ $ref['type'] }}/{{ $ref['slug'] }}" aria-label="View {{ $ref['title'] ?? $ref['slug'] }}">
                                    {{ $ref['title'] ?? $ref['slug'] }}
                                </a>
                                &middot;
                                {{ $ref['date'] ?? 'Recent' }}
                            </div>
                            <div class="reference-excerpt">
                                {{ $ref['excerpt'] }}
                            </div>
                        </div>
                    @endforeach
                </div>
            </div>
        @empty
            <div class="empty">
                No patterns yet.<br>
                As you write, recurring themes will surface here.
            </div>
        @endforelse
    </div>

    <script>
        // PWA: Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }
    </script>
</body>
</html>
