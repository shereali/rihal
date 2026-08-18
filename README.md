# Rihal — Madrasha Management Platform

**200x Better** — A complete rethinking of Islamic school management for Bangladesh.

---

## What Is Rihal?

Rihal is a multi-tenant, REST-first, Laravel 11 + Nuxt 4 web application for managing madrasas (Islamic schools) in Bangladesh. It replaces the legacy demo.sabaaq.com system with a modern, extensible, AI-ready platform.

**Target users:** Madrasa administrators, teachers, staff, students, guardians, donors, and platform administrators.

**Target market:** Bengali-speaking madrasas in Bangladesh.

---

## Core Features (23 Modules)

### People & Organization
- Multi-tenant architecture (each madrasa is a tenant)
- User management (students, teachers, staff, admins, guardians)
- Tenant management (CRUD, subscription tiers, modules)
- Donor/sponsor management

### Academic
- Academic sessions (year/term management)
- Classes and sections
- Subjects (Islamic + regular)
- Timetables
- Enrollments (student → class → section → session)

### Exams & Results
- Exam management (create, schedule, seat plans)
- Mark entry (per exam, per subject, per student)
- Results/report cards (GPA, percentage, grade, position)
- AI-generated report card comments (Phase 2)
- Parent notification

### Attendance
- Daily attendance marking (manual, fingerprint, facial, RFID, QR, WiFi)
- Attendance devices integration
- Attendance patterns (aggregated, risk scores)
- Parent notification on absence

### Finance
- Fee structures (per class, per session, flexible heads)
- Fee payment tracking (cash, bKash, Nagad, card, bank)
- Funds (Zakah, Sadaqah, General — separate accounting)
- Donations (with retention scoring)
- Expenses (with approval workflow)
- Budgets (fiscal year, variance analysis)
- Chart of accounts (double-entry ready)
- Journal entries (double-entry, balanced)
- Cash book
- Vendor management

### Notices & Communication
- Notices (multi-channel: in-app, SMS, email, WhatsApp)
- Notice read receipts
- Communication templates
- Emergency alerts with escalation cascade

### Hostel
- Hostel rooms (capacity, occupancy, warden, monthly fee)
- Hostel visitors (check-in/check-out)

### Transport
- Transport buses (capacity, route, driver, GPS)
- Transport routes (stops, timing)
- Student transport assignments

### HR
- Teacher management (qualifications, certifications, contracts)
- Staff management
- Recruitment (job postings, applications)
- Emergency contacts

### Inventory
- Stock management (reorder levels, valuation)
- Stock transactions
- Vendor management

### Property
- Property management (land, building — valuation, documents, geo-location)

### AI (Future)
- সবক AI — Bengali natural language queries across all modules
- Graph-based analytics (cross-module queries)
- Predictive analytics (attendance risk, academic risk, donor churn)

---

## Tech Stack

| Layer | Technology |
|-------|-----------|
| Backend | Laravel 11 (PHP 8.3) |
| Frontend | Nuxt 4 (Vue 3 + TypeScript) |
| Database | MySQL |
| API | REST (JSON envelope), Sanctum token auth |
| Auth | Laravel Sanctum (API tokens, `rihal_` prefix) |
| i18n | Bengali-first (bn locale, Asia/Dhaka timezone) |
| CSS | Custom design tokens (Rihal green #145032, gold #d4af37) |
| State | Pinia (auth store) |
| Testing | PHPUnit (Laravel) |
| Hosting | Compatible with cheap shared PHP hosting in Bangladesh |

---

## Project Structure

```
SabaaqNext/
├── laravel/                      # Laravel 11 backend
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/   (Auth, Tenant, User)
│   │   │   ├── Middleware/           (Auth, Tenant, Role checks)
│   │   │   └── Requests/Api/V1/      (Validation with Bengali msgs)
│   │   ├── Models/                   (57 models covering 23 modules)
│   │   ├── Services/                 (Auth, Tenant)
│   │   └── Providers/
│   ├── config/                       (16 config files)
│   ├── database/
│   │   ├── migrations/               (13 migrations, full schema)
│   │   └── seeders/                  (PlatformAdmin, DemoTenant, Roles)
│   ├── routes/
│   │   ├── api.php                    (REST v1 endpoints)
│   │   ├── web.php                    (healthcheck)
│   │   └── console.php
│   ├── tests/Feature/                (Auth, Tenant, User tests)
│   └── composer.json
├── nuxt/                          # Nuxt 4 frontend
│   ├── app/
│   │   ├── components/layout/    (AppSidebar, AppTopBar)
│   │   ├── composables/          (useAuth, useApi)
│   │   ├── layouts/              (default, auth)
│   │   ├── pages/                (login, dashboard, register)
│   │   ├── plugins/              (api)
│   │   ├── stores/               (auth - Pinia)
│   │   ├── utils/                (api)
│   │   ├── types/                (TypeScript types)
│   │   ├── assets/css/           (design tokens)
│   │   └── css/main.css
│   └── nuxt.config.ts
├── README.md
├── WEEKS.md
├── docker-compose.yml
├── docs/
│   ├── architecture/ADRs.md
│   └── scenarios/
│       ├── scenario-attendance.md
│       └── scenario-fee-payment.md
└── .gitignore
```

---

## API Quick Start

```bash
# Start Laravel server
cd laravel
php artisan serve --port 8000

# Register a new user
curl -X POST http://localhost:8000/api/v1/auth/register \
  -H "Content-Type: application/json" \
  -d '{
    "name_bn": "Test User",
    "email": "test@example.com",
    "password": "password123",
    "password_confirmation": "password123",
    "phone": "+880****0000"
  }'

# Login
curl -X POST http://localhost:8000/api/v1/auth/login \
  -H "Content-Type: application/json" \
  -d '{"email": "test@example.com", "password": "password123"}'

# Access protected endpoint
curl -H "Authorization: Bearer ***" \
  http://localhost:8000/api/v1/auth/user
```

---

## Platform Capabilities

- **Multi-tenancy:** Each madrasa is a separate tenant with its own data, users, settings, and subscription
- **REST API:** Clean JSON API — register, login, CRUD tenants, CRUD users
- **Role-based access:** 6 roles with 55 permissions
- **Bengali-first:** All UI strings in Bengali, timezone Asia/Dhaka, validation messages in Bengali
- **Extensible:** Plugin system, module system, configurable subscription tiers
- **AI-ready:** The 57 models and graph-ready schema set up for সবক AI integration
- **Soft deletes:** Every model uses SoftDeletes for data retention and audit trails

---

## Environment

- **PHP:** 8.3+ (Laragon includes this)
- **Composer:** Latest
- **MySQL:** 8.0+ (via Laragon or standalone)
- **Node.js:** 20+ (for Nuxt)
- **npm/pnpm:** For frontend dependencies

---

## Rename Note

The project was renamed from **Sabaaq** → **Rihal** across all source files. The demo site being cloned/enhanced remains https://demo.sabaaq.com (reference only).

---

*For Week 1 status, see WEEKS.md.*
