<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Emergent Quotes &mdash; Midnight Pilgrim</title>
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

        .header {
            margin-bottom: 3rem;
        }

        .header h1 {
            font-size: 1.8rem;
            font-weight: 300;
            color: #c4c4c4;
            margin-bottom: 0.5rem;
        }

        .header p {
            color: #555;
            font-size: 0.95rem;
        }

        .quote-item {
            margin: 2rem 0;
            padding: 1.5rem;
            border-left: 2px solid #333;
            background: #0d0d0d;
        }

        .quote-text {
            color: #8b8baf;
            font-size: 1.1rem;
            line-height: 1.7;
            margin-bottom: 1rem;
            font-style: italic;
        }

        .quote-meta {
            font-size: 0.85rem;
            color: #555;
        }

        .sources {
            margin-top: 0.75rem;
        }

        .sources a {
            color: #666;
            text-decoration: none;
            margin-right: 1rem;
            transition: color 0.2s;
        }

        .sources a:hover {
            color: #8b8baf;
        }

        .empty {
            text-align: center;
            color: #444;
            padding: 3rem 1rem;
            font-size: 0.95rem;
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
            <a href="/adjacent-view">Adjacent</a>
            <a href="/sit">Sit</a>
        </div>
    </nav>

    <div class="container">
        <div class="header">
            <h1>Emergent Quotes</h1>
            <p>Sentences that have appeared multiple times across your writings.</p>
        </div>

        @if(count($emergent) > 0)
            @foreach($emergent as $quote)
                <div class="quote-item">
                    <div class="quote-text">"{{ $quote['sentence'] }}"</div>
                    <div class="quote-meta">
                        Appeared {{ $quote['count'] }} times
                        
                        <div class="sources">
                            @foreach($quote['sources'] as $slug)
                                <a href="/view/notes/{{ $slug }}">{{ ucfirst(str_replace('-', ' ', $slug)) }}</a>
                            @endforeach
                        </div>
                    </div>
                </div>
            @endforeach
        @else
            <div class="empty">
                No emergent quotes found yet. Write more to discover patterns.
            </div>
        @endif
    </div>
</body>
</html>
