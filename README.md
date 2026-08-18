# Rihal — মাদ্রাসা ব্যবস্থাপনা প্ল্যাটফর্ম (Madrasha Management Platform)

**Rihal** is a bilingual (Bengali-first) management platform for Bengali *madrasas* in
Bangladesh. It covers the full operational loop of a madrasa: students, teachers,
attendance, exams & results, fees/finance, and notices — with multi-tenant isolation
and role-based access control.

> Source-level branding note: the project was originally scaffolded as "Sabaaq / Sabaaq
> Next"; all user-facing copy and code identifiers now read **Rihal**. (No filename-level
> rename was performed — dirs are still `laravel/` and `nuxt/`.)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | **Laravel 11** (PHP 8.3), Sanctum auth, REST API |
| Frontend | **Nuxt 4** (Vue 3, Pinia), TypeScript, Bengali `bn` locale |
| Database | **MySQL 8.4** |
| Tests | PHPUnit (feature/integration) |
| Tooling | Composer, Node 22, Vite/Nitro |

**Architecture decisions**
- **REST-first** (no GraphQL). All endpoints under `/api/v1`.
- **Multi-tenancy** via an Eloquent global scope + `tenant_id` column on every
  tenant-scoped model; a middleware resolves the active tenant from the authenticated user.
- **Auth**: Laravel Sanctum bearer tokens. Frontend stores the token, restores the session
  on load, and redirects to `/login` on HTTP 401.
- **Timezone**: `Asia/Dhaka`. Server-side locale: `bn`.

---

## Repository Layout

```
SabaaqNext/
├── laravel/            # Laravel 11 API (backend)
│   ├── app/
│   │   ├── Http/Controllers/Api/V1/   # ~30 resource controllers
│   │   ├── Models/                    # 57 Eloquent models
│   │   └── Http/Middleware/           # tenant resolution, role/permission
│   ├── database/
│   │   ├── migrations/                # 16 base + 2 fix migrations
│   │   └── seeders/                   # 5 seeders (see below)
│   └── routes/api.php                 # 164 route definitions
└── nuxt/               # Nuxt 4 SPA/SSR (frontend)
    └── app/
        ├── pages/                     # 24+ route pages
        ├── components/layout/         # AppSidebar, AppTopBar
        ├── composables/               # useAuth, useApi
        └── plugins/api.client.ts      # axios instance + 401 interceptor
```

---

## Prerequisites

- **PHP 8.3+** with extensions: `pdo_mysql`, `mbstring`, `openssl`, `tokenizer`, `xml`, `ctype`, `json`, `bcmath`, `fileinfo`.
- **Composer 2**
- **Node.js 22+** and npm
- **MySQL 8.4** running and reachable (default `127.0.0.1:3306`).

---

## Setup

### 1. Backend (Laravel)

```bash
cd laravel

# Dependencies
composer install

# Environment
cp .env.example .env
php artisan key:generate

# Configure DB credentials in .env (DB_DATABASE, DB_USERNAME, DB_PASSWORD)
# Default expects a MySQL schema named `rihal_next`.

# Database + demo data
php artisan migrate:fresh --seed

# Run the API (default http://localhost:8000)
php artisan serve
```

The seeder chain (`DatabaseSeeder`) runs:
1. `RolePermissionSeeder` — roles & permissions.
2. `PlatformAdminSeeder` — super-admin (`admin@rihal.app`).
3. `DemoTenantSeeder` — a demo tenant with classes, sections, subjects, students,
   teachers, and a tenant admin.

### 2. Frontend (Nuxt)

```bash
cd nuxt

npm install

# Optional: override the API base (defaults to http://localhost:8000/api/v1)
# NUXT_PUBLIC_API_BASE=http://your-api/api/v1

npm run dev          # http://localhost:3000  (or `npm run build` for production)
```

The frontend talks to `http://localhost:8000/api/v1` by default. Change it via
`NUXT_PUBLIC_API_BASE` (Nuxt) or `config.public.apiBase` in `nuxt.config.js`.

---

## Docker (one-command local stack)

A `docker-compose.yml` spins up the full stack — MySQL 8.4, the Laravel API
(port 8000), and the Nuxt frontend (port 3000):

```bash
# from repo root
docker compose up --build
```

- API: http://localhost:8000  ·  Frontend: http://localhost:3000
- On startup the API container runs `migrate:fresh --seed`, so the database is
  seeded with demo data automatically.
- Override the API base the frontend calls with `NUXT_PUBLIC_API_BASE`
  (defaults to `http://api:8000/api/v1` inside the compose network).

> Images: `laravel/Dockerfile` (PHP 8.3 FPM + Composer) and `nuxt/Dockerfile`
> (Node 22, production `nuxt build`).

---

## CI

`.github/workflows/ci.yml` runs on every push/PR to `master`/`main`:
- **Laravel tests** — MySQL 8.4 service, `migrate:fresh --seed`, then `php artisan test`.
- **Nuxt build** — `npm install` + `npm run build` (Node 22).

## Production hardening

- **Rate limiting** — `POST /auth/login` is throttled to 5/min; `POST /notifications/absence` and `POST /notifications/fee-due` to 20/min (per user) via Laravel's `throttle` middleware.
- **Notification dispatch seam** — `config/rihal.php` + env vars (`NOTIFICATION_SMS_DRIVER`, `NOTIFICATION_EMAIL_DRIVER`) select a real gateway (Twilio / SMTP / BD SMS). When left `null` (default), external dispatch is **logged only** and the in-app record is always created. Credentials: `TWILIO_*`, `BD_SMS_*`.
- **Read receipts** — guardians can mark a single notification or all as read (`POST /notifications/{id}/read`, `POST /notifications/read-all`); the portal highlights unread items.
- **Role-scoped self-service** — `/student/me` (role=student) and `/teacher/assignments` (role=teacher) enforce role via `abort_if(403)`, and the sidebar renders a role-appropriate nav.

---

## UI / Design system

The frontend is a custom, dependency-free **premium design system** (no UI library):

- **Global tokens** — `nuxt/app/assets/css/main.css` defines the full theme (brand green `#145032`, gold accent `#d4af37`, semantic colors, shadows, radii, layout vars) and shared component styles (buttons, cards, tables, forms, badges, alerts).
- **Loaded globally** via the `css:` key in `nuxt.config.js` (previously the file existed but was never imported — that was the "plain text / no style" bug).
- **Bengali-first typography** — Noto Sans Bengali + Inter from Google Fonts in `nuxt.config.js` `app.head`; applied via `app.vue` (`<NuxtLayout>` wraps every page so the sidebar/topbar chrome renders) and `body.font-bn`.
- **Auth screens** use a branded gradient + centered card. Fully **responsive**.

After `migrate:fresh --seed`:

| Role | Email | Password |
|------|-------|----------|
| Platform Admin (super_admin) | `admin@rihal.app` | *(set in `PlatformAdminSeeder`)* |
| Tenant Admin | `admin@demo.bd` | `admin123` |
| Teacher | `teacher1@demo.bd` … `teacher4@demo.bd` | `teacher123` |
| Guardian | `guardian@demo.bd` | `guardian123` |

---

## API Overview

All routes are under `/api/v1` and require a Sanctum bearer token
(`Authorization: Bearer <token>`), except `POST /auth/login` and `POST /auth/register`.

| Group | Endpoints |
|-------|-----------|
| Auth | `/auth/login`, `/auth/register`, `/auth/logout`, `/auth/user` |
| Dashboard | `GET /dashboard/stats` |
| Students | `/students` (index/show/store/update/destroy) |
| Teachers | `/teachers`, `/teachers/{id}/schedule` |
| Academic lookups | `/academic/classes`, `/academic/sections`, `/academic/subjects` |
| Exams | `/exams` + `/exams/{id}/results` |
| Exam Results | `/exam-results` + `/exam-results/{id}/publish` · `/unpublish` |
| Marks & Enrollment | `/mark-entries`, `/enrollments` |
| Attendance | `/attendance`, `/attendance/summary` |
| Teacher Assignments | `/teacher-assignments`, `/teacher-assignments/{id}` |
| Finance | `/funds`, `/donors`, `/donations`, `/expenses`, `/vendors`, `/fee-structures`, `/fee-payments`, `/inventory`, `/journal-entries`, `/cash-book`, `/finance/summary` |
| Notices | `/notices` |
| Tenants | `/tenants` (super_admin for create/delete) |

> Note: the `/finance/*` and `/academic/*` read routes and the dashboard stats endpoint
> were added in Weeks 6–8 to wire the frontend; the older `/exams/{id}/results` and
> `PUT/PATCH /exam-results/{id}` are served by `ExamResultController` (not `ExamController`).

---

## Frontend Pages (Bengali-first)

| Area | Routes |
|------|--------|
| Dashboard | `/dashboard` (live KPI cards, recent notices/students/exams, attendance summary) |
| Students | `/students`, `/students/[id]`, `/students/[id]/edit`, `/students/create` |
| Exams | `/exams`, `/exams/[id]`, `/exams/create`, `/exams/[id]/edit` (results + publish/unpublish) |
| Marks / Enrollment | `/marks/create`, `/enrollments/create` |
| Attendance | `/attendance`, `/attendance/create` |
| Teacher Assignments | `/teacher-assignments`, `/teacher-assignments/create` |
| Academic | `/academic` (classes / sections / subjects) |
| Fees | `/fees`, `/fees/collect` |
| Finance | `/finance`, `/finance/funds|donations|expenses` (+ `/create`, `/[id]`), `/finance/donors` |
| Settings | `/settings` (profile, language, logout) |
| Reports (API) | `GET /reports/attendance` (class × date-range matrix), `GET /reports/results` (per-exam marks), `GET /reports/attendance/export` + `GET /reports/results/export` (CSV) |
| Guardian (API) | `GET /guardian/portal` (linked students' attendance/results/fees) |
| Self-service (API) | `GET /student/me` (role=student), `GET /teacher/assignments` (role=teacher) |
| Notifications (API) | `GET /notifications` (guardian sees own / admin sees all), `POST /notifications/absence`, `POST /notifications/fee-due`, `POST /notifications/read-all`, `POST /notifications/{id}/read` |
| Self-service | `/student/me` (student: own attendance/results/fees), `/teacher/assignments` (teacher: own assignments) |
| Notices | `/notice`, `/notice/create`, `/notice/[id]`, `/notice/[id]/edit` |
| Auth | `/login`, `/register` |

The sidebar (`AppSidebar.vue`) links the primary modules and uses the shared `<Icon name="…">`
component for iconography.

---

## Testing

Backend feature tests (auth, B-controllers, finance, exam-results publish workflow):

```bash
cd laravel
php artisan test
# or: php vendor/bin/phpunit tests/Feature/
```

Current status: **52 passing / 246 assertions** (green).

---

## Project Status (Weeks 1–8)

| Week | Delivered |
|------|-----------|
| 1–5 | Scaffold: Laravel 11 API, 57 models, ~30 controllers, multi-tenant + Sanctum, Nuxt 4 shell, 52-test suite. |
| 6 | Seeder verification (`migrate:fresh --seed`), B-controller column-mismatch fixes, auth-flow polish, finance sub-pages (list/create/detail), mark-entry & enrollment forms, GitHub init + first push. |
| 7 | Exam Results publish workflow (exam detail page + publish/unpublish), student profile enrichment (enrollments + results), attendance entry form + attendance schema alignment, dashboard live stats (`/dashboard/stats`). |
| 8 | Teacher Assignment UI (list + create + sidebar nav) + academic lookup endpoints + `section()` relation fix. |

**Fully working end-to-end**: authentication, students (CRUD + profile), exams
(list/detail/create/edit + results publish), marks & enrollment entry, attendance
(entry + daily list/summary), teacher assignments, finance module (funds/donations/
expenses list/create/detail + summary), notices, and a live dashboard.

---

## Common Commands

```bash
# Backend
cd laravel && php artisan migrate:fresh --seed   # reset + demo data
cd laravel && php artisan serve                   # API on :8000
cd laravel && php artisan test                    # run suite

# Frontend
cd nuxt && npm run dev                            # dev server on :3000
cd nuxt && npm run build                          # production build (.output/)
```

---

## License

Proprietary — Rihal / সাবাক (Shere Ali). Internal use.
