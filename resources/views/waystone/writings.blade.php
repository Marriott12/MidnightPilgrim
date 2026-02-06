<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Writings &mdash; Midnight Pilgrim</title>
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        body {
            font-family: Georgia, 'Times New Roman', serif;
            background: #0a0a0a;
            color: #999;
            line-height: 1.8;
            min-height: 100vh;
        }

        nav {
            padding: 2rem;
            border-bottom: 1px solid #1a1a1a;
            max-width: 800px;
            margin: 0 auto;
            display: flex;
            gap: 2rem;
            flex-wrap: wrap;
        }

        nav a {
            color: #555;
            text-decoration: none;
            font-size: 0.95rem;
            transition: color 0.3s;
        }

        nav a:hover, nav a.current {
            color: #888;
        }

        .container {
            max-width: 680px;
            margin: 0 auto;
            padding: 4rem 2rem 8rem;
        }

        h1 {
            font-size: 2rem;
            color: #c4c4c4;
            font-weight: 400;
            margin-bottom: 3rem;
            line-height: 1.3;
        }

        .intro {
            color: #777;
            font-size: 0.95rem;
            margin-bottom: 3rem;
            font-style: italic;
        }

        .writing {
            margin-bottom: 4rem;
            padding-bottom: 3rem;
            border-bottom: 1px solid #1a1a1a;
        }

        .writing:last-child {
            border-bottom: none;
        }

        .writing-body {
            color: #888;
            line-height: 1.9;
            white-space: pre-wrap;
        }

        .empty {
            text-align: center;
            color: #555;
            padding: 6rem 2rem;
            font-style: italic;
        }

        @media (max-width: 640px) {
            .container {
                padding: 3rem 1.5rem 6rem;
            }

            h1 {
                font-size: 1.6rem;
            }

            nav {
                padding: 1.5rem;
            }
        }

        ::selection {
            background: #222;
            color: #ddd;
        }
    </style>
</head>
<body>
    <nav>
        <a href="/waystone">Philosophy</a>
        <a href="/waystone/writings" class="current">Writings</a>
        <a href="/waystone/download">Download</a>
    </nav>

    <div class="container">
        <h1>Shared Writings</h1>

        <p class="intro">
            Only content explicitly marked "shareable" appears here.<br>
            No dates, no comments, no metrics—just words.
        </p>

        @forelse($writings ?? [] as $writing)
            <div class="writing">
                <div class="writing-body">{{ $writing['body'] }}</div>
            </div>
        @empty
            <div class="empty">
                Nothing has been shared yet.<br>
                This space remains quiet.
            </div>
        @endforelse
    </div>
</body>
</html>
