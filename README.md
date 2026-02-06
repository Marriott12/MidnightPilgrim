# Midnight Pilgrim 🌑

**Status:** ✅ All Phases (0-8) Complete  
**Philosophy:** Silence-first, local-first, covenant-protected

---

## What Is This?

**Midnight Pilgrim** is a local-first reflective writing system that refuses to optimize for engagement, track behavior, or compromise privacy.

It is **not** a productivity app.  
It is **not** a SaaS platform.  
It is **not** an AI toy.  
It is **not** therapy.

It is a place to **walk with ideas over time**.

**What it is:**
- A local markdown vault (Obsidian-compatible)
- A quote distillation engine (heuristics only, manual promotion)
- A daily thought ritual (one per day, user-invoked)
- A pattern recognition system (shows what you've been circling)
- A calm companion (optional check-ins, always private)
- A silence-first interface (dark, minimal, keyboard-friendly)
- A quiet public invitation (Waystone website)

---

## Core Philosophy

Midnight Pilgrim is built on one unbreakable rule:

> **Markdown is the brain.  
> Laravel is the steward.**

Everything else flows from this.

### Unbreakable Rules

**1. Silence is the Default**
- No unsolicited responses
- No notifications or reminders
- No prompts to "check in" or "write something"
- Silence is not absence—it is presence without pressure

**2. Markdown is Sacred**
- All written content lives as `.md` files in the storage vault
- The database stores **metadata only**
- The system must survive if:
  - The database is deleted
  - Laravel is removed
  - This repository is archived and reopened years later

**3. Privacy by Design**

Three concentric layers enforce privacy:

1. **Inner Circle — Personal (Default)**
   - All new content is `private`
   - Local-first, silence-first
   - Never shared without explicit action

2. **Middle Layer — Reflective (Opt-in)**
   - Selected items marked `reflective`
   - Surfaced carefully within personal interface
   - Surfacing is always opt-in, never automatic

3. **Outer Layer — Waystone / Public (Explicit Ritual)**
   - Only `shareable` items appear publicly
   - Requires deliberate sharing ritual
   - **Hard rule**: Mental health artifacts (check-ins, interactions) can NEVER be shared

**4. Local-First Forever**
- Data stays on your machine
- Exportable as pure Markdown ZIP
- No cloud sync (by design)
- No analytics, tracking, or telemetry
- Works offline

**5. No Gamification Ever**
- No streaks, badges, or metrics
- No charts or "days active" counters
- No urgency-colored UI elements
- No productivity dashboards

---

## What We Refuse (Covenant)

### Never Added
- **Engagement Metrics**: No writing streaks, word count goals, or productivity dashboards
- **Notifications**: No push notifications, email reminders, or "time to reflect" prompts
- **Social Features**: No feeds, followers, likes, reactions, or discovery algorithms
- **Automated Interventions**: No crisis detection, automatic escalation, or diagnostic suggestions
- **Unsolicited Content**: No "featured" writings, recommended notes, or AI-generated insights

### What We Protect
- **Calm Interface**: One action at a time, no dashboards or feeds
- **Temporal Respect**: No "on this day" features or "you haven't written in X days" guilt
- **Cognitive Quiet**: No popups, tooltips, onboarding wizards, or feature suggestions

**This is a covenant, not marketing.** It exists to make it difficult for future contributors to drift toward productized, attention-focused behavior.

---

## Installation

### Requirements
- PHP >= 8.2.0
- Composer
- SQLite

### Setup
```bash
# Clone to local machine
git clone <repository> MidnightPilgrim
cd MidnightPilgrim

# Install dependencies
composer install

# Set up environment
cp .env.example .env
php artisan key:generate

# Create storage directories
php artisan storage:link

# Run migrations
php artisan migrate

# Start server
php artisan serve
```

### Access Views
- **Write:** http://127.0.0.1:8000/write
- **Read:** http://127.0.0.1:8000/read
- **Adjacent:** http://127.0.0.1:8000/adjacent-view
- **Sit:** http://127.0.0.1:8000/sit (Mental Health Companion)
- **Waystone (public):** http://127.0.0.1:8000/waystone

---

## Project Structure

```
midnight-pilgrim/
├── app/
│   ├── Models/              # Metadata only (Note, Quote, DailyThought, CheckIn)
│   ├── Services/
│   │   ├── QuoteEngine.php              # Heuristic quote extraction
│   │   ├── DailyThoughtEngine.php       # Once-per-day ritual
│   │   ├── AdjacencyEngine.php          # Pattern recognition
│   │   ├── MentalHealthCompanionService.php  # Optional check-in
│   │   └── ...
│   ├── Http/
│   │   ├── Controllers/
│   │   │   ├── WriteController.php
│   │   │   ├── ReadController.php
│   │   │   ├── CompanionController.php  # Sit view
│   │   │   └── WaystoneController.php   # Public site
│   │   └── Middleware/
│   │       ├── PublicContentGuard.php   # Visibility enforcement
│   │       └── SetPublicMode.php        # Waystone isolation
│   └── Policies/
│       └── ContentVisibilityPolicy.php  # Privacy boundaries
│
├── database/
│   ├── migrations/
│   └── database.sqlite      # Metadata only (replaceable)
│
├── storage/app/
│   ├── vault/               # Notes/poems (SACRED - Obsidian-compatible)
│   ├── quotes/              # Extracted quotes with source references
│   ├── thoughts/            # Daily thoughts (one per day)
│   ├── companion/           # Mental health check-ins (NEVER shareable)
│   └── cache/               # Adjacency results (1-hour TTL)
│
├── resources/views/
│   ├── write.blade.php      # Writing interface
│   ├── read.blade.php       # Content browser
│   ├── adjacent.blade.php   # Pattern view
│   ├── sit.blade.php        # Mental health companion
│   └── waystone/            # Public website views
│
└── public/
    ├── manifest.json        # PWA manifest
    └── sw.js                # Service worker
```

### Critical Directories

- **`storage/app/vault`**  
  This is the **mind** of Midnight Pilgrim.  
  Never delete it. Never auto-refactor it. Never hide it.

- **`storage/app/companion`**  
  Mental health check-ins. Permanently private. Never exported or shared.

- **`database/database.sqlite`**  
  This is replaceable. Helpful, but not sacred.

---

## How to Use

### Writing
1. Go to `/write`
2. Type freely (auto-saves to localStorage)
3. **Mark quotes** by starting a line with `>`
4. Click "Save" (defaults to private)
5. **Keyboard shortcut:** Ctrl/Cmd+Enter to save

**Example:**
```markdown
Had a quiet walk tonight. The streets felt empty but not lonely.

> Sometimes silence is just space, not absence.

Need to remember this feeling.
```

The quoted line (`>`) will be extracted automatically.

### Reading
1. Go to `/read`
2. Filter by: All, Notes, Quotes, Thoughts
3. Click any item to view full content
4. Everything is chronological (most recent first)

**No feeds, no metrics, no recommendations.**

### Patterns (Adjacency)
1. Go to `/adjacent-view`
2. See words/phrases you've been circling
3. Each cluster shows **your own words** (no interpretation)
4. Click references to return to source material

**Question answered:** "What have I been circling lately?"

### Daily Thoughts (Optional Ritual)
Generate once per day from your quote collection:

```php
use App\Services\DailyThoughtEngine;

$engine = app(DailyThoughtEngine::class);
$thought = $engine->generate();  // Returns null if already generated today
```

**Ritual:** Not automated, you invoke when ready.

### Mental Health Check-In (Optional)
Visit `/sit` for a minimal, calm interface.

**Two modes:**
- **Mode A (Reflective)**: References your existing notes
- **Mode B (Check-in)**: Brief optional question, private storage

**Hard boundaries:** No advice, no diagnosis, no treatment suggestions.

---

## Storage Structure

All content stored as **markdown files**:

```
storage/app/
├── vault/           # Notes and poems (private by default)
│   └── 2026-02-06--153045--quiet-walk.md
├── quotes/          # Extracted quotes (manual or marked with >)
│   └── 2026-02-06--153045--silence-as-space.md
├── thoughts/        # Daily thoughts (one per day)
│   └── 2026-02-06--daily-thought.md
├── companion/       # Mental health check-ins (NEVER shareable)
│   └── checkins/2026-02-06--190000.md
└── cache/           # Adjacency results (temporary, 1-hour TTL)
    └── adjacency.json
```

**Obsidian-compatible:** Open `storage/app/vault/` as an Obsidian vault.

### Markdown Format

All files use YAML frontmatter:

```markdown
---
title: Quiet Walk
type: note
tags: [evening, reflection]
date: 2026-02-06T15:30:45Z
visibility: private
source: vault/2026-02-06--153045--quiet-walk.md  # For quotes
---

Content here...
```

---

## Features Implemented

### Phase 0: Immutable Foundations ✅
- Visibility covenant (private by default)
- Mental health isolation (companion/ permanently private)
- Storage boundaries enforced
- Non-goals documented in code

### Phase 1: Markdown Ingestion ✅
- Read-only vault parsing
- Obsidian YAML frontmatter support
- No database dependency for content
- Visibility-aware defaults

### Phase 2: Quote Engine ✅
- Manual marking with `>` prefix
- Heuristic suggestions (line length, punctuation, whitespace)
- No AI or sentiment analysis
- Immutable markdown storage with source references

### Phase 3: Daily Thought Engine ✅
- One thought per day from quote collection
- User-invoked (not automated)
- Silence on duplicate attempts
- Ritual, not automation

### Phase 4: Adjacency Engine ✅
- Pattern recognition across notes/quotes/thoughts
- Returns references only (no interpretation)
- Answers: "What have you been circling lately?"
- Cached for performance

### Phase 5: Mental Health Companion ✅
- Mode A (Reflective): References existing notes
- Mode B (Check-in): Brief optional question
- Hard safety boundaries (no advice, no diagnosis)
- Always private in isolated companion/

### Phase 6: UI Experience ✅
- Write, Read, Adjacent, Sit views
- Responsive (mobile → desktop)
- PWA-installable
- Dark/night-first design
- Keyboard-friendly with focus management
- ARIA labels and accessibility
- No dashboards, metrics, or feeds

### Phase 7: Quiet Public Website ✅
- Waystone (public invitation, not product page)
- Philosophy, Writings (shareable only), Download
- No tracking or analytics
- Static-feeling design
- Middleware-isolated from private content

### Phase 8: Covenant Documentation ✅
- Comprehensive covenant in this README
- Explicit non-goals stated clearly
- Contributor guidance
- Protection against drift
- Mental health boundaries documented

---

## Ethical Contract

### Not a Therapist
- Midnight Pilgrim is **not** medical or mental-health advice
- It does not diagnose or treat
- It does not provide clinical guidance

### No Unsolicited Intervention
- The system will **not** escalate
- The system will **not** call services
- The system will **not** push users toward providers
- When repeated high-intensity signals appear, the system **may** gently suggest: *"You don't have to carry this alone. Talking with someone you trust could help."*
- This is a **suggestion**, not an alert
- It appears once, then respects silence

### User Data Ownership
- All writings stored locally
- Full export available at any time (ZIP of markdown files)
- No server uploads
- No account required

---

## Quote Extraction

### Automatic (Manual Marking)
In any note, start a line with `>`:
```markdown
> This line becomes a quote
```

### Manual Promotion
```php
use App\Services\QuoteEngine;

$engine = app(QuoteEngine::class);

// Get suggestions (heuristics only, not AI)
$suggestions = $engine->suggestQuotes($note);

// Manually promote selected text
$quote = $engine->promoteToQuote($note, $selectedText);
```

**Heuristics used:**
- Line length (8-120 chars)
- Ends with punctuation (. ! ? —)
- Isolated by whitespace
- Contains emphasis (* " _)

**Not used:** Sentiment, frequency, AI, engagement

---

## Visibility Levels

All content has visibility (defaults to `private`):

- **`private`** - Only you, never exported
- **`reflective`** - Opt-in sharing (requires explicit action)
- **`shareable`** - Explicit ritual to share publicly on Waystone

**Mental health data (CheckIn, Interaction):**
- Always `private` (cannot be changed)
- Stored in isolated `companion/` directory
- Excluded from all exports

**Hard rules enforced in code:**
- New items default to `private`
- Nothing becomes `shareable` without explicit sharing ritual
- Mental-health artifacts may NEVER be marked `shareable`

---

## For Contributors

### Design Constraints

If you are adding new features, ask:
- **"Does this add noise or pressure?"** If yes, do not add it.
- **"Does this encourage engagement or retention?"** If yes, do not add it.
- **"Does this compromise privacy?"** If yes, do not add it.

### Contributor Covenant

- Prioritize **silence and privacy** over feature expansion
- Any change that increases attention, retention, or tracking must be explicitly justified and approved by maintainers
- Document intent for all non-trivial UI or engine changes
- Include clear explanation of how the change preserves the project's values

### Architecture Principles

**Service Layer**
- Services are **read-only by default**
- Services **never** modify content unless explicitly called to do so
- Services **respect silence** - returning `null` is always acceptable

**Markdown-First Storage**
- Database stores metadata only
- Content lives in markdown files
- System must survive database deletion

**No Tracking**
- No analytics
- No telemetry
- No usage metrics
- No A/B testing

---

## Export / Portability

All notes, quotes, and thoughts can be exported as a single ZIP of Markdown files.

**Mental health check-ins (companion/)** are excluded from export by design—they are permanently private.

Export is generated from local data and provided for download so users may leave with their writings.

---

## License

[Add your license here - recommend AGPL-3.0 to protect the covenant]

---

## Support

Midnight Pilgrim is self-hosted software. There is no support team, no helpdesk, no SaaS dashboard.

If something breaks, you fix it.  
If something is unclear, you read the code.  
If something is missing, you add it (within the covenant).

**This is intentional.**


