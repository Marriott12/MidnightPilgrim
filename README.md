# Midnight Pilgrim 🌑

**Midnight Pilgrim** is a local-first knowledge and reflection system.

It is not a productivity app.  
It is not a SaaS.  
It is not an AI toy.

It is a place to **walk with ideas over time**.

---

## Core Philosophy (Read This First)

Midnight Pilgrim is built on one unbreakable rule:

> **Markdown is the brain.  
> Laravel is the steward.**

Everything else flows from this.

### What this means in practice

- All written content lives as `.md` files
- The database stores **metadata only**
- The system must survive if:
  - The database is deleted
  - Laravel is removed
  - This repository is archived and reopened years later
- Silence is a feature
- Automation is earned, not assumed

If a decision must be made between **power and clarity**,  
**clarity always wins**.

---

## Project Structure

PROJECT STRUCTURE
-----------------
midnight-pilgrim/
├── app/
│   ├── Models/
│   │   ├── Note.php
│   │   ├── Quote.php
│   │   ├── DailyThought.php
│   │   └── Interaction.php
│   ├── Services/
│   │   ├── NoteService.php
│   │   ├── QuoteEngine.php
│   │   ├── DailyThoughtEngine.php
│   │   └── AssistantService.php
│   ├── Console/
│   │   └── Commands/
│   │       └── GenerateDailyThought.php
│   └── Policies/
│       └── SilencePolicy.php
│
├── database/
│   ├── migrations/
│   └── database.sqlite
│
├── storage/
│   ├── vault/            // Markdown notes live here (Obsidian‑compatible)
│   ├── quotes/
│   └── thoughts/
│
├── routes/
│   └── api.php
│
└── README.md
*/


### Critical Directories

- **`storage/app/vault`**  
  This is the **mind** of Midnight Pilgrim.  
  Never delete it. Never auto-refactor it. Never hide it.

- **`database/database.sqlite`**  
  This is replaceable. Helpful, but not sacred.

---

## What Exists Today (Phase 1)

Currently implemented:

- `Note` model (metadata only)
- Markdown note storage
- YAML front-matter
- Local-first architecture
- Obsidian compatibility

Not yet implemented (by design):

- Controllers
- APIs
- UI
- AI / embeddings
- Search
- Authentication
- Automation

Midnight Pilgrim grows **slowly and intentionally**.

---

## Local Setup

### Requirements

- PHP (latest stable)
- Composer
- SQLite

### Install Dependencies

```bash
composer install

---

## Ethical Contract

Midnight Pilgrim is a deliberate, local-first companion for reflection. This section is a short promise.

- **Not a therapist:** Midnight Pilgrim is not medical or mental-health advice. It does not diagnose or treat.
- **No unsolicited intervention:** The system will not escalate, call services, or push users toward outside providers.
- **Encourage human connection:** When repeated high-intensity signals appear, the system may gently suggest reaching out to trusted people — never as an automated escalation.
- **Silence is intentional:** Silence is the default and a core part of the experience.
- **Local-first data:** User writings are stored locally and exportable; the system is designed for portability and user ownership.

## Do Not Cross (Developer Notes)

The following are deliberate boundaries to prevent feature creep:

- No gamification (no streaks, no badges, no metrics). 
- No unread counters or urgency-colored UI elements.
- No automatic surfacing of private content.
- No unsolicited clinical advice or prognosis.

If you are adding new features, ask: "Does this add noise or pressure?" If yes, do not add it.

## Export / Portability

All notes, quotes, and thoughts can be exported as a single ZIP of Markdown files via the `/export` route. This requires no account — the export is generated from local data and provided for download so users may leave with their writings.

---

## Core Idea — Concentric Layers (LOCKED IN)

Midnight Pilgrim stores experience in three concentric layers and enforces strict boundaries:

1. **Inner Circle — Personal (Default)**
  - Local-first, private memory, silence-first. New content is always `private` by default.

2. **Middle Layer — Reflective (Opt-in)**
  - Selected items can be marked `reflective` and surfaced carefully within the personal interface. Surfacing is always opt-in and never automatic.

3. **Outer Layer — Waystone / Open Pilgrim**
  - Only `shareable` items appear here. Public interfaces show structure, practices, and fictional examples — not personal content.

Hard rules enforced in code:

- Visibility is the keystone: `private` | `reflective` | `shareable`.
- New items default to `private`.
- Nothing becomes `shareable` without the explicit sharing ritual (no bulk sharing, no suggestions).
- Mental-health artifacts (check-ins, interactions, reflections derived from them) may never be marked `shareable`.

If you are a developer: do not cross these boundaries. They are intentional safeguards to protect users.


