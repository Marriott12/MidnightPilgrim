<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <title>{{ isset($isEditing) && $isEditing ? 'Edit' : 'Write' }} &mdash; Midnight Pilgrim</title>
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

        /* Silence-first design: no header clutter */
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
            flex: 1;
            max-width: 1200px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            width: 100%;
            display: flex;
            gap: 2rem;
        }

        .editor-pane {
            flex: 1;
        }

        .preview-pane {
            flex: 1;
            border-left: 1px solid #1a1a1a;
            padding-left: 2rem;
            display: none;
        }

        .preview-pane.active {
            display: block;
        }

        .preview-content {
            color: #999;
            font-size: 1.05rem;
            line-height: 1.8;
        }

        .preview-content h1, .preview-content h2, .preview-content h3 {
            color: #c4c4c4;
            font-weight: 400;
            margin: 2rem 0 1rem;
        }

        .preview-content h1 { font-size: 1.8rem; }
        .preview-content h2 { font-size: 1.4rem; }
        .preview-content h3 { font-size: 1.2rem; }

        .preview-content blockquote {
            border-left: 2px solid #333;
            padding-left: 1rem;
            margin: 1.5rem 0;
            color: #8b8baf;
            font-style: italic;
        }

        .preview-content code {
            background: #0f0f0f;
            padding: 0.2rem 0.4rem;
            border-radius: 2px;
            font-size: 0.9em;
        }

        .preview-content pre {
            background: #0f0f0f;
            padding: 1rem;
            border-radius: 2px;
            overflow-x: auto;
            margin: 1rem 0;
        }

        .preview-content ul, .preview-content ol {
            margin: 1rem 0;
            padding-left: 2rem;
        }

        .preview-content li {
            margin: 0.5rem 0;
        }

        .preview-content a {
            color: #8b8baf;
            text-decoration: none;
            border-bottom: 1px solid #333;
        }

        .toggle-preview {
            background: transparent;
            border: 1px solid #333;
            color: #666;
            padding: 0.4rem 0.8rem;
            font-size: 0.85rem;
            cursor: pointer;
            border-radius: 2px;
            margin-bottom: 1rem;
        }

        .toggle-preview:hover {
            border-color: #666;
            color: #999;
        }

        .toggle-preview.active {
            border-color: #8b8baf;
            color: #8b8baf;
        }

        /* Minimal inputs - like opening a notebook */
        input[type="text"],
        textarea {
            width: 100%;
            background: transparent;
            border: none;
            color: #c4c4c4;
            font-family: inherit;
            outline: none;
        }

        input[type="text"] {
            font-size: 1.3rem;
            font-weight: 300;
            padding-bottom: 0.5rem;
            border-bottom: 1px solid #1a1a1a;
            margin-bottom: 1.5rem;
        }

        textarea {
            min-height: 400px;
            font-size: 1.05rem;
            line-height: 1.7;
            resize: vertical;
        }

        input[type="text"]::placeholder,
        textarea::placeholder {
            color: #333;
        }

        /* Quiet action area - no prominent buttons */
        .actions {
            margin-top: 2rem;
            padding-top: 1.5rem;
            border-top: 1px solid #1a1a1a;
            display: flex;
            gap: 1rem;
            align-items: center;
        }
        
        /* Silence mode: hide navigation and counts */
        body.silence-mode nav,
        body.silence-mode #word-count,
        body.silence-mode #char-count,
        body.silence-mode .actions {
            display: none;
        }
        
        body.silence-mode .container {
            padding-top: 2rem;
        }
        
        .silence-toggle {
            position: fixed;
            top: 1rem;
            right: 1rem;
            background: transparent;
            border: 1px solid #222;
            color: #444;
            padding: 0.4rem 0.8rem;
            font-size: 0.75rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 2px;
            z-index: 1000;
        }
        
        .silence-toggle:hover {
            border-color: #333;
            color: #666;
        }
        
        /* Silence feature controls */
        .silence-controls {
            opacity: 0.3;
            transition: opacity 0.3s;
        }
        
        .silence-controls:hover {
            opacity: 1;
        }
        
        .silence-btn {
            background: transparent;
            border: 1px solid #1a1a1a;
            color: #444;
            padding: 0.6rem;
            font-size: 1.2rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 2px;
            width: 3rem;
            height: 3rem;
            display: flex;
            align-items: center;
            justify-content: center;
        }
        
        .silence-btn:hover {
            border-color: #333;
            background: #0d0d0d;
        }
        
        .silence-btn.active {
            border-color: #8b8baf;
            color: #8b8baf;
        }

        button {
            background: transparent;
            border: 1px solid #333;
            color: #999;
            padding: 0.6rem 1.2rem;
            font-size: 0.9rem;
            cursor: pointer;
            transition: all 0.2s;
            border-radius: 2px;
        }

        button:hover {
            border-color: #666;
            color: #c4c4c4;
        }

        button.primary {
            border-color: #666;
            color: #c4c4c4;
        }

        .hint {
            font-size: 0.85rem;
            color: #444;
            margin-left: auto;
        }

        /* Empty state: intentional, not broken */
        .empty-state {
            text-align: center;
            color: #444;
            padding: 3rem 1rem;
            font-size: 0.95rem;
        }

        /* Mobile responsive */
        @media (max-width: 640px) {
            .container {
                padding: 2rem 1rem;
            }

            textarea {
                min-height: 300px;
                font-size: 1rem;
            }

            .actions {
                flex-direction: column;
                align-items: stretch;
            }

            .hint {
                margin-left: 0;
                text-align: center;
            }
        }

        /* Night-friendly: no bright whites */
        ::selection {
            background: #222;
            color: #eee;
        }

        /* Accessibility */
        .sr-only {
            position: absolute;
            width: 1px;
            height: 1px;
            padding: 0;
            margin: -1px;
            overflow: hidden;
            clip: rect(0, 0, 0, 0);
            white-space: nowrap;
            border-width: 0;
        }

        /* Keyboard focus indicator */
        .user-is-tabbing *:focus {
            outline: 2px solid #666;
            outline-offset: 2px;
        }
    </style>
</head>
<body>
    <button class="silence-toggle" onclick="toggleSilence()">Silence</button>
    
    <nav>
        <div>
            <a href="/" style="color: #999; font-weight: 500;">Midnight Pilgrim</a>
            <span style="margin-left: 1.5rem; font-size: 0.85rem; color: #444;">{{ now()->format('F j') }}</span>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <a href="/write" class="current">Write</a>
            <a href="/read">Read</a>
            <a href="/adjacent-view">Adjacent</a>
            <a href="/sit">Sit</a>
        </div>
    </nav>

    <!-- Silence Features -->
    <div class="silence-controls" style="position: fixed; bottom: 1rem; left: 1rem; z-index: 100; display: flex; gap: 0.5rem; flex-direction: column;">
        <button type="button" class="silence-btn" onclick="toggleWriteOnly()" title="Write without AI processing">
            <span id="write-only-indicator">✍️</span>
        </button>
        <button type="button" class="silence-btn" onclick="toggleNoArchive()" title="Do not keep this">
            <span id="no-archive-indicator">🔥</span>
        </button>
        <button type="button" class="silence-btn" onclick="openStillness()" title="Timed stillness">
            <span>🕯️</span>
        </button>
    </div>
    
    <!-- Timed Stillness Overlay -->
    <div id="stillness-overlay" class="hidden" style="position: fixed; inset: 0; background: #0a0a0a; z-index: 9999; display: flex; align-items: center; justify-content: center;">
        <div style="text-align: center; color: #444;">
            <div id="stillness-text" style="font-size: 0.9rem; margin-bottom: 2rem;">Choose stillness duration</div>
            <div style="display: flex; gap: 1.5rem; justify-content: center; margin-bottom: 2rem;" id="stillness-choices">
                <button onclick="enterStillness(60)" class="silence-btn">1 min</button>
                <button onclick="enterStillness(180)" class="silence-btn">3 min</button>
                <button onclick="enterStillness(300)" class="silence-btn">5 min</button>
            </div>
            <button onclick="closeStillness()" class="silence-btn" style="font-size: 0.8rem; color: #333;">Leave quietly</button>
        </div>
    </div>

    <div class="container">
        <div class="editor-pane">
            <button type="button" class="toggle-preview" onclick="togglePreview()">Preview</button>
            
            <form method="POST" action="{{ isset($isEditing) && $isEditing ? '/notes/' . $note->slug : '/notes/store' }}" aria-label="{{ isset($isEditing) && $isEditing ? 'Edit note' : 'New note' }}">
                @csrf
                @if(isset($isEditing) && $isEditing)
                @method('PUT')
            @endif
            
            <label for="title" class="sr-only">Note title</label>
            <input 
                type="text"
                id="title"
                name="title" 
                value="{{ $note->title ?? '' }}"
                placeholder="Title (optional)"
                aria-placeholder="Title (optional)"
                tabindex="0"
            >
            
            <label for="body" class="sr-only">Write your note</label>
            <textarea 
                id="body"
                name="body" 
                placeholder="Like opening a notebook at night..."
                aria-placeholder="Like opening a notebook at night"
                tabindex="0"
            >{{ $body ?? '' }}</textarea>

            <div style="text-align: right; margin-top: 0.5rem; font-size: 0.8rem; color: #333;">
                <span id="word-count">0 words</span> &middot; <span id="char-count">0 characters</span>
            </div>

            <div class="actions">
                <button type="submit" class="primary">{{ isset($isEditing) && $isEditing ? 'Update' : 'Save' }}</button>
                @if(isset($isEditing) && $isEditing)
                    <a href="/view/notes/{{ $note->slug }}" style="color: #666; text-decoration: none; padding: 0.6rem 1.2rem;">Cancel</a>
                @else
                    <button type="button" onclick="document.querySelector('textarea').value = ''; document.querySelector('#title').value = '';">Clear</button>
                @endif
                <span class="hint">Defaults to private. Mark quotes with &gt;</span>
                <span class="hint" style="margin-left: 1rem; color: #333;">⌘S to save • ⌘K to clear</span>
            </div>
            
                <!-- Hidden field to pass edit state to JavaScript -->
                <input type="hidden" id="is-editing" value="{{ isset($isEditing) && $isEditing ? '1' : '0' }}">
                
                <!-- Silence feature flags -->
                <input type="hidden" id="write-only-input" name="write_only" value="0">
                <input type="hidden" id="no-archive-input" name="no_archive" value="0">
            </form>
        </div>
        
        <div class="preview-pane" id="preview-pane">
            <div style="color: #555; font-size: 0.85rem; margin-bottom: 1.5rem; text-transform: uppercase; letter-spacing: 0.05em;">Preview</div>
            <div id="preview-content" class="preview-content">
                <div style="color: #333; font-style: italic;">Start writing to see preview...</div>
            </div>
        </div>
    </div>

    <script>
        // PWA: Register service worker
        if ('serviceWorker' in navigator) {
            navigator.serviceWorker.register('/sw.js');
        }

        // Auto-save to localStorage (offline resilience)
        const titleInput = document.querySelector('#title');
        const textarea = document.querySelector('#body');
        const AUTOSAVE_KEY_TITLE = 'midnight_pilgrim_draft_title';
        const AUTOSAVE_KEY_BODY = 'midnight_pilgrim_draft_body';

        // Only restore draft if not editing
        const isEditing = document.getElementById('is-editing').value === '1';
        if (!isEditing) {
            const draftTitle = localStorage.getItem(AUTOSAVE_KEY_TITLE);
            const draftBody = localStorage.getItem(AUTOSAVE_KEY_BODY);
            if (draftTitle && !titleInput.value) {
                titleInput.value = draftTitle;
            }
            if (draftBody && !textarea.value) {
                textarea.value = draftBody;
            }
        }

        // Save draft on input (debounced) - only when not editing
        let saveTimeout;
        const autosave = () => {
            if (!isEditing) {
                clearTimeout(saveTimeout);
                saveTimeout = setTimeout(() => {
                    localStorage.setItem(AUTOSAVE_KEY_TITLE, titleInput.value);
                    localStorage.setItem(AUTOSAVE_KEY_BODY, textarea.value);
                }, 500);
            }
        };
        titleInput.addEventListener('input', autosave);
        textarea.addEventListener('input', autosave);

        // Clear draft on submit
        document.querySelector('form').addEventListener('submit', () => {
            localStorage.removeItem(AUTOSAVE_KEY_TITLE);
            localStorage.removeItem(AUTOSAVE_KEY_BODY);
        });

        // Keyboard shortcut: Ctrl/Cmd+Enter to submit
        textarea.addEventListener('keydown', (e) => {
            if ((e.ctrlKey || e.metaKey) && e.key === 'Enter') {
                e.preventDefault();
                document.querySelector('form').submit();
            }
        });

        // Accessibility helper: focus outline for keyboard users
        document.body.addEventListener('keydown', function onFirstTab(e) {
            if (e.key === 'Tab') {
                document.documentElement.classList.add('user-is-tabbing');
                document.body.removeEventListener('keydown', onFirstTab);
            }
        });

        // Keyboard shortcuts (silence-friendly)
        document.addEventListener('keydown', (e) => {
            // Cmd/Ctrl + S to save (quiet, no alert)
            if ((e.metaKey || e.ctrlKey) && e.key === 's') {
                e.preventDefault();
                document.querySelector('form').requestSubmit();
            }
            
            // Cmd/Ctrl + K to clear (quiet confirmation)
            if ((e.metaKey || e.ctrlKey) && e.key === 'k') {
                e.preventDefault();
                if (titleInput.value.trim() || textarea.value.trim()) {
                    // Quiet confirmation (no alert)
                    const clear = confirm('Clear this draft?');
                    if (clear) {
                        titleInput.value = '';
                        textarea.value = '';
                        localStorage.removeItem(AUTOSAVE_KEY_TITLE);
                        localStorage.removeItem(AUTOSAVE_KEY_BODY);
                        titleInput.focus();
                    }
                }
            }
            
            // Escape to blur (release focus quietly)
            if (e.key === 'Escape') {
                textarea.blur();
            }
        });

        // Tab support in textarea (4 spaces)
        textarea.addEventListener('keydown', (e) => {
            if (e.key === 'Tab') {
                e.preventDefault();
                const start = textarea.selectionStart;
                const end = textarea.selectionEnd;
                textarea.value = textarea.value.substring(0, start) + '    ' + textarea.value.substring(end);
                textarea.selectionStart = textarea.selectionEnd = start + 4;
            }
        });

        // Markdown preview toggle
        function togglePreview() {
            const previewPane = document.getElementById('preview-pane');
            const toggleBtn = document.querySelector('.toggle-preview');
            
            if (previewPane.classList.contains('active')) {
                previewPane.classList.remove('active');
                toggleBtn.classList.remove('active');
            } else {
                previewPane.classList.add('active');
                toggleBtn.classList.add('active');
                updatePreview();
            }
        }

        function updatePreview() {
            const previewPane = document.getElementById('preview-pane');
            if (!previewPane.classList.contains('active')) return;
            
            const body = textarea.value;
            const previewContent = document.getElementById('preview-content');
            
            if (!body.trim()) {
                previewContent.innerHTML = '<div style="color: #333; font-style: italic;">Start writing to see preview...</div>';
                return;
            }
            
            // Send to backend for rendering
            fetch('/api/preview', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': document.querySelector('[name="_token"]').value
                },
                body: JSON.stringify({ content: body })
            })
            .then(res => res.json())
            .then(data => {
                previewContent.innerHTML = data.html;
            })
            .catch(() => {
                // Fallback to simple line breaks
                previewContent.innerHTML = body.replace(/\n/g, '<br>');
            });
        }

        // Update preview on typing (debounced)
        let previewTimeout;
        textarea.addEventListener('input', () => {
            clearTimeout(previewTimeout);
            previewTimeout = setTimeout(updatePreview, 500);
            updateWordCount();
        });

        // Word and character counter
        function updateWordCount() {
            const text = textarea.value;
            const words = text.trim() ? text.trim().split(/\s+/).length : 0;
            const chars = text.length;
            
            document.getElementById('word-count').textContent = `${words} word${words !== 1 ? 's' : ''}`;
            document.getElementById('char-count').textContent = `${chars} character${chars !== 1 ? 's' : ''}`;
        }

        // Initialize word count
        updateWordCount();
        
        // Silence mode toggle
        function toggleSilence() {
            document.body.classList.toggle('silence-mode');
            const isSilent = document.body.classList.contains('silence-mode');
            localStorage.setItem('silenceMode', isSilent ? 'true' : 'false');
            
            // Update button text
            document.querySelector('.silence-toggle').textContent = isSilent ? 'Return' : 'Silence';
        }
        
        // Restore silence mode from localStorage
        if (localStorage.getItem('silenceMode') === 'true') {
            document.body.classList.add('silence-mode');
            document.querySelector('.silence-toggle').textContent = 'Return';
        }
        
        // Silence Features
        let writeOnlyMode = false;
        let noArchiveMode = false;
        let stillnessTimer = null;
        let escPressTime = null;
        
        function toggleWriteOnly() {
            writeOnlyMode = !writeOnlyMode;
            const btn = document.getElementById('write-only-indicator').parentElement;
            btn.classList.toggle('active');
            document.getElementById('write-only-input').value = writeOnlyMode ? '1' : '0';
            localStorage.setItem('writeOnlyMode', writeOnlyMode ? 'true' : 'false');
        }
        
        function toggleNoArchive() {
            noArchiveMode = !noArchiveMode;
            const btn = document.getElementById('no-archive-indicator').parentElement;
            btn.classList.toggle('active');
            document.getElementById('no-archive-input').value = noArchiveMode ? '1' : '0';
            localStorage.setItem('noArchiveMode', noArchiveMode ? 'true' : 'false');
        }
        
        function openStillness() {
            document.getElementById('stillness-overlay').classList.remove('hidden');
        }
        
        function closeStillness() {
            document.getElementById('stillness-overlay').classList.add('hidden');
            if (stillnessTimer) {
                clearTimeout(stillnessTimer);
                stillnessTimer = null;
            }
        }
        
        function enterStillness(seconds) {
            // Hide choices, show inert state
            document.getElementById('stillness-choices').style.display = 'none';
            document.getElementById('stillness-text').textContent = '';
            
            // After duration, optionally show closing line
            stillnessTimer = setTimeout(() => {
                if (localStorage.getItem('stillnessClosingLine') === 'true') {
                    document.getElementById('stillness-text').textContent = 'Still here.';
                    setTimeout(closeStillness, 2000);
                } else {
                    closeStillness();
                }
            }, seconds * 1000);
        }
        
        // Panic Exit: Long-press ESC to immediately clear and exit
        document.addEventListener('keydown', (e) => {
            if (e.key === 'Escape') {
                if (!escPressTime) {
                    escPressTime = Date.now();
                }
            }
        });
        
        document.addEventListener('keyup', (e) => {
            if (e.key === 'Escape' && escPressTime) {
                const duration = Date.now() - escPressTime;
                if (duration > 800) {
                    // Panic exit: clear screen immediately
                    document.body.innerHTML = '<div style="background: #0a0a0a; min-height: 100vh;"></div>';
                    setTimeout(() => window.location.href = '/', 100);
                }
                escPressTime = null;
            }
        });
        
        // Restore states from localStorage
        if (localStorage.getItem('writeOnlyMode') === 'true') {
            writeOnlyMode = true;
            document.getElementById('write-only-indicator').parentElement.classList.add('active');
            document.getElementById('write-only-input').value = '1';
        }
        if (localStorage.getItem('noArchiveMode') === 'true') {
            noArchiveMode = true;
            document.getElementById('no-archive-indicator').parentElement.classList.add('active');
            document.getElementById('no-archive-input').value = '1';
        }
    </script>
</body>
</html>
