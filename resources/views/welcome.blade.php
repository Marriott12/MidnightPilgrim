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
            padding: 8rem 2rem 12rem;
        }

        h1 {
            font-size: 2rem;
            font-weight: 300;
            letter-spacing: -0.02em;
            color: #c4c4c4;
            margin-bottom: 1rem;
        }

        .subtitle {
            font-size: 1.125rem;
            color: #666;
            margin-bottom: 2rem;
            font-weight: 300;
        }

        .last-note {
            font-size: 0.9375rem;
            color: #555;
            margin-bottom: 1rem;
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

        section {
            margin-bottom: 5rem;
        }

        h2 {
            font-size: 0.875rem;
            text-transform: uppercase;
            letter-spacing: 0.15em;
            color: #555;
            margin-bottom: 1.5rem;
            font-weight: 400;
        }

        p {
            margin-bottom: 1.5rem;
            font-size: 1rem;
            color: #777;
        }

        a {
            color: inherit;
            text-decoration: none;
        }

        .divider {
            height: 1px;
            background: #1a1a1a;
            margin: 5rem 0;
        }

        .modes {
            text-align: center;
            margin: 6rem 0;
            font-size: 0.9375rem;
            color: #555;
        }

        .modes a {
            color: #666;
            text-decoration: none;
            transition: color 0.2s;
        }

        .modes a:hover {
            color: #999;
        }

        .closing {
            font-size: 0.9375rem;
            color: #555;
            font-style: italic;
            margin-top: 8rem;
            text-align: center;
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
                padding: 4rem 1.5rem 8rem;
            }

            h1 {
                font-size: 1.75rem;
            }

            .subtitle {
                font-size: 1rem;
                margin-bottom: 4rem;
            }

            section {
                margin-bottom: 4rem;
            }
        }
    </style>
</head>
<body>
    <div class="container">
        <h1>Midnight Pilgrim</h1>
        <p class="subtitle">A quiet system for thinking — not performing</p>

        @if(isset($lastNote))
            <p class="last-note">
                Last: <a href="/view/notes/{{ $lastNote['slug'] }}">{{ $lastNote['title'] }}</a>
            </p>
        @endif

        <p class="entry"><a href="/write">Write</a></p>

        <section>
            <h2>What This Is</h2>
            <p>Midnight Pilgrim is a local-first writing environment. Your notes exist as markdown files on your machine. The application provides structure for reflection without imposing pace, goal, or interpretation.</p>
            <p>It does not track engagement. It does not suggest what to write next. It does not attempt to optimize your thinking or improve your output. The interface exists to serve access, not to shape behavior.</p>
            <p>You can write in silence. You can sit with mental health check-ins that are never analyzed, never surfaced, never used to encourage continued use. You can leave at any time without losing access to what you wrote.</p>
        </section>

        <div class="divider"></div>

        <section>
            <h2>Silence</h2>
            <p>Silence is a valid state. The application does not interpret gaps in writing as problems to solve. It does not send notifications. It does not re-engage you after absence.</p>
            <p>You can enable write-only mode, where content is never processed. You can mark notes as no-archive, meaning they will not surface in future sessions. You can enter timed stillness — an inert screen with no input, no output, no measurement.</p>
            <p>These are not hidden features. They are design choices that treat silence as equal to speech.</p>
        </section>

        <div class="divider"></div>

        <section>
            <h2>Autonomy</h2>
            <p>Your data lives in plaintext markdown files. The database is optional infrastructure for search and adjacency. If you remove the application, your writing remains intact and readable.</p>
            <p>There is no cloud sync by default. There is no account. There is no dependency on external services to access what you wrote. The application does not create lock-in.</p>
            <p>You can fork the codebase. You can modify the interface. You can remove features you find intrusive. This is not protected by designed complexity.</p>
        </section>

        <div class="divider"></div>

        <section>
            <h2>Artificial Intelligence</h2>
            <p>The companion feature uses AI, but it does not attempt to become a relationship. It references your own notes when relevant. It does not remember prior conversations. It does not build a model of you over time.</p>
            <p>Responses avoid interpretation, diagnosis, and advice. The language is witnessing, not coaching. When you stop writing, it does not continue. When you close the session, nothing persists except what you explicitly saved.</p>
            <p>AI is a tool for adjacency and resonance, not for optimization or correction. It does not try to make you more productive, more insightful, or more consistent. It reflects proximity between your words, not progress.</p>
        </section>

        <div class="divider"></div>

        <section>
            <h2>Mental Health Boundaries</h2>
            <p>The Sit mode allows private mental health check-ins. These are stored locally and isolated from all other features. They are never analyzed for trends. They are never surfaced as memories. They do not generate insights or suggestions.</p>
            <p>The companion does not diagnose. It does not offer therapeutic advice. It does not claim to help. It witnesses what you write and sometimes reflects your own words back. That is the boundary.</p>
            <p>If you are in crisis, this is not a substitute for human support. The application does not escalate, intervene, or connect you to resources. It is a container for private thought, not a care system.</p>
        </section>

        <div class="divider"></div>

        <div class="modes">
            <a href="/write">Write</a> · <a href="/read">Read</a> · <a href="/adjacent-view">Adjacent</a> · <a href="/sit">Sit</a>
        </div>

        <p class="closing">This page describes intent. If the application violates these boundaries, that is a failure to maintain the covenant, not a feature.</p>

        <div style="text-align: center;">
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
