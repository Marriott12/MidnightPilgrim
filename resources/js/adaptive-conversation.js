/**
 * Midnight Pilgrim - Adaptive Conversation System
 * 
 * Client-side implementation of psychologically adaptive conversational system
 */

class AdaptiveConversationSystem {
    constructor() {
        this.sessionUuid = null;
        this.mode = 'quiet';
        this.isInitialized = false;
        this.messageHistory = [];
        this.csrfToken = document.querySelector('meta[name="csrf-token"]')?.content;
    }

    /**
     * Initialize session
     */
    async init(preferredMode = 'quiet') {
        try {
            const response = await fetch('/api/conversation/init', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ mode: preferredMode }),
            });

            const data = await response.json();
            this.sessionUuid = data.session_uuid;
            this.mode = data.mode;
            this.isInitialized = true;

            return data;
        } catch (error) {
            console.error('Failed to initialize session:', error);
            throw error;
        }
    }

    /**
     * Send message and get response
     */
    async sendMessage(message) {
        if (!this.isInitialized || !this.sessionUuid) {
            throw new Error('Session not initialized');
        }

        try {
            const response = await fetch('/api/conversation/message', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({
                    session_uuid: this.sessionUuid,
                    message: message,
                    mode: this.mode,
                }),
            });

            const data = await response.json();
            
            // Add messages to history
            this.messageHistory.push({ role: 'user', content: message });
            this.messageHistory.push({ role: 'assistant', content: data.message });

            return data;
        } catch (error) {
            console.error('Failed to send message:', error);
            throw error;
        }
    }

    /**
     * End current session
     */
    async endSession() {
        if (!this.sessionUuid) return;

        try {
            const response = await fetch('/api/conversation/end', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ session_uuid: this.sessionUuid }),
            });

            const data = await response.json();
            this.isInitialized = false;
            
            return data;
        } catch (error) {
            console.error('Failed to end session:', error);
            throw error;
        }
    }

    /**
     * Delete session permanently
     */
    async deleteSession() {
        if (!this.sessionUuid) return;

        try {
            await fetch('/api/conversation/session', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ session_uuid: this.sessionUuid }),
            });

            this.sessionUuid = null;
            this.isInitialized = false;
            this.messageHistory = [];
        } catch (error) {
            console.error('Failed to delete session:', error);
            throw error;
        }
    }

    /**
     * Delete all user data
     */
    async deleteProfile() {
        try {
            await fetch('/api/conversation/profile', {
                method: 'DELETE',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
            });

            this.sessionUuid = null;
            this.isInitialized = false;
            this.messageHistory = [];
        } catch (error) {
            console.error('Failed to delete profile:', error);
            throw error;
        }
    }

    /**
     * Get random philosophical prompt
     */
    async getRandomPrompt() {
        try {
            const response = await fetch('/api/conversation/random-prompt');
            const data = await response.json();
            return data.prompt;
        } catch (error) {
            console.error('Failed to get random prompt:', error);
            throw error;
        }
    }

    /**
     * Get session thoughts/reflection
     */
    async getThoughts() {
        if (!this.sessionUuid) {
            throw new Error('No active session');
        }

        try {
            const response = await fetch(
                `/api/conversation/thoughts?session_uuid=${this.sessionUuid}`
            );
            const data = await response.json();
            return data.reflection;
        } catch (error) {
            console.error('Failed to get thoughts:', error);
            throw error;
        }
    }

    /**
     * Get adjacent theme suggestion
     */
    async getAdjacentTheme() {
        try {
            const response = await fetch('/api/conversation/adjacent');
            const data = await response.json();
            return data.theme;
        } catch (error) {
            console.error('Failed to get adjacent theme:', error);
            throw error;
        }
    }

    /**
     * Get narrative reflection (every 5 sessions)
     */
    async getReflection() {
        try {
            const response = await fetch('/api/conversation/reflection');
            const data = await response.json();
            return data;
        } catch (error) {
            console.error('Failed to get reflection:', error);
            throw error;
        }
    }

    /**
     * Switch mode
     */
    async switchMode(newMode) {
        this.mode = newMode;
        
        try {
            await fetch('/api/conversation/update-mode', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-TOKEN': this.csrfToken,
                },
                body: JSON.stringify({ mode: newMode }),
            });
        } catch (error) {
            console.error('Failed to update mode:', error);
        }
    }

    /**
     * Apply response delay (for emotional pacing)
     */
    async applyDelay(milliseconds) {
        if (milliseconds > 0) {
            return new Promise(resolve => setTimeout(resolve, milliseconds));
        }
    }
}

// UI Controller
class ConversationUI {
    constructor(conversationSystem) {
        this.system = conversationSystem;
        this.elements = {};
        this.isTyping = false;
    }

    /**
     * Initialize UI elements
     */
    initElements() {
        this.elements = {
            resumeGate: document.getElementById('resume-gate'),
            resumeButton: document.getElementById('resume-session'),
            newSessionButton: document.getElementById('new-session'),
            resumePrompt: document.getElementById('resume-prompt'),
            conversationWrapper: document.getElementById('conversation-wrapper'),
            messagesContainer: document.getElementById('messages'),
            userInput: document.getElementById('user-input'),
            sendButton: document.getElementById('send-button'),
            modeToggle: document.getElementById('mode-toggle'),
            randomButton: document.getElementById('random-button'),
            thoughtsButton: document.getElementById('thoughts-button'),
            adjacentButton: document.getElementById('adjacent-button'),
            endSessionButton: document.getElementById('end-session'),
            typingIndicator: document.getElementById('typing-indicator'),
        };
    }

    /**
     * Bind event listeners
     */
    bindEvents() {
        this.elements.resumeButton?.addEventListener('click', () => this.handleResume());
        this.elements.newSessionButton?.addEventListener('click', () => this.handleNewSession());
        this.elements.sendButton?.addEventListener('click', () => this.handleSendMessage());
        this.elements.userInput?.addEventListener('keypress', (e) => {
            if (e.key === 'Enter' && !e.shiftKey) {
                e.preventDefault();
                this.handleSendMessage();
            }
        });
        this.elements.modeToggle?.addEventListener('click', () => this.toggleMode());
        this.elements.randomButton?.addEventListener('click', () => this.handleRandomPrompt());
        this.elements.thoughtsButton?.addEventListener('click', () => this.handleThoughts());
        this.elements.adjacentButton?.addEventListener('click', () => this.handleAdjacent());
        this.elements.endSessionButton?.addEventListener('click', () => this.handleEndSession());
    }

    /**
     * Handle resume session
     */
    async handleResume() {
        const data = await this.system.init(this.system.mode);
        this.hideResumeGate();
        this.showConversation();
        
        // Show resume prompt if available
        if (data.resume_prompt) {
            this.addSystemMessage(data.resume_prompt);
        }
    }

    /**
     * Handle new session
     */
    async handleNewSession() {
        await this.system.deleteSession();
        await this.system.init('quiet');
        this.hideResumeGate();
        this.showConversation();
        this.clearMessages();
    }

    /**
     * Handle send message
     */
    async handleSendMessage() {
        const message = this.elements.userInput.value.trim();
        if (!message || this.isTyping) return;

        // Add user message to UI
        this.addMessage('user', message);
        this.elements.userInput.value = '';

        // Show typing indicator
        this.showTyping();

        try {
            const response = await this.system.sendMessage(message);
            
            // Apply delay for emotional pacing
            await this.system.applyDelay(response.delay);
            
            this.hideTyping();
            this.addMessage('assistant', response.message);
        } catch (error) {
            this.hideTyping();
            this.addSystemMessage('Something went wrong. Please try again.');
        }
    }

    /**
     * Handle random prompt
     */
    async handleRandomPrompt() {
        try {
            const prompt = await this.system.getRandomPrompt();
            this.elements.userInput.value = prompt;
            this.elements.userInput.focus();
        } catch (error) {
            console.error('Failed to get random prompt');
        }
    }

    /**
     * Handle thoughts
     */
    async handleThoughts() {
        try {
            const reflection = await this.system.getThoughts();
            this.showModal('Session Reflection', reflection);
        } catch (error) {
            console.error('Failed to get thoughts');
        }
    }

    /**
     * Handle adjacent theme
     */
    async handleAdjacent() {
        try {
            const theme = await this.system.getAdjacentTheme();
            this.elements.userInput.value = theme;
            this.elements.userInput.focus();
        } catch (error) {
            console.error('Failed to get adjacent theme');
        }
    }

    /**
     * Handle end session
     */
    async handleEndSession() {
        if (!confirm('End this session?')) return;

        try {
            const result = await this.system.endSession();
            
            // Check for reflection
            if (result.reflection_available) {
                const reflection = await this.system.getReflection();
                if (reflection.has_reflection) {
                    this.showReflectionModal(reflection);
                }
            }
            
            // Reset UI
            this.clearMessages();
            this.showResumeGate();
        } catch (error) {
            console.error('Failed to end session');
        }
    }

    /**
     * Toggle mode
     */
    async toggleMode() {
        const newMode = this.system.mode === 'quiet' ? 'company' : 'quiet';
        await this.system.switchMode(newMode);
        this.updateModeUI(newMode);
    }

    /**
     * Add message to UI
     */
    addMessage(role, content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = `message message-${role}`;
        messageDiv.textContent = content;
        this.elements.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    /**
     * Add system message
     */
    addSystemMessage(content) {
        const messageDiv = document.createElement('div');
        messageDiv.className = 'message message-system';
        messageDiv.textContent = content;
        this.elements.messagesContainer.appendChild(messageDiv);
        this.scrollToBottom();
    }

    /**
     * Show typing indicator
     */
    showTyping() {
        this.isTyping = true;
        if (this.elements.typingIndicator) {
            this.elements.typingIndicator.style.display = 'block';
        }
    }

    /**
     * Hide typing indicator
     */
    hideTyping() {
        this.isTyping = false;
        if (this.elements.typingIndicator) {
            this.elements.typingIndicator.style.display = 'none';
        }
    }

    /**
     * Clear messages
     */
    clearMessages() {
        if (this.elements.messagesContainer) {
            this.elements.messagesContainer.innerHTML = '';
        }
    }

    /**
     * Scroll to bottom
     */
    scrollToBottom() {
        if (this.elements.messagesContainer) {
            this.elements.messagesContainer.scrollTop = 
                this.elements.messagesContainer.scrollHeight;
        }
    }

    /**
     * Show/hide UI elements
     */
    showResumeGate() {
        if (this.elements.resumeGate) {
            this.elements.resumeGate.style.display = 'block';
        }
        if (this.elements.conversationWrapper) {
            this.elements.conversationWrapper.style.display = 'none';
        }
    }

    hideResumeGate() {
        if (this.elements.resumeGate) {
            this.elements.resumeGate.style.display = 'none';
        }
    }

    showConversation() {
        if (this.elements.conversationWrapper) {
            this.elements.conversationWrapper.style.display = 'flex';
        }
    }

    /**
     * Update mode UI
     */
    updateModeUI(mode) {
        if (this.elements.modeToggle) {
            this.elements.modeToggle.textContent = mode === 'quiet' ? 'Quiet' : 'Company';
        }
    }

    /**
     * Show modal
     */
    showModal(title, content) {
        // Implementation depends on your modal system
        alert(`${title}\n\n${content}`);
    }

    /**
     * Show reflection modal
     */
    showReflectionModal(reflection) {
        let content = 'Pattern Observations:\n';
        reflection.observations.forEach((obs, i) => {
            content += `${i + 1}. ${obs}\n`;
        });
        content += `\nContradiction:\n${reflection.contradiction}\n`;
        content += `\nQuestion:\n${reflection.question}`;
        
        this.showModal('Narrative Reflection', content);
    }
}

// Initialize on page load
document.addEventListener('DOMContentLoaded', () => {
    const conversationSystem = new AdaptiveConversationSystem();
    const ui = new ConversationUI(conversationSystem);
    
    ui.initElements();
    ui.bindEvents();
    
    // Auto-initialize session
    conversationSystem.init().then(data => {
        if (data.has_active_session) {
            ui.elements.resumePrompt.textContent = data.resume_prompt;
            ui.showResumeGate();
        } else {
            ui.hideResumeGate();
            ui.showConversation();
        }
    });
});
