# Conversation API Reference

## Base URL
`http://localhost:8000/conversation`

## Authentication
All requests use **anonymous UUID** stored in `pilgrim_uuid` httpOnly cookie.
No authentication required, no user accounts.

---

## Endpoints

### 1. View Conversation Interface
**GET** `/conversation`

Shows conversation UI with resume gate or active session.

**Response:** HTML page

---

### 2. Resume Existing Session
**POST** `/conversation/resume`

Resume an existing active session.

**Form Data:**
- `mode` (optional): `quiet` or `company` - defaults to `quiet`

**Response:** Redirect to `/conversation`

---

### 3. Begin New Session
**POST** `/conversation/begin`

Hard deletes previous session and creates new one.

**Form Data:**
- `mode` (required): `quiet` or `company`

**Response:** Redirect to `/conversation`

---

### 4. Send Message
**POST** `/conversation/send`

Send a message and get response.

**JSON Body:**
```json
{
  "message": "Your message here",
  "mode": "quiet" // or "company"
}
```

**Response:**
```json
{
  "response": "Assistant response text",
  "silence": false
}
```

Or if silent:
```json
{
  "response": null,
  "silence": true
}
```

**Headers:**
- `Content-Type: application/json`
- `X-CSRF-TOKEN: {token}`

---

### 5. Get Random Line
**POST** `/conversation/random`

Pull a weighted random line from cache.

**Response:**
```json
{
  "line": "A short reflective line from your notes"
}
```

**Headers:**
- `X-CSRF-TOKEN: {token}`

---

### 6. Generate Thoughts
**POST** `/conversation/thoughts`

Generate 2-4 line reflective compression of recent activity.

**Response:**
```json
{
  "thoughts": "Compressed reflective text"
}
```

**Headers:**
- `X-CSRF-TOKEN: {token}`

**Note:** Requires active session.

---

### 7. Get Adjacent Notes
**POST** `/conversation/adjacent`

Find semantically similar notes.

**JSON Body:**
```json
{
  "query": "Search query or current thought"
}
```

**Response:**
```json
{
  "notes": [
    {
      "title": "Note title",
      "excerpt": "Short excerpt..."
    },
    {
      "title": "Another note",
      "excerpt": "Another excerpt..."
    }
  ]
}
```

**Headers:**
- `Content-Type: application/json`
- `X-CSRF-TOKEN: {token}`

---

### 8. Change Mode
**POST** `/conversation/mode`

Switch between quiet and company modes.

**JSON Body:**
```json
{
  "mode": "company" // or "quiet"
}
```

**Response:**
```json
{
  "mode": "company"
}
```

**Headers:**
- `Content-Type: application/json`
- `X-CSRF-TOKEN: {token}`

---

### 9. Close Session
**POST** `/conversation/close`

Close current active session (mark as closed, don't delete).

**Response:**
```json
{
  "closed": true
}
```

**Headers:**
- `X-CSRF-TOKEN: {token}`

---

## Error Handling

All endpoints handle errors gracefully:
- Missing UUID → Creates new one
- No active session → Returns 404 or creates new
- AI failure → Falls back to rule-based or silence
- Invalid input → Returns validation errors

---

## Privacy Notes

1. **No IP tracking** - Never logged or stored
2. **No analytics** - No metrics, no engagement tracking
3. **Anonymous** - UUID only, no personal data
4. **Local-first** - All data in local SQLite database
5. **Hard deletion** - "Begin again" permanently deletes old data
6. **httpOnly cookies** - JavaScript cannot access UUID
7. **Silence is valid** - `null` responses are intentional and meaningful

---

## Console Commands

### Rebuild Random Lines Cache
```bash
php artisan conversation:rebuild-cache
```

Extracts short meaningful lines from all notes.
- Lines: 20-100 characters
- Not questions
- Weighted randomness (currently equal weight)

---

## Environment Variables

### Optional AI Integration
```env
OPENAI_API_KEY=sk-...
```

If not set, system uses rule-based responses.

---

## Database Tables

### conversation_sessions
- Stores active/closed sessions
- Linked to UUID
- Has mode (quiet/company)

### messages
- Belongs to session
- Role: user or assistant
- Cascade deletes with session

### short_lines_cache
- Weighted random lines
- Extracted from notes
- No metadata attached

### notes.embedding
- JSON column (nullable)
- For future semantic search
- Not yet implemented
