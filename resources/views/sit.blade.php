<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Sit &mdash; Midnight Pilgrim</title>
    <link rel="manifest" href="/manifest.json">
    <style>
        body {
            font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', sans-serif;
            background: #0a0a0a;
            color: #cfcfcf;
            min-height: 100vh;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .frame {
            width: 100%;
            max-width: 720px;
            padding: 4rem 2rem;
            box-sizing: border-box;
            text-align: center;
        }

        .prompt {
            font-size: 1.2rem;
            color: #bfbfbf;
            margin-bottom: 3rem;
            line-height: 1.8;
        }

        .controls {
            display: flex;
            gap: 1rem;
            justify-content: center;
        }

        button {
            background: transparent;
            border: 1px solid #333;
            color: #cfcfcf;
            padding: 0.9rem 1.5rem;
            font-size: 1rem;
            border-radius: 4px;
            cursor: pointer;
        }

        .overlay {
            position: fixed;
            inset: 0;
            display: none;
            align-items: center;
            justify-content: center;
            background: rgba(0,0,0,0.7);
            padding: 2rem;
        }

        .overlay .card {
            background: #070707;
            padding: 2rem;
            max-width: 720px;
            width: 100%;
            text-align: left;
            border-radius: 6px;
        }

        .card p { font-size: 1rem; color: #ddd; }

        @media (max-width: 640px) {
            .frame { padding: 3rem 1rem; }
            .prompt { font-size: 1rem; }
        }
    </style>
</head>
<body>
    <nav style="position:absolute; top:0; left:0; right:0; padding:1rem; display:flex; justify-content:space-between;">
        <a href="/" style="color:#999; text-decoration:none;">Midnight Pilgrim</a>
        <div><a href="/write" style="color:#888; text-decoration:none; margin-right:1rem;">Write</a><a href="/read" style="color:#888; text-decoration:none">Read</a></div>
    </nav>

    <div class="frame" role="main">
        <div class="prompt">
            Sit. Breathe. This is a small, quiet place.
        </div>

        <div class="controls">
            <button id="begin" aria-label="Begin quiet moment">Begin</button>
            <button id="close" aria-label="Return to main views">Close</button>
        </div>
    </div>

    <div class="overlay" id="overlay" role="dialog" aria-modal="true" aria-labelledby="overlayText">
        <div class="card" id="overlayCard">
            <p id="overlayText" style="margin-bottom: 1.5rem;">…</p>
            
            <form id="checkInForm" style="margin-bottom: 1rem;">
                @csrf
                <label for="feeling" style="display: block; color: #999; font-size: 0.9rem; margin-bottom: 0.5rem;">How heavy (1–5, optional)</label>
                <input 
                    type="number" 
                    id="feeling" 
                    name="intensity" 
                    min="1" 
                    max="5" 
                    style="background: #0a0a0a; border: 1px solid #333; color: #cfcfcf; padding: 0.6rem; width: 80px; border-radius: 4px;"
                    aria-label="Rate heaviness from 1 to 5"
                />
                <textarea 
                    id="note" 
                    name="note" 
                    placeholder="Optional note (stays private)"
                    rows="3"
                    style="background: #0a0a0a; border: 1px solid #333; color: #cfcfcf; padding: 0.6rem; width: 100%; margin-top: 0.5rem; border-radius: 4px; font-family: inherit; resize: vertical;"
                    aria-label="Optional private note"
                ></textarea>
            </form>
            
            <div style="display: flex; gap: 0.5rem; justify-content: flex-end;">
                <button id="saveCheckIn" style="border-color: #555;">Save (Private)</button>
                <button id="overlayClose">Close</button>
            </div>
        </div>
    </div>

    <script>
        const begin = document.getElementById('begin');
        const close = document.getElementById('close');
        const overlay = document.getElementById('overlay');
        const overlayText = document.getElementById('overlayText');
        const overlayClose = document.getElementById('overlayClose');
        const saveCheckIn = document.getElementById('saveCheckIn');
        const feelingInput = document.getElementById('feeling');
        const noteInput = document.getElementById('note');
        const checkInForm = document.getElementById('checkInForm');

        // Focus trap elements
        const focusableElements = [feelingInput, noteInput, saveCheckIn, overlayClose];

        begin.addEventListener('click', async () => {
            try {
                const res = await fetch('/sit/begin', { method: 'POST', headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' } });
                const data = await res.json();
                overlayText.textContent = data?.text || 'You can simply be here.';
            } catch (e) {
                overlayText.textContent = 'You can simply be here.';
            }
            overlay.style.display = 'flex';
            // Focus first input for accessibility
            setTimeout(() => feelingInput.focus(), 100);
        });

        function hideOverlay() { 
            overlay.style.display = 'none'; 
            // Clear form
            feelingInput.value = '';
            noteInput.value = '';
            // Return focus to begin button
            begin.focus();
        }
        
        close.addEventListener('click', () => {
            window.location.href = '/write';
        });
        
        overlayClose.addEventListener('click', hideOverlay);

        // Save check-in (always private, stored in companion/)
        saveCheckIn.addEventListener('click', async () => {
            const intensity = parseInt(feelingInput.value);
            if (!intensity || intensity < 1 || intensity > 5) {
                alert('Please enter a number between 1 and 5.');
                return;
            }
            
            try {
                const formData = new FormData();
                formData.append('intensity', intensity);
                formData.append('note', noteInput.value || '');
                
                const res = await fetch('/sit/check-in', {
                    method: 'POST',
                    headers: { 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: formData
                });
                
                if (res.ok) {
                    overlayText.textContent = 'Saved quietly. Always private.';
                    feelingInput.value = '';
                    noteInput.value = '';
                    setTimeout(hideOverlay, 2000);
                }
            } catch (e) {
                overlayText.textContent = 'Could not save. You can close and try again.';
            }
        });

        // Keyboard navigation
        document.addEventListener('keydown', (e) => {
            // Escape closes overlay
            if (e.key === 'Escape' && overlay.style.display === 'flex') {
                hideOverlay();
            }
            
            // Focus trap when overlay is open
            if (overlay.style.display === 'flex' && e.key === 'Tab') {
                const firstElement = focusableElements[0];
                const lastElement = focusableElements[focusableElements.length - 1];
                
                if (e.shiftKey && document.activeElement === firstElement) {
                    e.preventDefault();
                    lastElement.focus();
                } else if (!e.shiftKey && document.activeElement === lastElement) {
                    e.preventDefault();
                    firstElement.focus();
                }
            }
        });
    </script>
</body>
</html>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>Sit &mdash; Midnight Pilgrim</title>
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
            display: flex;
            flex-direction: column;
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

        /* Large whitespace - intentional emptiness */
        .container {
            flex: 1;
            max-width: 500px;
            margin: 0 auto;
            padding: 8rem 2rem 4rem;
            width: 100%;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            text-align: center;
        }

        /* Sparse text - calm presence */
        .prompt {
            font-size: 1.1rem;
            color: #888;
            line-height: 1.8;
            margin-bottom: 3rem;
            max-width: 400px;
        }

        /* Minimal input - if needed */
        input[type="text"],
        input[type="number"],
        textarea.sit-input {
            background: transparent;
            border: none;
            border-bottom: 1px solid #333;
            color: #c4c4c4;
            font-family: inherit;
            font-size: 1.05rem;
            text-align: center;
            padding: 0.8rem 0;
            outline: none;
            transition: border-color 0.2s;
            width: 100%;
            max-width: 300px;
            margin-bottom: 2rem;
        }

        input[type="text"]:focus,
        input[type="number"]:focus,
        textarea.sit-input:focus {
            border-bottom-color: #666;
        }

        textarea.sit-input {
            resize: none;
            min-height: 120px;
            text-align: left;
            line-height: 1.7;
        }

        /* Only two buttons: begin and close */
        .actions {
            display: flex;
            gap: 1.5rem;
            margin-top: 2rem;
        }

        button {
            background: transparent;
            border: 1px solid #333;
            color: #666;
            padding: 0.7rem 1.8rem;
            font-size: 0.95rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 2px;
        }

        button:hover {
            border-color: #666;
            color: #999;
        }

        button.primary {
            border-color: #555;
            color: #888;
        }

        button.primary:hover {
            border-color: #888;
            color: #c4c4c4;
        }

        /* Reflection display - when companion responds */
        .reflection {
            max-width: 450px;
            color: #888;
            font-size: 1rem;
            line-height: 1.8;
            margin: 2rem 0;
            padding: 2rem;
            border-left: 2px solid #1a1a1a;
            text-align: left;
        }

        .reflection-meta {
            font-size: 0.85rem;
            color: #555;
            margin-top: 1rem;
            font-style: italic;
        }

        /* Mode indicator - subtle */
        .mode {
            position: absolute;
            top: 1.5rem;
            right: 1.5rem;
            font-size: 0.8rem;
            color: #444;
        }

        /* Mobile adjustments */
        @media (max-width: 640px) {
            .container {
                padding: 4rem 1.5rem 3rem;
            }

            .prompt {
                font-size: 1rem;
            }

            .actions {
                flex-direction: column;
                width: 100%;
                max-width: 240px;
            }

            button {
                width: 100%;
            }
        }

        ::selection {
            background: #222;
            color: #eee;
        }

        /* Hidden by default */
        .hidden {
            display: none;
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
            <a href="/sit" class="current">Sit</a>
        </div>
    </nav>

    <div class="mode">Companion</div>

    <div class="container">
        <!-- Mode Selection (Initial) -->
        <div id="mode-select">
            <div class="prompt">
                How would you like to sit?
            </div>
            <div class="actions">
                <button class="primary" onclick="selectMode('reflective')">Reflective</button>
                <button onclick="selectMode('checkin')">Brief Check-In</button>
            </div>
        </div>

        <!-- Mode A: Reflective (User-Invoked) -->
        <div id="reflective-mode" class="hidden">
            <div class="prompt" id="reflective-prompt">
                I'm here. You can write, or you can sit.
            </div>
            
            <form id="reflective-form" onsubmit="handleReflective(event)">
                <textarea 
                    class="sit-input" 
                    name="input" 
                    placeholder="What's on your mind?"
                    autofocus
                ></textarea>
                
                <div class="actions">
                    <button type="submit" class="primary">Send</button>
                    <button type="button" onclick="closeSit()">Close</button>
                </div>
            </form>

            <!-- Response area -->
            <div id="reflective-response" class="hidden"></div>
        </div>

        <!-- Mode B: Check-In (Brief, Optional) -->
        <div id="checkin-mode" class="hidden">
            <div class="prompt">
                How heavy did today feel?
            </div>
            
            <form id="checkin-form" onsubmit="handleCheckin(event)">
                <input 
                    type="number" 
                    name="intensity" 
                    min="1" 
                    max="5" 
                    placeholder="1–5"
                    style="max-width: 120px;"
                    autofocus
                >
                
                <div class="actions">
                    <button type="submit" class="primary">Save</button>
                    <button type="button" onclick="closeSit()">Skip</button>
                </div>
            </form>
        </div>
    </div>

    <script>
        // PWA
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }

        function selectMode(mode) {
            document.getElementById('mode-select').classList.add('hidden');
            
            if (mode === 'reflective') {
                document.getElementById('reflective-mode').classList.remove('hidden');
                document.querySelector('#reflective-form textarea').focus();
            } else {
                document.getElementById('checkin-mode').classList.remove('hidden');
                document.querySelector('#checkin-form input').focus();
            }
        }

        function closeSit() {
            // Quiet close - no confirmation needed
            window.location.href = '/write';
        }

        async function handleReflective(e) {
            e.preventDefault();
            
            const input = e.target.input.value.trim();
            if (!input) return;

            // Simple fetch to companion endpoint (to be created)
            try {
                const response = await fetch('/api/companion/reflect', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ input })
                });

                const data = await response.json();
                
                // Show response
                const responseDiv = document.getElementById('reflective-response');
                if (data.response) {
                    responseDiv.innerHTML = `
                        <div class="reflection">
                            ${data.response}
                        </div>
                    `;
                    responseDiv.classList.remove('hidden');
                }

                // Clear input
                e.target.input.value = '';
                
            } catch (error) {
                // Fail quietly
                console.error('Companion error:', error);
            }
        }

        async function handleCheckin(e) {
            e.preventDefault();
            
            const intensity = parseInt(e.target.intensity.value);
            if (!intensity || intensity < 1 || intensity > 5) return;

            // Store check-in quietly
            try {
                await fetch('/api/companion/checkin', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': '{{ csrf_token() }}'
                    },
                    body: JSON.stringify({ 
                        mood: 'neutral', 
                        intensity 
                    })
                });

                // Close quietly after save
                setTimeout(() => closeSit(), 500);
                
            } catch (error) {
                // Fail quietly
                console.error('Check-in error:', error);
            }
        }

        // Escape to close
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                closeSit();
            }
        });
    </script>
</body>
</html>
