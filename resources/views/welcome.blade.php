<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Midnight Pilgrim</title>
        <link rel="manifest" href="/manifest.json">
        <link rel="icon" type="image/x-icon" href="/favicon.ico">
        <meta name="theme-color" content="#0a0a0a">
        <meta name="description" content="A quiet place for your thoughts. Silence-first, local-first, no tracking.">
        
        <style>
            /* Reset */
            * { margin: 0; padding: 0; box-sizing: border-box; }
            
            /* Base */
            html { 
                scroll-behavior: smooth;
                font-size: 16px;
            }
            
            body { 
                font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, "Helvetica Neue", sans-serif;
                background: #0a0a0a;
                color: #999;
                line-height: 1.8;
                font-weight: 300;
                -webkit-font-smoothing: antialiased;
            }
            
            /* Typography */
            h1, h2, h3 { font-weight: 300; line-height: 1.2; }
            h1 { 
                font-size: 2.5rem; 
                letter-spacing: -0.02em; 
                color: #c4c4c4;
            }
            h2 { 
                font-size: 1.125rem; 
                letter-spacing: -0.01em; 
                color: #999;
                font-weight: 300;
            }
            h3 { 
                font-size: 1rem; 
                color: #c4c4c4;
                font-weight: 400;
            }
            
            p { color: #777; }
            a { color: inherit; text-decoration: none; }
            
            ::selection {
                background: #222;
                color: #eee;
            }
            
            /* Layout */
            .container {
                max-width: 40rem;
                margin: 0 auto;
                padding: 0 1.5rem;
            }
            
            .section {
                padding: 5rem 0;
            }
            
            /* Hero - Single focal point */
            .hero {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 3rem 1.5rem;
            }
            
            .hero-content {
                max-width: 28rem;
            }
            
            .hero h1 {
                margin-bottom: 1.5rem;
            }
            
            .hero .tagline {
                font-size: 1.125rem;
                color: #999;
                margin-bottom: 2.5rem;
                line-height: 1.6;
            }
            
            .hero .principles {
                font-size: 0.75rem;
                color: #555;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                margin-bottom: 3.5rem;
            }
            
            /* Separator - Visual silence */
            .separator {
                width: 3rem;
                height: 1px;
                background: #262626;
                margin: 2.5rem auto;
            }
            
            /* Button - Restrained */
            .btn {
                display: inline-block;
                padding: 0.875rem 2.75rem;
                background: transparent;
                border: 1px solid #333;
                color: #999;
                font-size: 0.9375rem;
                font-weight: 300;
                letter-spacing: 0.02em;
                transition: all 0.3s ease;
                cursor: pointer;
            }
            
            .btn:hover {
                border-color: #666;
                color: #c4c4c4;
                background: rgba(255, 255, 255, 0.02);
            }
            
            /* What This Is - Calm explanation */
            .intro {
                text-align: center;
                max-width: 34rem;
                margin: 0 auto 5rem;
            }
            
            .intro p {
                font-size: 1rem;
                line-height: 1.9;
                color: #777;
            }
            
            /* Modes - Not features, ways of being */
            .modes {
                display: grid;
                gap: 2.5rem;
                margin-bottom: 3rem;
            }
            
            .mode {
                padding: 1.5rem;
                border: 1px solid #1a1a1a;
                transition: all 0.3s ease;
            }
            
            .mode:hover {
                border-color: #262626;
                background: rgba(255, 255, 255, 0.01);
            }
            
            .mode h3 {
                margin-bottom: 0.75rem;
            }
            
            .mode p {
                font-size: 0.9375rem;
                line-height: 1.7;
                color: #666;
            }
            
            .mode a {
                display: block;
            }
            
            .mode a:hover h3 {
                color: #8b8baf;
            }
            
            /* Philosophy - Collapsible, quiet */
            .philosophy {
                text-align: center;
                padding: 4rem 0;
                border-top: 1px solid #1a1a1a;
            }
            
            .philosophy p {
                font-size: 0.9375rem;
                font-style: italic;
                margin-bottom: 1.25rem;
                color: #555;
            }
            
            .philosophy a {
                font-size: 0.875rem;
                color: #666;
                border-bottom: 1px solid transparent;
                transition: all 0.3s ease;
            }
            
            .philosophy a:hover {
                border-bottom-color: #666;
                color: #8b8baf;
            }
            
            /* Footer - Entry points */
            .footer {
                text-align: center;
                padding: 3rem 0 5rem;
            }
            
            .install-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 2rem;
                background: #0f0f0f;
                border: 1px solid #1a1a1a;
                color: #666;
                font-size: 0.875rem;
                transition: all 0.3s ease;
                margin-bottom: 1rem;
                cursor: pointer;
                font-family: inherit;
                font-weight: 300;
            }
            
            .install-btn:hover {
                border-color: #333;
                color: #999;
                background: #121212;
            }
            
            .install-btn.hidden {
                display: none;
            }
            
            .continue-link {
                display: block;
                margin-top: 1rem;
                font-size: 0.875rem;
                color: #444;
            }
            
            .continue-link:hover {
                color: #666;
            }
            
            /* Responsive - Mobile first */
            @media (min-width: 48rem) {
                h1 { font-size: 3.25rem; }
                .hero .tagline { font-size: 1.25rem; }
                .modes { 
                    grid-template-columns: repeat(2, 1fr); 
                    gap: 2rem;
                }
                .section { padding: 6rem 0; }
            }
            
            @media (min-width: 64rem) {
                h1 { font-size: 3.75rem; }
                .container { max-width: 44rem; }
            }
            
            /* Reduced motion preference */
            @media (prefers-reduced-motion: reduce) {
                * {
                    animation: none !important;
                    transition: none !important;
                }
                html { scroll-behavior: auto; }
            }
        </style>
    </head>
    
    <body>
        <!-- Hero / Arrival - Single focal point -->
        <section class="hero">
            <div class="hero-content">
                <h1>Midnight Pilgrim</h1>
                <p class="tagline">A quiet place for your thoughts.</p>
                
                @if(isset($lastNote))
                    <p style="font-size: 0.9rem; color: #555; margin: 1.5rem 0 0 0;">
                        You last wrote here on <span style="color: #666;">{{ $lastNote['date'] }}</span>: 
                        <a href="/view/notes/{{ $lastNote['slug'] }}" style="color: #8b8baf; text-decoration: none;">{{ $lastNote['title'] }}</a>
                    </p>
                @endif
                
                <div class="separator"></div>
                <p class="principles">Silence-first · Local-first · No tracking</p>
                <a href="/write" class="btn">Begin</a>
            </div>
        </section>
        
        <!-- What This Is - Explanation -->
        <section class="section">
            <div class="container">
                <div class="intro">
                    <p>
                        Midnight Pilgrim is a local-first writing space. Your thoughts live as markdown files 
                        on your machine. There is no optimization, no engagement tracking, no persuasion. 
                        Everything is private by default. The app exists to serve your reflection, not to shape it.
                    </p>
                </div>
                
                <!-- Modes of Presence - Not features -->
                <div class="modes">
                    <div class="mode">
                        <a href="/write">
                            <h3>Write</h3>
                            <p>Capture thoughts in markdown. Private by default.</p>
                        </a>
                    </div>
                    
                    <div class="mode">
                        <a href="/read">
                            <h3>Read</h3>
                            <p>Return without judgment. Search gently.</p>
                        </a>
                    </div>
                    
                    <div class="mode">
                        <a href="/adjacent-view">
                            <h3>Adjacent</h3>
                            <p>See proximity between thoughts. No interpretation.</p>
                        </a>
                    </div>
                    
                    <div class="mode">
                        <a href="/sit">
                            <h3>Sit</h3>
                            <p>Quiet mental health companion. Brief check-ins or long silence.</p>
                        </a>
                    </div>
                </div>
            </div>
        </section>
        
        <!-- Philosophy - Collapsible by restraint -->
        <section class="philosophy">
            <div class="container">
                <p>Your content lives in markdown files. The app is optional.</p>
                <a href="/philosophy">Read the philosophy</a>
            </div>
        </section>
        
        <!-- Footer / Entry -->
        <footer class="footer">
            <button id="installBtn" class="install-btn hidden">Install App</button>
            <a href="/write" class="continue-link hidden" id="continueLink">Continue in browser</a>
        </footer>
        
        <script>
            // PWA Install - Quiet, optional
            let deferredPrompt;
            
            window.addEventListener('beforeinstallprompt', (e) => {
                e.preventDefault();
                deferredPrompt = e;
                const installBtn = document.getElementById('installBtn');
                const continueLink = document.getElementById('continueLink');
                installBtn.classList.remove('hidden');
                continueLink.classList.remove('hidden');
            });

            document.getElementById('installBtn').addEventListener('click', async () => {
                if (!deferredPrompt) return;
                deferredPrompt.prompt();
                const { outcome } = await deferredPrompt.userChoice;
                deferredPrompt = null;
                if (outcome === 'accepted') {
                    document.getElementById('installBtn').classList.add('hidden');
                    document.getElementById('continueLink').classList.add('hidden');
                }
            });
            
            // Service worker registration - Silent
            if ('serviceWorker' in navigator) {
                navigator.serviceWorker.register('/sw.js').catch(() => {
                    // Fail silently
                });
            }
        </script>
    </body>
</html>
