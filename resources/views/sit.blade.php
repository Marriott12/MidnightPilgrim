<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Sit — Midnight Pilgrim</title>
    <link rel="manifest" href="/manifest.json">
    <style>
        * {
            margin: 0;
            padding: 0;
            box-sizing: border-box;
        }

        html {
            scroll-behavior: smooth;
        }

        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #a3a3a3;
            line-height: 1.7;
            font-weight: 300;
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            -webkit-font-smoothing: antialiased;
        }

        /* Navigation */
        nav {
            padding: 1.25rem 1.5rem;
            border-bottom: 1px solid #1a1a1a;
            display: flex;
            justify-content: space-between;
            align-items: center;
        }

        nav a {
            color: #666;
            text-decoration: none;
            font-size: 0.875rem;
            transition: color 0.2s;
        }

        nav a:hover {
            color: #a3a3a3;
        }

        nav .home {
            color: #888;
            font-weight: 400;
        }

        nav .links {
            display: flex;
            gap: 1.5rem;
        }

        /* Main Container - Intentional emptiness */
        .container {
            flex: 1;
            max-width: 32rem;
            margin: 0 auto;
            padding: 8rem 2rem 4rem;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
        }

        /* Prompts and text */
        .prompt {
            font-size: 1.125rem;
            color: #737373;
            line-height: 1.8;
            text-align: center;
            margin-bottom: 3rem;
            max-width: 28rem;
        }

        .subtext {
            font-size: 0.875rem;
            color: #525252;
            text-align: center;
            margin-top: -1.5rem;
            margin-bottom: 3rem;
            font-style: italic;
        }

        /* Buttons - minimal, calm */
        .actions {
            display: flex;
            gap: 1rem;
            margin-top: 1rem;
        }

        button {
            background: transparent;
            border: 1px solid #262626;
            color: #737373;
            padding: 0.75rem 2rem;
            font-size: 0.9375rem;
            font-weight: 300;
            cursor: pointer;
            transition: all 0.3s ease;
            letter-spacing: 0.01em;
        }

        button:hover {
            border-color: #525252;
            color: #a3a3a3;
        }

        button:disabled {
            opacity: 0.4;
            cursor: not-allowed;
        }

        /* Form inputs */
        input[type="number"],
        textarea {
            background: transparent;
            border: none;
            border-bottom: 1px solid #262626;
            color: #d4d4d4;
            font-family: inherit;
            font-size: 1rem;
            font-weight: 300;
            text-align: center;
            padding: 0.75rem 0;
            outline: none;
            transition: border-color 0.3s;
            width: 100%;
            max-width: 20rem;
        }

        input[type="number"]:focus,
        textarea:focus {
            border-bottom-color: #525252;
        }

        textarea {
            resize: none;
            min-height: 8rem;
            text-align: left;
            line-height: 1.7;
            margin-bottom: 2rem;
        }

        ::placeholder {
            color: #404040;
        }

        /* Response area */
        .response {
            max-width: 28rem;
            margin-top: 3rem;
            padding: 2rem 0;
            border-top: 1px solid #1a1a1a;
        }

        .response-text {
            color: #888;
            font-size: 0.9375rem;
            line-height: 1.8;
            margin-bottom: 1rem;
        }

        .response-meta {
            font-size: 0.8125rem;
            color: #525252;
            font-style: italic;
        }

        /* States */
        .hidden {
            display: none;
        }

        /* Mobile */
        @media (max-width: 40rem) {
            .container {
                padding: 4rem 1.5rem 3rem;
            }

            .prompt {
                font-size: 1rem;
            }

            .actions {
                flex-direction: column;
                width: 100%;
                max-width: 16rem;
            }

            button {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <!-- Navigation -->
    <nav>
        <a href="/" class="home">Midnight Pilgrim</a>
        <div class="links">
            <a href="/write">Write</a>
            <a href="/read">Read</a>
            <a href="/adjacent-view">Adjacent</a>
        </div>
    </nav>

    <div class="container">
        <!-- Initial State - Choose mode -->
        <div id="initial-state">
            <div class="prompt">You don't have to do anything here.</div>
            <div class="subtext">Would you like company or quiet?</div>
            <div class="actions">
                <button onclick="enterMode('company')">Company</button>
                <button onclick="enterMode('quiet')">Quiet</button>
            </div>
        </div>
        
        <!-- Quiet Mode (Isolation - no AI, just space) -->
        <div id="quiet-mode" class="hidden">
            <div class="prompt">This space is yours. Completely private.</div>
            <div class="subtext">No AI. No storage. Just you.</div>
            <form id="quiet-form" onsubmit="handleQuiet(event)">
                <textarea 
                    name="input" 
                    placeholder="Write anything. It stays here."
                    autofocus
                ></textarea>
                <div class="actions">
                    <button type="button" onclick="clearQuiet()">Clear</button>
                    <button type="button" onclick="resetState()">Close</button>
                </div>
            </form>
        </div>
        
        <!-- Company Mode (Existing check-in flow) -->
        <div id="company-mode" class="hidden">
            <div class="prompt">How heavy did today feel?</div>
            <div class="subtext">1 = light, 5 = heavy. Saved privately, never shared.</div>
            <form id="checkin-form" onsubmit="handleCheckin(event)">
                <input 
                    type="number" 
                    name="intensity" 
                    min="1" 
                    max="5" 
                    placeholder="1–5"
                    style="max-width: 8rem;"
                    autofocus
                >
                <div class="actions">
                    <button type="submit">Save</button>
                    <button type="button" onclick="resetState()">Skip</button>
                </div>
            </form>
        </div>

        <!-- Saved State -->
        <div id="saved-state" class="hidden">
            <div class="prompt">Saved quietly.</div>
            <div class="subtext">Always private. Never shared.</div>
            <div class="actions">
                <button onclick="window.location.href='/write'">Continue</button>
            </div>
        </div>
    </div>

    <script>
        // PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js').catch(() => {});
        }

        const states = {
            initial: document.getElementById('initial-state'),
            quiet: document.getElementById('quiet-mode'),
            company: document.getElementById('company-mode'),
            saved: document.getElementById('saved-state')
        };

        function hideAll() {
            Object.values(states).forEach(el => el.classList.add('hidden'));
        }

        function showState(name) {
            hideAll();
            states[name].classList.remove('hidden');
        }

        function enterMode(mode) {
            showState(mode);
            
            if (mode === 'company') {
                document.querySelector('#checkin-form input').focus();
            } else if (mode === 'quiet') {
                document.querySelector('#quiet-form textarea').focus();
            }
        }

        function resetState() {
            showState('initial');
            
            // Clear forms
            document.getElementById('quiet-form').reset();
            document.getElementById('checkin-form').reset();
        }
        
        // Quiet mode: just a textarea, no storage, no AI
        function handleQuiet(e) {
            e.preventDefault();
            // Intentionally do nothing - just provide space
        }
        
        function clearQuiet() {
            document.querySelector('#quiet-form textarea').value = '';
            document.querySelector('#quiet-form textarea').focus();
        }

        async function handleCheckin(e) {
            e.preventDefault();
            
            const input = e.target.input.value.trim();
            if (!input) return;

            const responseDiv = document.getElementById('reflective-response');
            const submitBtn = e.target.querySelector('button[type="submit"]');
            
            submitBtn.disabled = true;
            submitBtn.textContent = '…';

            try {
                const response = await fetch('/sit/begin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ input })
                });

                const data = await response.json();
                
                if (data.text || data.response) {
                    responseDiv.innerHTML = `
                        <div class="response">
                            <div class="response-text">${data.text || data.response}</div>
                            <div class="response-meta">This stays between you and the quiet</div>
                        </div>
                    `;
                    responseDiv.classList.remove('hidden');
                }

                e.target.input.value = '';
                
            } catch (error) {
                // Fail quietly - no alert
                console.error('Error:', error);
            } finally {
                submitBtn.disabled = false;
                submitBtn.textContent = 'Send';
            }
        }

        async function handleCheckin(e) {
            e.preventDefault();
            
            const intensity = parseInt(e.target.intensity.value);
            if (!intensity || intensity < 1 || intensity > 5) return;

            const submitBtn = e.target.querySelector('button[type="submit"]');
            submitBtn.disabled = true;
            submitBtn.textContent = '…';

            try {
                await fetch('/sit/check-in', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        intensity,
                        note: ''
                    })
                });

                // Show saved state
                showState('saved');
                
            } catch (error) {
                console.error('Error:', error);
                submitBtn.disabled = false;
                submitBtn.textContent = 'Save';
            }
        }

        // Escape to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                window.location.href = '/write';
            }
        });
    </script>
</body>
</html>
