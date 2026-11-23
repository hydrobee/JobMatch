# JobMatch

A personal job-search assistant built with Laravel. It collects job listings, scores each one
against your skills, tracks your applications and emails you a daily digest.

## What it does

- **Collects jobs** from RSS/Atom feeds every morning (07:00 Kuching time), or by hand-adding a posting.
  The same job seen twice is saved once.
- **Matches skills.** Skills are detected in each posting and compared with your skill list, giving a
  match % plus the skills you have and the ones you are missing.
- **Tracks applications** on a Saved / Applied / Interview / Offer / Rejected board, with notes, contact,
  resume version, follow-up dates and a stage history.
- **Emails a daily digest** (08:00) of new good matches and follow-ups that are due.
- **Dashboard** with counts, best matches, follow-ups due and the skills you are missing most.
- **Optional AI skill extraction** with Gemini (see below). The free keyword matcher is the default.
- **Resume tailoring.** Paste your master resume, or upload it as a PDF (text PDFs only; install the reader with
  `composer require smalot/pdfparser`). Then press **Tailor my resume** on any job. You get a copy for
  that job, a list of suggested changes, which of the posting's keywords your resume already has, and an ATS
  checklist (standard headings, contact details, no tables or fancy symbols, length). Download it as `.txt`, or
  open the print page and save it as a PDF. The checks work without AI; the suggestions and draft need an API key.

## Setup

```bash
composer install
cp .env.example .env        # then set the DB_* values for PostgreSQL
php artisan key:generate
php artisan migrate
npm install && npm run build
php artisan serve
```

Register an account, then:

1. **Skills**: add the skills you have.
2. **Sources**: add an RSS/Atom feed URL (most job boards offer one for a saved search), or use **Jobs > Add job**.
3. Press **Fetch now** on a source to try it.

### Daily fetch and digest

```bash
php artisan schedule:work        # local development
```

On a server, run `php artisan schedule:run` every minute from cron. You can also run the jobs by hand:

```bash
php artisan jobmatch:fetch
php artisan jobmatch:digest
```

Mail goes to `storage/logs/laravel.log` until you set real `MAIL_*` values in `.env`.

### AI skill extraction (optional)

In `.env`:

```
JOBMATCH_EXTRACTOR=ai
GEMINI_API_KEY=your-key
```

Gemini reads each new posting and returns the skills it asks for. If there is no key, or the API call
fails for any reason, the app falls back to keyword matching, so imports never break.

Both features use `gemini-3.5-flash-lite` by default; change `JOBMATCH_AI_MODEL` and `JOBMATCH_RESUME_MODEL` in `.env` to use another Gemini model.
For resume tailoring, the AI is told to reorder and reword
only what is already in your master resume and to list missing requirements as gaps instead of adding them.
Always read the result before you send it.

The keyword matcher uses the dictionary in `config/jobmatch.php`. Add your own skills and aliases there.

## Tests

```bash
php artisan test
```

Tests run on in-memory SQLite. GitHub Actions runs them on every push (`.github/workflows/tests.yml`).

## Notes

- Job postings are stored in `job_listings` because Laravel's own queue table is already called `jobs`.
- Only RSS and Atom feeds are supported as automatic sources. Scraping sites that do not offer a feed is not built in,
  and many sites' terms forbid it.
- Source URLs are fetched by the server. If you ever host this publicly with open registration,
  add a check that blocks private and internal addresses.
