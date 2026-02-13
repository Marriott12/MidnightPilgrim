# FRONTEND INTEGRATION GUIDE

Quick reference for integrating the new Discipline system into the Midnight Pilgrim frontend.

---

## API INTEGRATION EXAMPLES

### 1. Initialize Discipline Contract

```javascript
// POST /api/discipline/init
async function initializeDisciplineContract() {
  const response = await fetch('/api/discipline/init', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    }
  });
  
  const data = await response.json();
  // { success: true, contract: { start_date, end_date, total_weeks } }
}
```

### 2. Get Contract Status

```javascript
// GET /api/discipline/status
async function getContractStatus() {
  const response = await fetch('/api/discipline/status');
  const data = await response.json();
  
  /* Response:
  {
    active: true,
    week: 5,
    total_weeks: 10,
    constraint: {
      type: 'concrete_imagery',
      description: 'Concrete imagery only. No abstractions...'
    },
    deadline: '2026-02-23T20:00:00Z',
    hours_until_deadline: 48,
    minimum_lines: 14,  // or 28 if penalties apply
    poems_submitted: 4,
    poems_missed: 1,
    monthly_releases: 0,
    monthly_release_due: true
  }
  */
}
```

### 3. Submit Poem

```javascript
// POST /api/discipline/submit-poem
async function submitPoem(content, selfAssessment) {
  const response = await fetch('/api/discipline/submit-poem', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      content: content,
      self_assessment: {
        lazy_where: "Line 8 relied on generic imagery",
        abstraction_where: "Used 'meaning' in line 4 without grounding",
        weakest_line: "Line 12 - rhythm breaks and adds nothing",
        risk_avoided: "Avoided specificity about the actual relationship"
      }
    })
  });
  
  const data = await response.json();
  
  /* Response:
  {
    success: true,
    message: 'Poem submitted successfully.',
    poem_id: 42,
    critique: {
      line_strength: "...",
      rhythm: "...",
      image_density: "...",
      conceptual_coherence: "...",
      emotional_honesty: "...",
      constraint_adherence: "...",
      weaknesses_identified: [...]
    },
    on_time: true
  }
  */
}
```

### 4. Get Pattern Reports

```javascript
// GET /api/discipline/patterns
async function getPatternReports() {
  const response = await fetch('/api/discipline/patterns');
  const data = await response.json();
  
  /* Response:
  {
    has_patterns: true,
    patterns: [
      {
        id: 1,
        type: 'abstraction_drift',
        description: 'Recurring tendency to retreat into abstraction...',
        evidence: ['Session 5: 3 abstract statements', ...],
        correction_strategy: 'Force specificity. Every claim must...',
        specific_exercise: 'For the next week: Every time you make...'
      }
    ]
  }
  */
}
```

### 5. Feature Buttons

```javascript
// GET /api/conversation/random-prompt
async function getRandomChallenge() {
  const response = await fetch('/api/conversation/random-prompt');
  const data = await response.json();
  
  /* Response:
  {
    type: 'constraint',
    title: 'Monosyllabic Constraint',
    description: 'Write 50 words using only single-syllable words...'
  }
  */
}

// GET /api/conversation/thoughts
async function getThoughtPrompt() {
  const response = await fetch('/api/conversation/thoughts');
  const data = await response.json();
  
  /* Response:
  {
    prompt: 'You cannot optimize what you refuse to measure.',
    challenge: 'What are you avoiding quantifying?'
  }
  */
}

// GET /api/conversation/adjacent?session_uuid=...
async function getAdjacentTheme(sessionUuid) {
  const response = await fetch(`/api/conversation/adjacent?session_uuid=${sessionUuid}`);
  const data = await response.json();
  
  /* Response:
  {
    type: 'contrast',
    title: 'Work → Play Inversion',
    content: 'You framed this as work. What if it were play?...'
  }
  */
}
```

### 6. Send Message (Updated with Discipline Tracking)

```javascript
// POST /api/conversation/message
async function sendMessage(sessionUuid, message) {
  const response = await fetch('/api/conversation/message', {
    method: 'POST',
    headers: {
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({
      session_uuid: sessionUuid,
      message: message
    })
  });
  
  const data = await response.json();
  
  /* Response now includes tone adaptation:
  {
    message: "That's undefined. What specifically does that mean?",
    delay: 0,  // milliseconds
    intensity: 0.45,
    tone_adaptation: 'sharp'  // 'baseline' | 'sharp'
  }
  */
}
```

---

## UI COMPONENTS NEEDED

### 1. Discipline Dashboard

Display:
- Current week number / total weeks
- Current constraint description
- Hours until deadline
- Required minimum lines
- Poems submitted vs missed
- Monthly release status

### 2. Poem Submission Form

Fields:
- Poem content (textarea)
- Self-assessment questions:
  - Where was I lazy?
  - Where did I hide behind abstraction?
  - What line is weakest and why?
  - What risk did I avoid?
- Submit button (disabled if deadline passed)

### 3. Critique Display

Show after submission:
- Line strength analysis
- Rhythm analysis
- Image density
- Conceptual coherence
- Emotional honesty
- Constraint adherence check
- Identified weaknesses

### 4. Pattern Report Modal

Display when patterns detected:
- Pattern type (badge)
- Description
- Evidence list
- Correction strategy
- Specific exercise
- Acknowledge button

### 5. Tone Indicator

Visual indicator for conversation tone:
- Baseline: Normal appearance
- Sharp: Warning color/icon to show escalation

### 6. Updated Feature Buttons

RANDOM button:
- Shows challenge type, title, description

THOUGHTS button:
- Shows prompt + challenge question

ADJACENT button:
- Shows theme type, title, content

---

## EXAMPLE UI FLOW

### Poetry Submission Flow

1. User clicks "Submit Poem" button
2. Check contract status:
   ```javascript
   const status = await getContractStatus();
   if (!status.active) {
     showError("No active discipline contract");
     return;
   }
   ```

3. Show form with:
   - Constraint reminder at top
   - Minimum lines requirement
   - Hours until deadline
   - Poem textarea
   - Self-assessment fields

4. On submit:
   - Validate all fields filled
   - Submit poem
   - Show critique
   - Update dashboard

### Pattern Report Flow

1. On session end or periodically:
   ```javascript
   const patterns = await getPatternReports();
   if (patterns.has_patterns) {
     showPatternModal(patterns.patterns);
   }
   ```

2. Display modal with all unacknowledged patterns

3. User reads and acknowledges:
   ```javascript
   await fetch('/api/discipline/acknowledge-pattern', {
     method: 'POST',
     body: JSON.stringify({ pattern_id: patternId })
   });
   ```

### Conversation Flow with Discipline Tracking

1. Send message as normal
2. Check response tone:
   ```javascript
   const response = await sendMessage(uuid, msg);
   if (response.tone_adaptation === 'sharp') {
     // Show visual indicator that tone has escalated
     showToneWarning("Vagueness or avoidance detected");
   }
   ```

3. Display message with appropriate styling

---

## STYLING SUGGESTIONS

### Tone Indicators
```css
.tone-baseline {
  /* Normal conversation style */
}

.tone-sharp {
  border-left: 4px solid #ff6b6b;
  background: rgba(255, 107, 107, 0.1);
}
```

### Discipline Dashboard
```css
.discipline-dashboard {
  display: grid;
  grid-template-columns: repeat(auto-fit, minmax(200px, 1fr));
  gap: 1rem;
}

.deadline-warning {
  color: #ff6b6b;
  font-weight: bold;
}

.deadline-ok {
  color: #51cf66;
}
```

### Pattern Report Badge
```css
.pattern-badge {
  display: inline-block;
  padding: 0.25rem 0.5rem;
  border-radius: 4px;
  font-size: 0.875rem;
  font-weight: 600;
}

.pattern-abstraction { background: #ffd43b; }
.pattern-avoidance { background: #ff6b6b; }
.pattern-grandiosity { background: #a78bfa; }
```

---

## STATE MANAGEMENT

Consider tracking:
```javascript
const disciplineState = {
  contractActive: false,
  currentWeek: 0,
  currentConstraint: null,
  hoursUntilDeadline: 0,
  minimumLines: 14,
  unacknowledgedPatterns: [],
  sessionTone: 'baseline',
  vaguenessCount: 0,
  abstractionCount: 0,
  avoidanceCount: 0,
};
```

Update state after each API call to keep UI in sync.

---

## ERROR HANDLING

```javascript
async function submitPoem(content, assessment) {
  try {
    const response = await fetch('/api/discipline/submit-poem', {
      method: 'POST',
      headers: { 'Content-Type': 'application/json' },
      body: JSON.stringify({ content, self_assessment: assessment })
    });
    
    const data = await response.json();
    
    if (!data.success) {
      // Show error message (e.g., not enough lines, already submitted)
      showError(data.message);
      return;
    }
    
    // Success - show critique
    showCritique(data.critique);
    
  } catch (error) {
    showError('Failed to submit poem. Please try again.');
  }
}
```

---

## NOTIFICATIONS

Implement notifications for:
1. **Deadline approaching** (24 hours before)
2. **Recovery window active** (Sunday 20:00 - Monday 20:00)
3. **Pattern report available**
4. **Monthly release due**
5. **Tone escalation** (when sharp tone activated)

---

## ACCESSIBILITY

- Use ARIA labels for tone indicators
- Ensure pattern reports are keyboard-navigable
- Provide clear error messages
- Use semantic HTML for dashboard widgets
- Ensure deadline timers are screen-reader friendly

---

**END OF FRONTEND GUIDE**

For full system documentation, see `DISCIPLINE_TRANSFORMATION.md`
