# Claude Code — Build: English Learning Tracker

## Your Role
You are a senior Laravel developer. Your job is to build a complete, fully working web application from scratch based on the requirements below. Do not ask questions — make smart decisions and build everything.

---

## Project Summary

Build a **personal English learning tracker** web application with the following purpose:
- The user plans their weekly English study schedule every Sunday
- They log daily study sessions by skill type
- They attach proof of work (audio files, video recordings, YouTube links)
- The system tracks streaks, progress per resource/book, and skill balance
- The app automatically sends progress updates and weekly summaries to a Telegram group

This is a **single-user system** (no registration needed). The owner logs in with a fixed admin account.

---

## Tech Stack

- **Framework:** Laravel (latest stable)
- **Database:** PostgreSQL
- **Frontend:** Blade templates + TailwindCSS (via CDN) + Alpine.js (via CDN)
- **Auth:** Laravel Breeze (session-based)
- **Telegram:** Telegram Bot API via Laravel HTTP Client
- **File Storage:** Laravel local storage (public disk)
- **Queues:** Database driver
- **Scheduler:** Laravel built-in scheduler

---

## Step-by-Step Instructions

### Step 1 — Laravel Project Setup

1. Create a new Laravel project
2. Configure `.env` for PostgreSQL
3. Install Laravel Breeze (Blade stack)
4. Add these keys to `.env`:
```
TELEGRAM_BOT_TOKEN=
TELEGRAM_CHAT_ID=
```
5. Set queue driver to `database` in `.env`
6. Set up storage link (`php artisan storage:link`)

---

### Step 2 — Database Design

Design and create migrations for the following tables. Use your best judgment for column types, indexes, and foreign keys.

#### `users`
Standard Laravel users table. No changes needed.

#### `weekly_plans`
A study plan created each week (typically on Sunday for the coming week).
- Must store: which week it belongs to (week start date = Monday), total planned hours for the week, optional notes
- One plan per week

#### `daily_plans`
Each day within a weekly plan.
- Must store: which weekly plan it belongs to, the specific date, planned hours for that day
- 7 days per week plan

#### `resources`
Learning materials the user studies from (books, podcasts, YouTube channels, courses, etc.)
- Must store: title, type (book / course / podcast / youtube_channel / other), which English skill it targets, total number of units/chapters (nullable, for books), description (nullable)
- Skill options: grammar, vocabulary, listening, reading, speaking, writing

#### `study_logs`
The main activity log. Every time the user records a study session.
- Must store: which daily plan it belongs to (nullable), which resource was used (nullable), skill type, when the session happened, duration in minutes, which units/chapters were covered (nullable, string), personal notes (nullable), whether this was sent to Telegram (boolean)
- Skill enum: grammar, vocabulary, listening, reading, speaking, writing

#### `study_log_attachments`
Files or links attached to a study log entry as proof of work.
- Must store: which study log it belongs to, attachment type (audio / video / youtube_link / image / other), file path (nullable, for uploads), YouTube URL (nullable), duration in seconds (nullable, for audio/video)
- A log can have multiple attachments

#### `resource_progress`
Tracks how far the user has gotten through a specific resource.
- Must store: which resource, how many units completed, last updated date
- One record per resource

#### `streaks`
Tracks the user's consecutive study days.
- Must store: current streak (days), longest streak ever, last date a study log was recorded
- Only one row in this table (single user)

#### `telegram_logs`
History of every message sent to Telegram.
- Must store: message type (daily_log / weekly_summary / reminder / manual), the message text, sent timestamp, status (sent / failed)

---

### Step 3 — Eloquent Models & Relationships

Create all models with correct relationships:

- `WeeklyPlan` hasMany `DailyPlan`
- `DailyPlan` belongsTo `WeeklyPlan`, hasMany `StudyLog`
- `StudyLog` belongsTo `DailyPlan` (nullable), belongsTo `Resource` (nullable), hasMany `StudyLogAttachment`
- `Resource` hasOne `ResourceProgress`, hasMany `StudyLog`
- `StudyLogAttachment` belongsTo `StudyLog`

Use `$fillable` on all models. Add appropriate casts (enums as strings, booleans, decimals).

---

### Step 4 — Seeders

Create a DatabaseSeeder that:
1. Creates one admin user: email `admin@english.test`, password `password`
2. Creates one `Streak` row with zeroed values
3. Creates 3 sample resources (one book with 115 units, one podcast, one YouTube channel)
4. Creates a sample weekly plan for the current week with 7 daily plans

---

### Step 5 — Service Classes

Create these service classes in `app/Services/`:

#### `TelegramService`
- Method: `send(string $message): bool`
- Uses Laravel HTTP Client to POST to Telegram Bot API
- Logs every send attempt to `telegram_logs` table
- Reads `TELEGRAM_BOT_TOKEN` and `TELEGRAM_CHAT_ID` from config/env
- Returns true on success, false on failure

#### `StreakService`
- Method: `recalculate(): void`
- Checks if a study log exists for yesterday
- If yes: increment current streak
- If no: reset current streak to 0
- Updates longest streak if current exceeds it
- Updates `last_study_date`

#### `WeeklySummaryService`
- Method: `generate(): string`
- Builds a formatted Telegram message for the weekly summary
- Includes: total hours studied vs planned, breakdown by skill, resources worked on, streak status, most studied skill, least studied skill
- Returns the message string (does not send it — sending is done by the caller)

#### `ProgressService`
- Method: `updateResourceProgress(int $resourceId): void`
- Recalculates and updates the `resource_progress` record for a given resource based on all its study logs

---

### Step 6 — Jobs

Create `app/Jobs/SendTelegramMessage.php`:
- Accepts a message string in the constructor
- On handle: calls `TelegramService::send()`
- Should be queued (implements `ShouldQueue`)

---

### Step 7 — Console Commands & Scheduler

Create two Artisan commands:

#### `SendDailyReminder`
- Signature: `telegram:daily-reminder`
- Checks if any study log exists for today
- If none: dispatches `SendTelegramMessage` with a reminder message
- Message format: "⚠️ Bugun hali o'qimadingiz! Ingliz tilini o'rganishni unutmang. 📚"

#### `SendWeeklySummary`
- Signature: `telegram:weekly-summary`
- Uses `WeeklySummaryService` to generate the message
- Dispatches `SendTelegramMessage`

Register both in `routes/console.php` (Laravel 11) or `app/Console/Kernel.php` (Laravel 10):
- `telegram:daily-reminder` → daily at 21:00
- `telegram:weekly-summary` → weekly on Sunday at 21:00

---

### Step 8 — Controllers & Routes

Create resourceful controllers. All routes should be under `auth` middleware.

#### `DashboardController`
- `index()`: loads and passes to view:
    - Today's study logs
    - This week's total planned vs logged hours
    - Current streak and longest streak
    - Skill breakdown for this week (minutes per skill)
    - All resources with their progress
    - Recent 5 study logs

#### `WeeklyPlanController`
- `index()`: list all weekly plans
- `create()`: form to create a new weekly plan (with 7 daily plan inputs)
- `store()`: save weekly plan + all 7 daily plans
- `show($id)`: show a week's plan and its logs

#### `StudyLogController`
- `index()`: list all logs with filters (by skill, by date range)
- `create()`: form with skill selector, resource selector, duration, units, notes, file upload, YouTube URL
- `store()`: save log + attachments, update resource progress, dispatch Telegram message if toggle is on, update streak
- `show($id)`: show a single log with all attachments
- `destroy($id)`: delete a log

#### `ResourceController`
- Full CRUD for resources
- `show($id)`: resource detail with all logs and progress bar

#### `TelegramController`
- `testSend()`: POST route to manually trigger a test Telegram message (for setup verification)

---

### Step 9 — File Uploads

In `StudyLogController@store`:
- Accept file uploads for `audio`, `video`, `image` attachment types
- Store in `storage/app/public/attachments/{log_id}/`
- Save the path to `study_log_attachments`
- Validate: audio (max 50MB, mimes: mp3,m4a,ogg,wav), video (max 200MB, mimes: mp4,mov,webm), image (max 10MB, mimes: jpg,png,webp)

---

### Step 10 — Blade Views

Build clean, responsive views using TailwindCSS (CDN). Use a shared layout (`layouts/app.blade.php`) with a sidebar navigation.

#### Pages to build:

**Dashboard** (`/`)
- Welcome header with current date
- Stats row: today's hours, week progress, current streak 🔥
- Skill balance: horizontal bar chart (pure CSS or simple inline style widths)
- Resource progress bars (title + X/Y units + percentage bar)
- Recent logs list with skill badge and duration

**Weekly Plans** (`/weekly-plans`)
- List of all weekly plans as cards (week date range, planned hours, logged hours)
- Button to create new plan

**Create Weekly Plan** (`/weekly-plans/create`)
- Form: week start date, total notes
- 7 rows (Mon–Sun) each with: date label + planned hours input

**Study Logs** (`/logs`)
- Table/list of all logs
- Filter by skill (dropdown), date range
- Each row: date, skill badge, resource name, duration, attachment icons, Telegram status

**Create Study Log** (`/logs/create`)
- Skill dropdown
- Resource dropdown (from DB)
- Duration (minutes)
- Date + time picker
- Units covered (text input)
- Notes (textarea)
- Attachment section with type selector:
    - If audio/video/image → file upload input
    - If youtube_link → URL text input
- Toggle: "Send to Telegram?" (default: on)

**Resources** (`/resources`)
- List of all resources with type badge, skill badge, progress bar (if book)
- Add new resource button

**Resource Detail** (`/resources/{id}`)
- Resource info
- Progress bar (units completed / total)
- All study logs for this resource listed

---

### Step 11 — Telegram Message Formats

When a study log is sent to Telegram, use this format:

```
📖 Grammar — 45 daqiqa
📚 Manba: Grammar in Use Elementary
📝 Mavzular: Unit 12, Unit 13
💬 Grammar tushunarliroq bo'lib qoldi
🔥 Streak: 5 kun
```

For the weekly summary:
```
📊 Haftalik Hisobot — 14–20 Aprel 2025

⏱ Rejalashtirilgan: 18 soat
✅ Bajarilgan: 14 soat 30 daqiqa (80%)

🧠 Ko'nikmalar bo'yicha:
  📖 Grammar: 3 soat 20 daqiqa
  📝 Vocabulary: 2 soat
  🎧 Listening: 4 soat 10 daqiqa
  📖 Reading: 2 soat
  🎙 Speaking: 1 soat 30 daqiqa
  ✍️ Writing: 1 soat 30 daqiqa

📚 Ishlangan manbalar:
  - Grammar in Use Elementary (Unit 10–15)
  - BBC 6 Minute English (3 epizod)

🔥 Streak: 6 kun
⭐ Eng ko'p: Listening
⚠️ Eng kam: Speaking

Davom eting! Har kun bir qadam oldinga. 💪
```

---

### Step 12 — Final Checks

After building everything:

1. Run `php artisan migrate --seed`
2. Confirm all routes work: `php artisan route:list`
3. Confirm queues table exists: `php artisan queue:table` + migrate
4. Confirm scheduler commands are registered
5. Create a `README.md` with:
    - Setup instructions
    - How to run the queue worker
    - How to test Telegram
    - How to run the scheduler locally

---

## Important Constraints

- Do NOT use any paid packages or services
- Do NOT use Livewire or Vue/React — plain Blade + Alpine.js only
- All text in views can be in Uzbek or English — use what makes sense
- Do NOT hard-code the Telegram token or chat ID anywhere in code — always use env/config
- All Telegram sends must go through the queue — never a synchronous HTTP call in a controller
- Handle file upload errors gracefully — show a user-friendly message
- The app must work offline (no external CDN calls that break functionality) — TailwindCSS via CDN is fine for styling only

---

## Deliverable

A fully working Laravel application that can be:
1. Cloned
2. `.env` configured
3. `composer install` + `php artisan migrate --seed` run
4. Logged into with `admin@english.test` / `password`
5. Immediately used to log study sessions and send to Telegram
