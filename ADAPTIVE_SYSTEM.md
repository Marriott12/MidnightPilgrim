# Midnight Pilgrim - Adaptive Conversational System

## Overview

Midnight Pilgrim is a psychologically adaptive conversational system with three distinct layers that work together to create a calm, intelligent companion that observes more than it speaks.

## Architecture

### LAYER 1 — CONVERSATIONAL ENGINE

**Two Modes:**

1. **Company Mode**
   - Reflective and story-driven
   - Emotionally intelligent
   - Gently confrontational when needed
   - Occasionally disagrees
   - Avoids therapy tone
   - Never over-praises or over-guides

2. **Quiet Mode**
   - 1–3 sentences maximum
   - Precise mirroring
   - No over-explaining
   - Minimal guidance
   - No validation language unless necessary

**Features:**
- Adaptive pacing with response delays based on emotional intensity
- Session resume logic using soft fingerprinting (IP hash + user agent)
- Contextual resume prompts summarizing last emotional theme
- Option to permanently delete session records

### LAYER 2 — EMOTIONAL PATTERN ENGINE

Computes and updates emotional metrics for each user:

- **emotional_baseline**: Average emotional tone across sessions (0-1 scale)
- **volatility_score**: Variance in emotional intensity (0-1 scale)
- **absolutist_language_frequency**: Count of absolute words (always, never, etc.)
- **self_criticism_index**: Normalized frequency of self-critical statements (0-1 scale)
- **recurring_topics[]**: Top 5 most frequent themes
- **time_of_day_emotional_drift**: Emotional tone by hour of day
- **session_depth_score**: Engagement metric based on message count and intensity (0-1 scale)

**Detection Capabilities:**
- Absolutist language (always, never, nothing, everything)
- Negative self-references and self-criticism patterns
- Emotional intensity words (devastated, overwhelmed, etc.)
- Topic extraction (work, relationships, anxiety, etc.)
- Repetition patterns

**Storage:**
- Metrics stored separately from conversation logs
- No verbatim memory referenced in responses
- Insights generated from aggregated metrics only

### LAYER 3 — NARRATIVE CONTINUITY ENGINE

Every 5 sessions, generates:

1. **3 Pattern Observations** - Based on emotional trajectory, intensity patterns, and recurring themes
2. **1 Contradiction** - Highlights inconsistencies in patterns
3. **1 Long-term Philosophical Question** - Avoids therapy framing; maintains philosophical tone

**Constraints:**
- No clinical labels
- No explicit "memory recall" statements
- Maintains presence illusion
- Optimizes for depth over verbosity

## Personalization Rules

1. **Tone Adaptation**: Response tone adapts based on user baseline
   - High volatility → calmer responses
   - Low baseline → gentler tone
   - High baseline → more direct tone

2. **Validation Reduction**: As session count increases, validation language decreases to increase depth

3. **Mode Preference**: System tracks and defaults to user's preferred mode

4. **Consistency**: Never over-praise, never over-guide

## Features

### 1. Random Button
Generates philosophical prompts aligned with user's recurring themes:
- Generic prompts for new users
- Theme-specific prompts for returning users
- Based on emotional patterns and topics

### 2. Thoughts Button
Generates structured reflection summary from current session only:
- Comments on session intensity
- Comments on emotional tone
- Identifies topics discussed
- Philosophical closing statement

### 3. Adjacent Button
Suggests related emotional theme based on recurring_topics:
- Maps topics to adjacent themes
- Helps users explore connected ideas
- Maintains philosophical framing

### 4. Reflection Mode Toggle
At session end, optionally shows narrative reflection (every 5 sessions):
- 3 pattern observations
- 1 identified contradiction
- 1 philosophical question

## API Endpoints

### Session Management

```
POST /api/conversation/init
Body: { mode: 'quiet' | 'company' }
Response: { session_uuid, mode, has_active_session, resume_prompt?, message_count }
```

```
POST /api/conversation/message
Body: { session_uuid, message, mode? }
Response: { message, delay, intensity, tone_adaptation }
```

```
POST /api/conversation/end
Body: { session_uuid }
Response: { success, reflection_available }
```

### Data Deletion

```
DELETE /api/conversation/session
Body: { session_uuid }
Permanently deletes session and all messages
```

```
DELETE /api/conversation/profile
Permanently deletes all user data (sessions, snapshots, reflections, profile)
```

### Feature Buttons

```
GET /api/conversation/random-prompt
Response: { prompt }
```

```
GET /api/conversation/thoughts?session_uuid={uuid}
Response: { reflection }
```

```
GET /api/conversation/adjacent
Response: { theme }
```

```
GET /api/conversation/reflection
Response: { has_reflection, observations, contradiction, question }
```

### Settings

```
POST /api/conversation/update-mode
Body: { mode: 'quiet' | 'company' }
Updates user's preferred mode
```

## Database Schema

### user_profiles
- fingerprint (hashed IP + user agent)
- emotional_baseline
- volatility_score
- absolutist_language_frequency
- self_criticism_index
- recurring_topics (JSON)
- time_of_day_emotional_drift (JSON)
- session_depth_score
- preferred_mode
- total_sessions
- sessions_since_reflection
- last_session_at

### conversation_sessions
- uuid
- user_profile_id
- fingerprint
- mode
- status
- session_intensity
- absolutist_count
- self_criticism_count
- detected_topics (JSON)
- emotional_tone
- message_count
- last_message_at

### messages
- session_id
- role (user | assistant)
- content
- created_at

### emotional_snapshots
- user_profile_id
- session_id
- intensity
- tone
- absolutist_count
- self_criticism_count
- topics (JSON)
- hour_of_day
- created_at

### narrative_reflections
- user_profile_id
- pattern_observations (JSON)
- identified_contradiction
- philosophical_question
- shown_to_user
- shown_at
- created_at

## Privacy & Data Protection

- **No PII**: Only hashed fingerprints stored, no names or emails
- **User Control**: Full data deletion available at any time
- **Minimal Retention**: Only metrics stored, not full conversations
- **Soft Fingerprinting**: Uses reversible hashing for anonymous identification
- **Session Isolation**: Each session can be deleted independently

## Installation & Setup

1. **Run Migrations:**
```bash
php artisan migrate
```

2. **Configure AI Service:**
Edit `AdaptiveConversationController::generateAIResponse()` to integrate with your chosen LLM (OpenAI, Claude, etc.)

3. **Build Frontend Assets:**
```bash
npm install
npm run build
```

4. **Test API:**
```bash
curl -X POST http://localhost/api/conversation/init \
  -H "Content-Type: application/json" \
  -d '{"mode":"quiet"}'
```

## Integration with AI Services

The system is designed to work with any LLM. Update the placeholder in `AdaptiveConversationController`:

```php
private function generateAIResponse(string $systemPrompt, array $messages): string
{
    // Example with OpenAI
    $openai = new OpenAI(config('services.openai.key'));
    
    $response = $openai->chat()->create([
        'model' => 'gpt-4',
        'messages' => array_merge(
            [['role' => 'system', 'content' => $systemPrompt]],
            $messages
        ),
        'temperature' => 0.8,
        'max_tokens' => 500,
    ]);
    
    return $response->choices[0]->message->content;
}
```

## Design Principles

1. **Presence over Memory**: Never explicitly reference "remembering" things
2. **Observation over Intervention**: System observes and reflects, doesn't diagnose
3. **Philosophy over Therapy**: Maintains philosophical tone, avoids clinical language
4. **Depth over Verbosity**: Especially in Quiet Mode, less is more
5. **Adaptive without Gamification**: Tracks patterns without creating streaks or scores
6. **Privacy-First**: Minimal data retention, full deletion capabilities

## Example Conversations

### Company Mode Example
```
User: I feel like I'm failing at everything.

System: "Failing" is such a final word. Like there's a finish line you missed. 
But you're still moving, still here. What if this isn't failure—just friction?

User: I guess I just expected more from myself by now.

System: Expected based on what? Some timeline you inherited, or one you actually chose?
```

### Quiet Mode Example
```
User: I feel like I'm failing at everything.

System: Everything? Or the things that matter most?

User: I guess the things that matter.

System: Those are the hardest to fail at. Because they matter.
```

## Emotional Pattern Detection Examples

### Absolutist Language
```
"I always mess things up" → Detected: "always"
"Nothing ever works out" → Detected: "nothing", "ever"
"Everyone hates me" → Detected: "everyone"
```

### Self-Criticism
```
"I'm so stupid" → Pattern match: self-deprecation
"I hate myself" → Pattern match: self-hatred
"What's wrong with me?" → Pattern match: self-questioning
"I'm not good enough" → Pattern match: inadequacy
```

### Topic Extraction
```
"My boss is impossible to work with" → Topics: [work]
"I feel so anxious all the time" → Topics: [anxiety]
"Nobody understands me" → Topics: [loneliness, relationships]
"I keep thinking about what I should have done" → Topics: [past, anxiety]
```

## Narrative Reflection Examples

### Pattern Observation (Session Trend)
```
Emotional trajectory trending upward:
"Over these last conversations, something has been lifting. The edge is softer."

Emotional trajectory trending downward:
"These past sessions have carried more weight. The darkness has been closer."
```

### Contradiction Example
```
High tone + High intensity:
"You speak hopefully while drowning in overwhelm. Which one is the lie?"

High volatility + High baseline:
"You swing wildly between states but insist you're fine. Stability might be the mask."
```

### Philosophical Questions
```
For high self-criticism:
"What would happen if you treated yourself like someone you were trying to understand?"
"Who taught you that you needed to earn existence?"

For recurring anxiety:
"What if anxiety is telling the truth, just poorly?"
"Is the fear protecting something, or destroying it?"

For work-related themes:
"Is work meaning, or escape from meaning?"
```

## Frontend Integration

The JavaScript client (`adaptive-conversation.js`) provides a complete interface:

```javascript
// Initialize system
const system = new AdaptiveConversationSystem();
await system.init('quiet');

// Send message
const response = await system.sendMessage("I'm struggling today");
console.log(response.message); // AI response
console.log(response.delay); // Suggested delay in ms
console.log(response.intensity); // Session intensity

// Get random prompt
const prompt = await system.getRandomPrompt();

// Get session thoughts
const thoughts = await system.getThoughts();

// Get adjacent theme
const theme = await system.getAdjacentTheme();

// End session and check for reflection
const result = await system.endSession();
if (result.reflection_available) {
    const reflection = await system.getReflection();
}

// Delete all data
await system.deleteProfile();
```

## Testing Checklist

- [ ] New user can start a session
- [ ] Returning user sees resume prompt
- [ ] Messages are saved correctly
- [ ] Emotional patterns are detected
- [ ] Session metrics update properly
- [ ] Profile metrics update after session
- [ ] Reflection generates after 5 sessions
- [ ] Random prompts work
- [ ] Thoughts button works
- [ ] Adjacent button works
- [ ] Mode switching works
- [ ] Session deletion works
- [ ] Profile deletion works
- [ ] Response delays apply correctly
- [ ] Tone adaptation works

## Future Enhancements

1. **Crisis Detection**: Identify crisis language and provide resources
2. **Advanced NLP**: Use ML models for better topic extraction
3. **Temporal Analysis**: Track patterns over weeks/months
4. **Export Data**: Allow users to export their metrics
5. **Voice Interface**: Add speech-to-text and text-to-speech
6. **Mobile App**: Native mobile applications
7. **Multi-language Support**: Detect and respond in multiple languages
8. **Accessibility**: Enhanced screen reader support
9. **Analytics Dashboard**: Visual representation of emotional patterns (opt-in)
10. **Journaling Integration**: Connect with note-taking system

## Contributing

This system is designed to be extended. Key extension points:

1. **New Topics**: Add to `EmotionalPatternEngineService::extractTopics()`
2. **New Observations**: Add to `NarrativeContinuityEngineService::generateObservations()`
3. **New Questions**: Add to `NarrativeContinuityEngineService::generatePhilosophicalQuestion()`
4. **New Patterns**: Add to detection arrays in `EmotionalPatternEngineService`

## License

[Your License Here]

## Support

For issues or questions, please [contact information or issue tracker].
