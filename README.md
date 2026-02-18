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

**4. Local-First, With Optional Encrypted Sync (2026)**
- Data stays on your machine by default
- **Opt-in encrypted cloud sync** is available for backup/sync (see below)
- All sync is end-to-end encrypted; only you hold the key
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


## Security, Privacy, and Observability Improvements

### Data Encryption
- Sensitive data (e.g., session UUIDs, emotional snapshots) is encrypted at rest using Laravel's built-in encryption.
- See `app/Support/Encryption.php` for utility methods.

### Input Validation & Rate Limiting
- All API endpoints use Laravel validation and custom middleware for input sanitization.
- Rate limiting is enforced via `ThrottleRequests` middleware (see `app/Http/Middleware/ApiRateLimit.php`).

### Logging & Privacy
- Logs are sanitized to avoid storing sensitive user data.
- See `app/Support/LogSanitizer.php` for implementation.

### Error Tracking & Monitoring
- Sentry integration is available (optional, opt-in via `.env`).
- See `config/sentry.php` and `app/Providers/SentryServiceProvider.php`.

---

## Observability & Monitoring

- Error tracking is available via Sentry (set `SENTRY_DSN` in `.env`)
- For local debugging, use Laravel Telescope (install via Composer and register the service provider)
- All errors and exceptions are logged with sensitive data sanitized

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

---

## Developer Setup & Troubleshooting

1. Clone the repository and run `composer install` and `npm install`.
2. Copy `.env.example` to `.env` and set your `APP_KEY` and any API keys (see below).
3. Run `php artisan key:generate` to generate a new app key.
4. Ensure `storage/` and `bootstrap/cache/` are writable (`chmod -R 775 storage bootstrap/cache`).
5. Run `php artisan migrate` to set up the database.
6. Start the server with `php artisan serve` and build assets with `npm run build`.
7. Run tests with `php artisan test`.

### Common Issues
- **Permission errors:** Ensure correct permissions on `storage/` and `bootstrap/cache/`.
- **Missing .env:** Copy `.env.example` to `.env` and set required values.
- **Database errors:** Ensure `database/database.sqlite` exists and is writable.
- **AI not responding:** Set `OPENAI_API_KEY` in `.env` or fallback to rule-based responses.
- **Frontend not updating:** Run `npm run build` after changes to JS/CSS.

---

## Error Tracking, Monitoring, and Secrets

- **Error Tracking:**
  - Sentry integration is available. Set `SENTRY_DSN` in `.env` to enable error reporting.
  - All errors are sanitized before logging or reporting.
- **Performance Monitoring:**
  - Use Laravel Telescope for local debugging (install via Composer, see Laravel docs).
- **Secrets Management:**
  - All secrets (API keys, DB credentials) are stored in `.env` (never commit this file).
  - Example keys:
    - `OPENAI_API_KEY=sk-...` (for LLM integration)
    - `SENTRY_DSN=...` (for error tracking)
    - `APP_KEY=...` (Laravel encryption)
  - See `.env.example` for all available environment variables.

---

## Optional Encrypted Cloud Sync

Midnight Pilgrim now supports **optional, encrypted cloud sync** for users who wish to back up or synchronize their vault across devices. See the top of this README for the full covenant update and privacy guarantees.

- **Opt-in only**: Disabled by default. You must explicitly enable it.
- **End-to-end encrypted**: All content is encrypted client-side before leaving your device. Only you hold the decryption key.
- **Privacy-preserving**: No analytics, tracking, or third-party sharing. No server can read your data.
- **Strict boundaries**: Mental health data (`companion/`) and any content not marked `shareable` or `reflective` is **never** synced unless you explicitly include it.
- **Local-first**: You may use Midnight Pilgrim entirely offline, forever. Cloud sync is a convenience, not a requirement.

**How to enable:**
1. Go to Settings → Cloud Sync
2. Generate a sync key (store this securely!)
3. Select which folders to sync (default: `vault/`, `quotes/`, `thoughts/`)
4. Exclude any files/folders you wish
5. You may disable and delete all cloud data at any time

**Warning:** If you lose your sync key, your cloud data cannot be decrypted or recovered by anyone.

---

## Encrypted Cloud Sync Service

The **Encrypted Cloud Sync Service** is a custom service that handles the encryption and decryption of content for users who enable cloud sync. It is not a standard Laravel service and must be imported manually.

### Importing the Service

To use the Encrypted Cloud Sync Service, you must import it into your application:

```php
use App\Services\EncryptedCloudSyncService;
```

### Using the Service

The service provides methods for encrypting and decrypting content:

```php
$syncService = app(EncryptedCloudSyncService::class);

// Encrypt content
$encrypted = $syncService->encrypt($content);

// Decrypt content
$decrypted = $syncService->decrypt($encrypted);
```

### Configuration

The service is configured in the `.env` file:

```env
ENCRYPTED_CLOUD_SYNC_ENABLED=true
ENCRYPTED_CLOUD_SYNC_KEY=your_secret_key
```

### Security

The service is secure and private. Only users who enable cloud sync can use it. The keys are stored in the `.env` file and are not exposed to the public.

---

## Local Analytics Dashboard (Opt-In)

Midnight Pilgrim now includes an **opt-in, local-only analytics dashboard**. No data ever leaves your device. Analytics are disabled by default and must be enabled in settings. No engagement metrics, no tracking, no sharing. See `/analytics` for your private dashboard.

---

## Plugin & Extension API (Experimental)

A new **plugin/extension API** is available for local-only plugins. All plugins must respect privacy boundaries and cannot access mental health data or export content without explicit user consent. See `/plugins` for the manager and upcoming documentation.

---

## Smarter AI Fallback

If the main AI service is unavailable, you can now enable fallback options:
- Local LLM (if installed)
- Cached responses
- Silence (default)

Configure at `/ai-fallback`. No data is sent to third parties without your explicit consent.

---

## Streamlined Onboarding

A new onboarding/first-run experience is available at `/onboarding`. This page introduces the philosophy, privacy boundaries, and available features (analytics, plugins, sync). No tracking, no pressure, no engagement metrics.

---


