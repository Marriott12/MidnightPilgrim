<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>{{ $item['title'] ?? $item['slug'] }} &mdash; Midnight Pilgrim</title>
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

        /* Markdown styling */
        .content h1, .content h2, .content h3 {
            color: #c4c4c4;
            font-weight: 400;
            margin: 2rem 0 1rem;
        }

        .content h1 { font-size: 1.8rem; }
        .content h2 { font-size: 1.4rem; }
        .content h3 { font-size: 1.2rem; }

        .content blockquote {
            border-left: 2px solid #333;
            padding-left: 1rem;
            margin: 1.5rem 0;
            color: #8b8baf;
            font-style: italic;
        }

        .content code {
            background: #0f0f0f;
            padding: 0.2rem 0.4rem;
            border-radius: 2px;
            font-size: 0.9em;
        }

        .content pre {
            background: #0f0f0f;
            padding: 1rem;
            border-radius: 2px;
            overflow-x: auto;
            margin: 1rem 0;
        }

        .content pre code {
            background: none;
            padding: 0;
        }

        .content ul, .content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }

        .content li {
            margin: 0.5rem 0;
        }

        .content a {
            color: #8b8baf;
            text-decoration: none;
            border-bottom: 1px solid #333;
        }

        .content a:hover {
            border-bottom-color: #8b8baf;
        }

        .content .wikilink {
            color: #8b8baf;
            border-bottom: 1px solid #333;
        }

        .content .wikilink:hover {
            border-bottom-color: #8b8baf;
        }

        .content .wikilink-missing {
            color: #555;
            border-bottom: 1px dotted #333;
            cursor: help;
        }

        .backlinks {
            margin-top: 3rem;
            padding-top: 2rem;
            border-top: 1px solid #1a1a1a;
        }

        .backlinks-title {
            font-size: 0.85rem;
            color: #555;
            text-transform: uppercase;
            letter-spacing: 0.05em;
            margin-bottom: 1rem;
        }

        .backlinks-list {
            list-style: none;
            padding: 0;
        }

        .backlinks-list li {
            margin: 0.5rem 0;
        }

        .backlinks-list a {
            color: #666;
            text-decoration: none;
            font-size: 0.95rem;
        }

        .backlinks-list a:hover {
            color: #8b8baf;
        }

        .back {
            margin-top: 2rem;
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
            {{ $item['date'] ?? 'Recent' }} &middot; {{ ucfirst($type) }}
            
            @if($type === 'notes')
                <div style="float: right; display: flex; gap: 1rem;">
                    <a href="/notes/{{ $item['slug'] }}/download" style="color: #666; font-size: 0.85rem;">Download</a>
                    <a href="/notes/{{ $item['slug'] }}/edit" style="color: #8b8baf; font-size: 0.85rem;">Edit</a>
                    <form action="/notes/{{ $item['slug'] }}" method="POST" style="display: inline;" onsubmit="return confirm('Delete this note quietly?');">
                        @csrf
                        @method('DELETE')
                        <button type="submit" style="background: none; border: none; color: #666; font-size: 0.85rem; cursor: pointer; padding: 0; font-family: inherit;">Delete</button>
                    </form>
                </div>
            @endif
        </div>

        <div class="content">
            {!! Str::markdown($item['body']) !!}
        </div>

        @if(!empty($backlinks) && count($backlinks) > 0)
            <div class="backlinks">
                <div class="backlinks-title">Linked from {{ count($backlinks) }} note{{ count($backlinks) !== 1 ? 's' : '' }}</div>
                <ul class="backlinks-list">
                    @foreach($backlinks as $backlink)
                        <li><a href="/view/notes/{{ $backlink['slug'] }}">{{ $backlink['title'] }}</a></li>
                    @endforeach
                </ul>
            </div>
        @endif

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
