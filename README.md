# CIDIC RECRUITMENT — سدك للاستقدام

موقع شركة استقدام عمالة منزلية في المملكة العربية السعودية، مبني بالكامل على Laravel 11
كتطبيق **مونوليث واحد** (موقع عام + لوحة تحكم في نفس المشروع).

A Saudi domestic-workers recruitment website: Arabic-first, RTL, Laravel 11 monolith.

---

## Stack

| Layer      | Technology                                   |
|------------|----------------------------------------------|
| Framework  | Laravel 11 (PHP 8.2+)                        |
| Views      | Blade templates (server-rendered)            |
| CSS        | Bootstrap 5 (RTL build) + custom design tokens |
| JS         | Alpine.js + Bootstrap bundle (minimal)       |
| Database   | MySQL 8                                      |
| Storage    | Laravel Storage (`storage/app/public`)       |
| Build      | Vite                                         |

No React, Vue, Next.js, separate API, or microservices — everything is one Laravel application.

---

## Requirements

- PHP **8.2+** with `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `fileinfo`, `gd`
- Composer 2
- Node.js 18+ and npm
- MySQL 8 (or MariaDB 10.6+)

---

## Installation

```bash
# 1. Install dependencies
composer install
npm install

# 2. Environment
cp .env.example .env
php artisan key:generate

# 3. Create the database, then point .env at it
#    mysql -u root -e "CREATE DATABASE cidic CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
#    DB_DATABASE=cidic
#    DB_USERNAME=root
#    DB_PASSWORD=

# 4. Schema + demo data
php artisan migrate --seed

# 5. Public disk symlink (required for images / CV files)
php artisan storage:link

# 6. Build assets
npm run build      # or: npm run dev

# 7. Serve
php artisan serve
```

Open <http://localhost:8000> for the public site and
<http://localhost:8000/admin/login> for the dashboard.

---

## Development credentials

> **⚠️ DEVELOPMENT ONLY — change these before deploying to production.**

| Role        | Email                | Password   |
|-------------|----------------------|------------|
| Super Admin | `admin@example.com`  | `password` |
| Staff       | `staff@example.com`  | `password` |

Change them from **لوحة التحكم → المستخدمون**, or reseed with your own values.

---

## Public routes

| Route                          | Description                       |
|--------------------------------|-----------------------------------|
| `/`                            | الرئيسية — homepage               |
| `/services`, `/services/{slug}`| خدماتنا                           |
| `/nationalities`               | الجنسيات                          |
| `/candidates`                  | السير الذاتية (search + filters)  |
| `/candidates/{slug}`           | تفاصيل السيرة الذاتية             |
| `/request`                     | طلب استقدام عام                   |
| `/request/candidate/{slug}`    | طلب عاملة محددة                   |
| `/about`, `/contact`, `/faq`   | صفحات ثابتة                       |
| `/privacy-policy`, `/terms`    | صفحات قانونية (CMS-editable)      |
| `/sitemap.xml`, `/robots.txt`  | SEO                               |

### Candidate filtering

Filters are plain GET parameters and survive pagination:

```
/candidates?nationality=philippines&category=nanny&experience=4&age_min=20&age_max=40&language=arabic&sort=experience
```

`nationality` and `category` accept either a slug or a numeric id.

---

## Admin dashboard (`/admin`)

Candidates (CV upload: photo, PDF, video) · customer requests with status history ·
general recruitment requests · services · nationalities · categories ·
how-it-works · why-choose-us · testimonials · FAQs · contact messages ·
CMS pages · site settings · users & roles.

### Bulk CV upload (`/admin/candidates/bulk`)

Upload many PDF CVs at once when you have no structured data for each worker:

1. Select multiple PDFs (up to 50 per batch, 10 MB each).
2. Choose **one** nationality and category for the whole batch.
3. Submit — one candidate record is created per file.

The candidate's name is derived from each filename, with document noise removed
(`CV`, `resume`, `copy`, `final`, years, `(2)` counters). `CV-Sara-Sri-Lanka-2024.pdf`
becomes **Sara Sri Lanka**. Arabic filenames are supported. A preview table shows the
derived names before uploading, and every name stays editable afterwards.

Because a PDF carries no structured fields, `profession` defaults to the chosen
category's Arabic name and `years_of_experience` starts at 0. The CV file itself is
**required** — it is the only source of the candidate's identity.

Each file is imported in its own transaction, so one malformed file cannot roll back
the batch; failures are reported by filename.

### Roles

| Role          | Permissions                                                        |
|---------------|--------------------------------------------------------------------|
| `super_admin` | Everything                                                          |
| `admin`       | Candidates, requests, services, content, settings                   |
| `staff`       | Candidates and requests only                                        |

Permissions are enforced by Gates (`manage_candidates`, `manage_requests`,
`manage_services`, `manage_content`, `manage_settings`, `manage_users`).

---

## Identifier generation

Identifiers are derived from the row's auto-increment id **after insert**, inside a
transaction — never `count() + 1`, which races and reuses numbers after deletions.

- Candidate reference: `CV-00001`
- Request number: `REQ-2026-000001`

---

## Site settings

All contact details, social links, SEO defaults and homepage hero content come from the
`site_settings` table — nothing is hardcoded in Blade. Read them with the cached helper:

```php
setting('company_phone');
setting('whatsapp', '0500000000');   // with default
whatsapp_url('نص الرسالة');           // builds a wa.me link
```

Settings are cached forever and the cache is flushed automatically on save.

---

## Testing

```bash
php artisan test
```

The suite runs against an in-memory SQLite database (configured in `phpunit.xml`), so it
needs no MySQL server. Application code targets MySQL.

Covered: homepage, candidate listing/filters/profile, available vs. unavailable request
rules, request validation and duplicate protection, request-number format and uniqueness,
status history, contact form, admin authentication and authorization, candidate CRUD with
uploads, settings cache invalidation.

---

## Security notes

- CSRF protection on every form; `POST → Redirect → GET` after each submission
- FormRequest validation for all user input; Saudi mobile format enforced
- Rate limiting on all public write endpoints and on admin login
- Uploads validated by MIME type and size; stored under generated UUID filenames
  (client filenames are never trusted or reused)
- Blade auto-escaping everywhere; `{!! !!}` is used only for admin-authored CMS content
- Mass-assignment protection via explicit `$fillable`
- Passwords hashed via Laravel's `hashed` cast
- Old files deleted when replaced; soft-deleted records retain their media

---

## Project layout

```
app/
├── Enums/            AvailabilityStatus, RequestStatus, UserRole, Permission, ...
├── Http/
│   ├── Controllers/  public controllers + Admin/ namespace
│   ├── Middleware/   EnsureUserIsAdmin
│   └── Requests/     Admin/ and Public/ FormRequests
├── Models/           16 Eloquent models
├── Services/         CandidateService, CandidateRequestService, FileUploadService,
│                     SettingService, ReferenceNumberService, ActivityLogger
└── Support/          helpers.php, ArabicSlug

resources/views/
├── layouts/          app.blade.php (public), admin.blade.php
├── partials/         header, footer, whatsapp, flash
├── components/       candidate-card, service-card, nationality-card, ...
├── pages/            home, services, nationalities, about, contact, faq, legal
├── candidates/       index, show
├── requests/         candidate, general, success
├── errors/           403, 404, 419, 429, 500, 503 (Arabic, branded)
└── admin/            dashboard + one folder per module
```

---

## Notes

- Arabic slugs are transliterated (`ArabicSlug`) because `Str::slug()` strips Arabic
  characters entirely, which would produce empty URLs.
- Mail is optional: `MAIL_MAILER=log` by default and no feature depends on mail delivery.
- Activity logging never throws — a logging failure cannot break a user request.

---

## لوحة السير الذاتية (CV Panel)

A self-contained panel at `/cv-panel` where coordinators upload worker CVs (PDF)
per nationality, customer-service agents reserve them for clients, and managers
supervise. Public nationality pages show only CVs that are still available.

### Roles

Panel access is governed by `users.department` (separate from the `role` column
that governs the main dashboard). A user with no department cannot reach the
panel, even if they hold dashboard permissions. Super admins reach everything.

| Department | Can do |
|---|---|
| `coordination` | Upload and delete CVs **in their own nationalities only**; follow up reserved CVs and mark them assigned. Cannot reserve. |
| `customer_service` | Reserve CVs, search clients, and act on **their own** reservations. |
| `branch_manager` | Reserve; manage panel users and coordinator↔nationality links. Cannot act on someone else's reservation. |

Coordinators are scoped to the nationalities linked to them through the
`admin_nationality` pivot. Everyone else sees all nationalities.

### Flows

**Upload** — pick a nationality, experience and religion (the last two are
mandatory because the public site filters on them), then upload up to 100 PDFs
at 10 MB each. The worker's name comes from the file name without its
extension. A file whose name already exists is skipped and reported. An
optional purge clears that nationality's stale CVs first; it never touches
anything reserved, held for a client, or bound to a contract.

**Reserve** — pick a registered client, or create one inline from just a name
and phone (WhatsApp leads are usually unregistered). The row is re-read under a
pessimistic lock, so two agents reserving the same CV cannot both succeed.

**After reserving** — only the reserver or a super admin may cancel, record a
Tamara payment, or create the contract. **Create contract** writes a
`recruitment_contracts` row, moves the worker to `assigned`, and the row then
leaves the panel list. `assigned` is the end of the cycle and cannot be undone
from the panel.

### Rules that the code enforces

1. **A reservation never expires.** There is no release job, no expiry
   reminder, and no notification text promising one. `RESERVATION_HOURS` is a
   display constant only.
2. **Withdrawal from the public site is permanent.** The first time a worker is
   booked, `cv_withdrawn_at` is stamped. Cancelling the reservation does *not*
   clear it — only `workers:restore-withdrawn` does.
3. **`available` never coexists with a client or an open contract.** Enforced in
   `Worker::booted()`. Every status change must go through Eloquent:
   `Worker::where(...)->update(['status' => ...])` bypasses the guard and is the
   bug that produced workers shown as "متاحة" while linked to a client.
4. **Deletes are soft**, and a booked CV is never deletable. The row and the
   file are both kept.
5. **CV files live on the private `cv_private` disk** and are streamed through
   authorized controllers with Range and ETag support. The stored file name is
   never exposed; responses are always named `cv-{id}.pdf`.
6. **Logging never blocks the action it records** — every write is wrapped.

### Commands

All default to a dry run; pass `--apply` to write.

```bash
php artisan workers:restore-withdrawn [--nationality=ID] [--apply]
php artisan workers:fix-available-with-client [--apply]
php artisan workers:sync-contract-status [--apply]
```

- `restore-withdrawn` — the only way a withdrawn CV returns to the public site.
  Clears `cv_withdrawn_at` for CVs that are available, unbooked and uncontracted.
- `fix-available-with-client` — repairs rows left inconsistent by a direct query
  update: sets them to `assigned` and fills the client from the contract.
- `sync-contract-status` — realigns statuses with contracts and clients.

Nothing is scheduled: no cron job may change a reservation.

### Setup

```bash
php artisan migrate
php artisan db:seed --class=CvPanelSeeder   # default branch + nationality ISO codes
npm run build
```

Then give at least one user a department:

```php
User::find(1)->update(['department' => 'coordination']);
```

### Public catalogue

`/cvs`, `/nationality/{code}` (ISO alpha-2, e.g. `/nationality/et`), `/cvs/{id}`
and `/cvs/{id}/pdf`. These show only workers matching
`Worker::scopePubliclyVisible()` — active, available, with a file, never
withdrawn. Passport and phone numbers are never rendered. Once a CV is reserved,
its PDF route returns an "تم حجز هذه العاملة" page rather than a bare 404.

### Tests

```bash
php artisan test tests/Feature/CvPanel
```

Covers the reservation race, the model guards, permanent withdrawal, the
permission matrix per role, bulk delete skipping booked rows, duplicate-skip on
upload, the public site hiding reserved and withdrawn CVs, and the PDF route
after a reservation.
