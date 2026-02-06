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
            max-width: 680px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            width: 100%;
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
    <nav>
        <div>
            <a href="/" style="color: #999; font-weight: 500;">Midnight Pilgrim</a>
        </div>
        <div style="display: flex; gap: 1.5rem;">
            <a href="/write" class="current">Write</a>
            <a href="/read">Read</a>
            <a href="/adjacent-view">Adjacent</a>
            <a href="/sit">Sit</a>
        </div>
    </nav>

    <div class="container">
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
        </form>
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
    </script>
</body>
</html>
