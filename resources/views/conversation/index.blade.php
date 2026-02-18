<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="theme-color" content="#0a0a0a">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>Conversation &mdash; Midnight Pilgrim</title>
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

        .container {
            flex: 1;
            max-width: 800px;
            margin: 0 auto;
            padding: 3rem 1.5rem;
            width: 100%;
            display: flex;
            flex-direction: column;
        }


    </style>
</head>
<body>
    <nav>
        <!-- Navigation content here -->
    </nav>
    <div class="container" aria-label="Conversation container">
        <div id="loading-indicator" style="display:none;text-align:center;margin:1rem 0;" aria-live="polite" aria-busy="true">Loading…</div>
        <div id="error-banner" style="display:none;color:#ff6b6b;text-align:center;margin:1rem 0;" role="alert"></div>
        <!-- Rest of conversation UI here -->
    </div>
</body>

        /* Resume gate */
        .resume-gate {
            text-align: center;
            padding: 4rem 2rem;
        }

        .resume-gate h1 {
            font-size: 1.4rem;
            font-weight: 400;
            color: #999;
            margin-bottom: 3rem;
        }

        .resume-gate .actions {
            display: flex;
            gap: 2rem;
            justify-content: center;
            flex-wrap: wrap;
        }

        .resume-gate button {
            padding: 0.75rem 2rem;
            background: transparent;
            border: 1px solid #333;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
        }

        .resume-gate button:hover {
            border-color: #666;
            color: #c4c4c4;
        }

        /* Conversation interface */
        .conversation-wrapper {
            flex: 1;
            display: flex;
            flex-direction: column;
        }

        .messages {
            flex: 1;
            overflow-y: auto;
            margin-bottom: 2rem;
            padding: 1rem 0;
        }

        .message {
            margin-bottom: 2rem;
            padding: 1rem;
            border-radius: 4px;
        }

        .message.user {
            background: #0f0f0f;
            border-left: 2px solid #333;
        }

        .message.assistant {
            background: transparent;
            color: #8b8baf;
            font-style: italic;
        }

        .message.silence {
            text-align: center;
            color: #444;
            font-size: 0.9rem;
        }

        .message-content {
            line-height: 1.7;
        }

        .message-time {
            font-size: 0.75rem;
            color: #444;
            margin-top: 0.5rem;
        }

        /* Input area */
        .input-area {
            border-top: 1px solid #1a1a1a;
            padding-top: 2rem;
        }

        .controls {
            display: flex;
            gap: 1rem;
            margin-bottom: 1rem;
            align-items: center;
        }

        .mode-switch {
            display: flex;
            gap: 0.5rem;
        }

        .mode-switch button {
            padding: 0.5rem 1rem;
            background: transparent;
            border: 1px solid #333;
            color: #666;
            cursor: pointer;
            font-size: 0.9rem;
            transition: all 0.2s;
        }

        .mode-switch button.active {
            border-color: #666;
            color: #c4c4c4;
        }

        .mode-switch button:hover {
            border-color: #666;
        }

        .special-buttons {
            display: flex;
            gap: 0.5rem;
            margin-left: auto;
        }

        .special-buttons button {
            padding: 0.5rem 1rem;
            background: transparent;
            border: 1px solid #222;
            color: #555;
            cursor: pointer;
            font-size: 0.85rem;
            transition: all 0.2s;
        }

        .special-buttons button:hover {
            border-color: #444;
            color: #888;
        }

        .leave-button {
            border-color: #1a1a1a !important;
            color: #444 !important;
        }

        .leave-button:hover {
            border-color: #333 !important;
            color: #666 !important;
        }

        .input-wrapper {
            position: relative;
        }

        #message-input {
            width: 100%;
            min-height: 100px;
            padding: 1rem;
            background: #0f0f0f;
            border: 1px solid #1a1a1a;
            color: #c4c4c4;
            font-family: inherit;
            font-size: 1rem;
            line-height: 1.6;
            resize: vertical;
        }

        #message-input:focus {
            outline: none;
            border-color: #333;
        }

        #message-input::placeholder {
            color: #444;
        }

        .send-button {
            margin-top: 1rem;
            padding: 0.75rem 2rem;
            background: transparent;
            border: 1px solid #333;
            color: #999;
            cursor: pointer;
            font-size: 1rem;
            transition: all 0.2s;
            width: 100%;
        }

        .send-button:hover:not(:disabled) {
            border-color: #666;
            color: #c4c4c4;
        }

        .send-button:disabled {
            opacity: 0.3;
            cursor: not-allowed;
        }

        /* Modal for special outputs */
        .modal {
            display: none;
            position: fixed;
            top: 0;
            left: 0;
            right: 0;
            bottom: 0;
            background: rgba(0, 0, 0, 0.9);
            align-items: center;
            justify-content: center;
            z-index: 1000;
        }

        .modal.active {
            display: flex;
        }

        .modal-content {
            max-width: 600px;
            width: 90%;
            background: #0a0a0a;
            border: 1px solid #333;
            padding: 2rem;
            max-height: 80vh;
            overflow-y: auto;
        }

        .modal-close {
            float: right;
            background: none;
            border: none;
            color: #666;
            font-size: 1.5rem;
            cursor: pointer;
            padding: 0;
            line-height: 1;
        }

        .modal-close:hover {
            color: #999;
        }

        .modal-text {
            color: #8b8baf;
            line-height: 1.8;
            font-size: 1.05rem;
            margin-top: 1rem;
        }

        .adjacent-note {
            margin: 1.5rem 0;
            padding: 1rem;
            border-left: 2px solid #333;
        }

        .adjacent-note h3 {
            color: #999;
            font-size: 1rem;
            font-weight: 400;
            margin-bottom: 0.5rem;
        }

        .adjacent-note p {
            color: #666;
            font-size: 0.95rem;
            line-height: 1.6;
        }

        /* Empty state */
        .empty-state {
            text-align: center;
            padding: 4rem 2rem;
            color: #666;
        }

        .empty-state h2 {
            font-size: 1.2rem;
            font-weight: 400;
            margin-bottom: 1rem;
        }

        .empty-state p {
            font-size: 0.95rem;
        }

        @media (max-width: 768px) {
            .controls {
                flex-direction: column;
                align-items: stretch;
            }

            .special-buttons {
                margin-left: 0;
            }
        }
    </style>
</head>
<body>
    <nav>
        <div>
            <a href="/">Midnight Pilgrim</a>
        </div>
        <div>
            <a href="/write">Write</a>
            <a href="/read" style="margin-left: 1rem;">Read</a>
            <a href="/conversation" class="current" style="margin-left: 1rem;">Conversation</a>
        </div>
    </nav>

    <div class="container" aria-label="Conversation container">
        <div id="loading-indicator" style="display:none;text-align:center;margin:1rem 0;" aria-live="polite" aria-busy="true">Loading…</div>
        <div id="error-banner" style="display:none;color:#ff6b6b;text-align:center;margin:1rem 0;" role="alert"></div>
        @if($hasActiveSession)
            <!-- Active conversation -->
            <div class="conversation-wrapper" aria-live="polite">
                <div class="messages" id="messages" tabindex="0" aria-label="Conversation messages">
                    @if($messages->isEmpty())
                        <div class="empty-state">
                            <h2>{{ $session->mode === 'quiet' ? 'Quiet mode' : 'Company mode' }}</h2>
                            <p>Silence is always valid.</p>
                        </div>
                    @else
                        @foreach($messages as $message)
                            <div class="message {{ $message->role }}" aria-label="{{ $message->role }} message">
                                <div class="message-content">{{ $message->content }}</div>
                                <div class="message-time">{{ $message->created_at->diffForHumans() }}</div>
                            </div>
                        @endforeach
                    @endif
                </div>

                <div class="input-area">
                    <div class="controls">
                        <div class="mode-switch" role="radiogroup" aria-label="Conversation mode">
                            <button type="button" class="mode-btn {{ $session->mode === 'quiet' ? 'active' : '' }}" data-mode="quiet" aria-checked="{{ $session->mode === 'quiet' ? 'true' : 'false' }}" role="radio">Quiet</button>
                            <button type="button" class="mode-btn {{ $session->mode === 'company' ? 'active' : '' }}" data-mode="company" aria-checked="{{ $session->mode === 'company' ? 'true' : 'false' }}" role="radio">Company</button>
                        </div>

                        <div class="special-buttons">
                            <button type="button" id="random-btn" aria-label="Random prompt">Random</button>
                            <button type="button" id="thoughts-btn" aria-label="Thoughts">Thoughts</button>
                            <button type="button" id="adjacent-btn" aria-label="Adjacent theme">Adjacent</button>
                            <button type="button" id="leave-btn" class="leave-button" aria-label="Leave Quietly">Leave Quietly</button>
                        </div>
                    </div>

                    <div class="input-wrapper">
                        <textarea id="message-input" placeholder="Type or be silent..." aria-label="Message input" rows="4"></textarea>
                    </div>

                    <button type="button" class="send-button" id="send-btn" aria-label="Send message">Send</button>
                </div>
            </div>
        @else
            <!-- Resume gate (first visit or after closing session) -->
            <div class="resume-gate">
                <h1>Would you like to begin?</h1>
                <div class="actions">
                    <form method="POST" action="{{ route('conversation.begin') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="mode" value="quiet">
                        <button type="submit">Begin in Quiet</button>
                    </form>
                    <form method="POST" action="{{ route('conversation.begin') }}" style="display: inline;">
                        @csrf
                        <input type="hidden" name="mode" value="company">
                        <button type="submit">Begin in Company</button>
                    </form>
                    <a href="/" style="display: inline-block; padding: 0.75rem 2rem; background: transparent; border: 1px solid #222; color: #444; text-decoration: none; transition: all 0.2s; cursor: pointer;">Leave Quietly</a>
                </div>
            </div>
        @endif
    </div>

    <!-- Modal for special outputs -->
    <div class="modal" id="output-modal">
        <div class="modal-content">
            <button class="modal-close" type="button">&times;</button>
            <div id="modal-body"></div>
        </div>
    </div>

    <!-- Help modal -->
    <button id="help-btn" aria-label="Show help" style="position:fixed;top:1.5rem;right:1.5rem;z-index:1100;background:#222;color:#fff;border:none;padding:0.5rem 1rem;border-radius:4px;cursor:pointer;">?</button>
    <div class="modal" id="help-modal" aria-modal="true" role="dialog" tabindex="-1">
        <div class="modal-content" style="max-width:600px;">
            <button class="modal-close" type="button" aria-label="Close help">&times;</button>
            <div id="help-body">
                <h2 style="margin-top:0;">Welcome to Midnight Pilgrim</h2>
                <ul style="margin:1.5rem 0 1rem 1.5rem;padding:0;line-height:1.7;">
                    <li><b>Silence is valid.</b> You never have to reply.</li>
                    <li><b>Private by default.</b> Nothing is shared or tracked.</li>
                    <li><b>Modes:</b> Quiet (minimal) or Company (gentle).</li>
                    <li><b>Feature buttons:</b> Random, Thoughts, Adjacent for prompts and reflection.</li>
                    <li><b>Delete anytime:</b> Use "Leave Quietly" to erase your session.</li>
                    <li><b>Accessibility:</b> Fully keyboard and screen reader friendly.</li>
                </ul>
                <p style="color:#888;font-size:0.95rem;">For more, see the <a href="/read" style="color:#8b8baf;">Read</a> and <a href="/waystone" style="color:#8b8baf;">Waystone</a> sections.</p>
            </div>
        </div>
    </div>

    <script>
        const csrfToken = document.querySelector('meta[name="csrf-token"]').content;
        let currentMode = '{{ $session->mode ?? "quiet" }}';

        // Mode switching
        document.querySelectorAll('.mode-btn').forEach(btn => {
            btn.addEventListener('click', async function() {
                const mode = this.dataset.mode;
                
                const response = await fetch('/conversation/mode', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ mode })
                });

                if (response.ok) {
                    currentMode = mode;
                    document.querySelectorAll('.mode-btn').forEach(b => b.classList.remove('active'));
                    this.classList.add('active');
                }
            });
        });

        // Send message
        const sendBtn = document.getElementById('send-btn');
        const messageInput = document.getElementById('message-input');
        const messagesContainer = document.getElementById('messages');

        async function sendMessage() {
            const message = messageInput.value.trim();
            
            if (!message) {
                // Allow sending empty/silence
                return;
            }

            sendBtn.disabled = true;

            try {
                const response = await fetch('/conversation/send', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': csrfToken
                    },
                    body: JSON.stringify({ message, mode: currentMode })
                });

                const data = await response.json();

                // Add user message
                addMessage('user', message);

                // Add assistant response (or silence)
                if (data.silence) {
                    addMessage('silence', '...');
                } else if (data.response) {
                    addMessage('assistant', data.response);
                }

                messageInput.value = '';
            } catch (error) {
                console.error('Error sending message:', error);
            } finally {
                sendBtn.disabled = false;
                messageInput.focus();
            }
        }

        function addMessage(role, content) {
            const messageDiv = document.createElement('div');
            messageDiv.className = `message ${role}`;
            
            const contentDiv = document.createElement('div');
            contentDiv.className = 'message-content';
            contentDiv.textContent = content;
            
            const timeDiv = document.createElement('div');
            timeDiv.className = 'message-time';
            timeDiv.textContent = 'just now';
            
            messageDiv.appendChild(contentDiv);
            messageDiv.appendChild(timeDiv);
            
            messagesContainer.appendChild(messageDiv);
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }

        sendBtn.addEventListener('click', sendMessage);

        messageInput.addEventListener('keydown', function(e) {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                sendMessage();
            }
        });

        // Random line
        document.getElementById('random-btn').addEventListener('click', async function() {
            const response = await fetch('/conversation/random', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            
            showModal(data.line || 'No lines available yet.');
        });

        // Thoughts
        document.getElementById('thoughts-btn').addEventListener('click', async function() {
            const response = await fetch('/conversation/thoughts', {
                method: 'POST',
                headers: {
                    'X-CSRF-TOKEN': csrfToken
                }
            });

            const data = await response.json();
            
            showModal(data.thoughts || 'Not enough context yet.');
        });

        // Adjacent
        document.getElementById('adjacent-btn').addEventListener('click', async function() {
            const query = messageInput.value.trim() || 'recent thoughts';
            
            const response = await fetch('/conversation/adjacent', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': csrfToken
                },
                body: JSON.stringify({ query })
            });

            const data = await response.json();
            
            let html = '';
            if (data.notes && data.notes.length > 0) {
                data.notes.forEach(note => {
                    html += `
                        <div class="adjacent-note">
                            <h3>${note.title || 'Untitled'}</h3>
                            <p>${note.excerpt}</p>
                        </div>
                    `;
                });
            } else {
                html = '<p class="modal-text">No adjacent notes found.</p>';
            }
            
            showModal(html);
        });

        // Leave Quietly
        document.getElementById('leave-btn').addEventListener('click', async function() {
            if (confirm('Close this conversation and return home?')) {
                const response = await fetch('/conversation/close', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': csrfToken
                    }
                });

                if (response.ok) {
                    window.location.href = '/';
                }
            }
        });

        // Modal
        const modal = document.getElementById('output-modal');
        const modalBody = document.getElementById('modal-body');

        function showModal(content) {
            modalBody.innerHTML = `<div class="modal-text">${content}</div>`;
            modal.classList.add('active');
        }

        modal.querySelector('.modal-close').addEventListener('click', function() {
            modal.classList.remove('active');
        });

        modal.addEventListener('click', function(e) {
            if (e.target === modal) {
                modal.classList.remove('active');
            }
        });

        // Help modal
        document.getElementById('help-btn').addEventListener('click', function() {
            const modal = document.getElementById('help-modal');
            modal.classList.add('active');
            modal.focus();
        });
        document.querySelector('#help-modal .modal-close').addEventListener('click', function() {
            document.getElementById('help-modal').classList.remove('active');
        });
        document.getElementById('help-modal').addEventListener('keydown', function(e) {
            if (e.key === 'Escape') {
                this.classList.remove('active');
            }
        });
    </script>
</body>
</html>
