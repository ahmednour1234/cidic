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
