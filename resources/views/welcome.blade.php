<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Midnight Pilgrim</title>
        <link rel="manifest" href="/manifest.json">
        <meta name="theme-color" content="#0a0a0a">
        
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
                color: #d4d4d4;
                line-height: 1.7;
                font-weight: 300;
                -webkit-font-smoothing: antialiased;
            }
            
            /* Typography */
            h1, h2, h3 { font-weight: 300; line-height: 1.3; }
            h1 { font-size: 2.5rem; letter-spacing: -0.02em; }
            h2 { font-size: 1.25rem; letter-spacing: -0.01em; }
            h3 { font-size: 1rem; }
            
            p { color: #a3a3a3; }
            a { color: inherit; text-decoration: none; }
            
            /* Layout */
            .container {
                max-width: 42rem;
                margin: 0 auto;
                padding: 0 1.5rem;
            }
            
            .section {
                padding: 4rem 0;
            }
            
            /* Hero */
            .hero {
                min-height: 100vh;
                display: flex;
                flex-direction: column;
                justify-content: center;
                align-items: center;
                text-align: center;
                padding: 2rem 1.5rem;
            }
            
            .hero h1 {
                color: #f5f5f5;
                margin-bottom: 1rem;
            }
            
            .hero .tagline {
                font-size: 1.125rem;
                color: #d4d4d4;
                margin-bottom: 0.75rem;
                font-weight: 300;
            }
            
            .hero .principles {
                font-size: 0.75rem;
                color: #737373;
                letter-spacing: 0.1em;
                text-transform: uppercase;
                margin-bottom: 3rem;
            }
            
            .hero .principles::before {
                content: '';
                display: block;
                width: 2rem;
                height: 1px;
                background: #404040;
                margin: 2rem auto 1.5rem;
            }
            
            /* Button */
            .btn {
                display: inline-block;
                padding: 0.875rem 2.5rem;
                background: transparent;
                border: 1px solid #525252;
                color: #d4d4d4;
                font-size: 0.9375rem;
                font-weight: 400;
                letter-spacing: 0.02em;
                transition: all 0.3s ease;
                cursor: pointer;
            }
            
            .btn:hover {
                border-color: #8b8baf;
                color: #f5f5f5;
                background: rgba(139, 139, 175, 0.05);
            }
            
            /* What This Is */
            .intro {
                text-align: center;
                max-width: 36rem;
                margin: 0 auto 4rem;
            }
            
            .intro p {
                font-size: 1rem;
                line-height: 1.8;
                color: #a3a3a3;
            }
            
            /* Modes */
            .modes {
                display: grid;
                gap: 2rem;
                margin-bottom: 4rem;
            }
            
            .mode {
                border-left: 1px solid #262626;
                padding-left: 1.5rem;
                transition: border-color 0.3s ease;
            }
            
            .mode:hover {
                border-left-color: #525252;
            }
            
            .mode h3 {
                color: #f5f5f5;
                margin-bottom: 0.5rem;
            }
            
            .mode p {
                font-size: 0.9375rem;
                line-height: 1.7;
            }
            
            .mode a {
                display: block;
                color: inherit;
            }
            
            .mode a:hover h3 {
                color: #8b8baf;
            }
            
            /* Philosophy */
            .philosophy {
                text-align: center;
                padding: 3rem 0;
                border-top: 1px solid #1a1a1a;
            }
            
            .philosophy p {
                font-size: 0.9375rem;
                font-style: italic;
                margin-bottom: 1rem;
                color: #737373;
            }
            
            .philosophy a {
                font-size: 0.875rem;
                color: #8b8baf;
                border-bottom: 1px solid transparent;
                transition: border-color 0.3s ease;
            }
            
            .philosophy a:hover {
                border-bottom-color: #8b8baf;
            }
            
            /* Footer */
            .footer {
                text-align: center;
                padding: 3rem 0 4rem;
            }
            
            .install-btn {
                display: inline-flex;
                align-items: center;
                gap: 0.5rem;
                padding: 0.75rem 2rem;
                background: #171717;
                border: 1px solid #262626;
                color: #a3a3a3;
                font-size: 0.875rem;
                transition: all 0.3s ease;
                margin-bottom: 1rem;
            }
            
            .install-btn:hover {
                border-color: #404040;
                background: #1a1a1a;
            }
            
            .install-btn.hidden {
                display: none;
            }
            
            /* Responsive */
            @media (min-width: 48rem) {
                h1 { font-size: 3.5rem; }
                .hero .tagline { font-size: 1.375rem; }
                .modes { grid-template-columns: repeat(2, 1fr); gap: 3rem 2rem; }
            }
            
            @media (min-width: 64rem) {
                .container { max-width: 48rem; }
            }
        </style>
    </head>
    
    <body>
        <!-- Hero / Arrival -->
        <section class="hero">
            <div>
                <h1>Midnight Pilgrim</h1>
                <p class="tagline">A quiet place for your thoughts</p>
                <p class="principles">Silence-first · Local-first · No tracking</p>
                <a href="/write" class="btn">Begin</a>
            </div>
        </section>
        
        <!-- What This Is -->
        <section class="section">
            <div class="container">
                <div class="intro">
                    <p>
                        Midnight Pilgrim is a local-first writing space. Your thoughts live as markdown files 
                        on your machine. There is no optimization, no engagement tracking, no persuasion. 
                        Everything is private by default. The app exists to serve your reflection, not to shape it.
                    </p>
                </div>
                
                <!-- Modes of Presence -->
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
        
        <!-- Philosophy -->
        <section class="philosophy">
            <div class="container">
                <p>Your content lives in markdown files. The app is optional.</p>
                <a href="/philosophy">Read the philosophy</a>
            </div>
        </section>
        
        <!-- Footer / Entry -->
        <footer class="footer">
            <button id="installBtn" class="install-btn hidden">Install App</button>
        </footer>
        
        <script>
            // PWA Install Prompt (no alerts, quiet behavior)
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
        </script>
    </body>
</html>
