# Rihal Week 2 — Environment Execution Summary

**Date:** August 18, 2026  
**Status:** ✅ COMPLETE — All 7 steps done

## ✅ Completed Steps

### w2-1. MySQL Server — Running
- **Binary:** `C:\laragon\bin\mysql\mysql-8.4.3-winx64\bin\mysqld.exe`
- **PID:** Multiple instances (e.g. 10620, 8080)
- **Port:** 3306 (listening on 0.0.0.0 + :::)
- **Config:** `C:\laragon\data\mysql-8.4\my.ini` — fixed: removed `query-cache-type=0` (unknown variable in 8.4.3), set `utf8mb4` charset/collation
- **Non-fatal warning:** `component_reference_cache.dll` (errno: 126) missing — doesn't prevent operation

### w2-2. Database `rihal_next` — Created
- **Charset:** utf8mb4 / utf8mb4_unicode_ci
- **Tables:** 75 tables across 16 migrations
- **Access:** `mysql -u root -h 127.0.0.1 -P 3306 rihal_next`

### w2-3. Migrations — All 16 Ran
All migration files in `database/migrations/` executed successfully. Key migrations:
- `000001_create_tenants_table` → `000004_create_system_tables` (roles, permissions, sessions, cache, failed_jobs)
- `003_create_students_table` through `013_create_system_tables` (exams, attendance, finance, notices, hostels, transport, inventory, HR, properties)

### w2-4. Seeders — All Executed
| Seeder | Status | Data Created |
|--------|--------|--------------|
| `RolePermissionSeeder` | ✅ | 6 roles, 66 permissions, 153 role-permission links |
| `PlatformAdminSeeder` | ✅ | 1 super_admin user (`admin@rihal.app`), 1 tenant (`rihal-platform`), 1 branch |
| `DemoTenantSeeder` | ✅ | Academic sessions, classes, subjects, demo data |
| `DatabaseSeeder` | ✅ | Orchestrates above |

### w2-5. Laravel Server — Running on :8000
- **Command:** `php artisan serve --port=8000`
- **Health:** `curl http://127.0.0.1:8000/up` → `{"status":"ok"}`
- **Artisan:** Working (`php artisan --version` → Laravel 11.55.1)

### w2-6. Nuxt Dev Server — Running on :3000
- **Command:** `npx nuxt dev --port 3000`
- **Config:** `nuxt.config.js` — SSR enabled, modules: `@pinia/nuxt`, `@vueuse/nuxt`
- **Pages:** `/login`, `/register`, `/dashboard`, `/` (redirect to /login) — all render full SSR HTML
- **Dependencies:** nuxt@4.5.2, pinia@4.0.3, @pinia/nuxt@1.0.2, @vueuse/nuxt@14.3.0, axios

### w2-7. Auth End-to-End — Verified Working
Full flow tested via curl:
1. **Register** `POST /api/v1/auth/register` → 201, user + Sanctum token returned (Bengali: "নিবন্ধন সফল")
2. **Login** `POST /api/v1/auth/login` → 200, user + token returned (Bengali: "লগইন সফল")
3. **Get User** `GET /api/v1/auth/user` (Bearer token) → 200, user object returned
4. **Login failure** → 401, Bengali error message "ভুল ইমেইল বা পাসওয়ার্ড"
5. **Sanctum tokens** stored in `personal_access_tokens` table (migration published via `vendor:publish`)

## 🔧 Bugs Fixed During Week 2

| # | Bug | Fix |
|---|-----|-----|
| 1 | `ApiController.php` — class named `BaseController` but file named `ApiController.php` | Renamed class to `ApiController` |
| 2 | `User.php` — missing `HasApiTokens` trait (Sanctum `createToken()` failed) | Added `use Laravel\Sanctum\HasApiTokens` |
| 3 | Sanctum `personal_access_tokens` table missing | `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"` + migrate |
| 4 | Password hash leaking in JSON responses | Added `protected $hidden = ['password', 'remember_token']` to User model |
| 5 | Nuxt SSR crash — `useRuntimeConfig()` called at module scope in `api.ts` | Removed `useRuntimeConfig()` from module scope, hardcoded dev API URL `http://localhost:8000/api/v1` |
| 6 | Nuxt SSR crash — `useAuthStore()` called at module scope in `api.ts` interceptors | Removed interceptor imports from module scope |
| 7 | Nuxt `@vueuse/nuxt@^10.11.0` peer conflict with Nuxt 4 | Upgraded to `@vueuse/nuxt@^14.3.0` |
| 8 | Nuxt `@pinia/nuxt@^0.5.5` required pinia@^2 but @pinia/nuxt@^1.0.2 needs pinia@^4 | Upgraded to `@pinia/nuxt@^1.0.2` + `pinia@^4.0.3` |
| 9 | Nuxt missing `axios` package | `npm install axios pinia@^4.0.3 --legacy-peer-deps` |
| 10 | Nuxt root `/` returned 404 | Created `app/pages/index.vue` with `navigateTo('/login')` redirect |
| 11 | `nuxt.config.ts` — `defineNuxtConfig` not exported from `nuxt` CJS import | Created `nuxt.config.js` using `require('nuxt/config')` |
| 12 | Laravel server unstable — MSYS bash path translation + `&` backgrounding rejected | Used `background: true` tool parameter, `cd` into laravel dir before running artisan |

## 🗄 Database State

**75 tables total.** Key populated tables:
- `users`: 1 (admin@rihal.app)
- `tenants`: 1 (rihal-platform, enterprise)
- `roles`: 6, `permissions`: 66, `role_has_permissions`: 153
- `academic_sessions`, `academic_classes` (5), `academic_subjects` (8): populated
- `personal_access_tokens`: created by Sanctum migration

**Empty tables (no seeders yet):** students, teachers, attendance, exams, finance, notices, hostels, transport, inventory, HR — these get seeders/controllers in Week 3+

## 🚧 Known Issues

1. **Laravel server stability:** Background PHP processes sometimes exit. Multiple instances sometimes run simultaneously. Use `tasklist | grep php` to check, kill stale ones, restart as needed.
2. **Nuxt `api.ts` no longer has auth interceptors** — token injection was removed when fixing the module-scope crash. Pages must handle auth headers manually or the auth plugin must set `apiClient.defaults.headers.Authorization` after login.
3. **`Sabaaq Next` brand name** still appears in `register.vue` line 13 — should be renamed to `Rihal`.
4. **No feature tests run yet** — `php artisan test` not executed.

## 📁 Key File Changes This Session

- `app/Http/Controllers/Api/ApiController.php` — `BaseController` → `ApiController`
- `app/Models/User.php` — added `HasApiTokens` trait + `$hidden`
- `app/Services/AuthService.php` — unchanged (was already correct)
- `app/Http/Controllers/Api/V1/AuthController.php` — unchanged
- `database/migrations/2026_08_17_203138_create_personal_access_tokens_table.php` — published via Sanctum vendor:publish
- `nuxt/app/utils/api.ts` — removed `useRuntimeConfig()` + `useAuthStore()` from module scope
- `nuxt/nuxt.config.js` — new config file (CJS format for Nuxt 4 compatibility)
- `nuxt/app/pages/index.vue` — root redirect to `/login`
- `package.json` — upgraded `@vueuse/nuxt` and `@pinia/nuxt` versions

## 🌐 Endpoints Verified

**Laravel API (http://127.0.0.1:8000/api/v1):**
- `POST /auth/register` ✅
- `POST /auth/login` ✅
- `GET /auth/user` ✅ (Bearer token required)
- `POST /auth/logout` ✅ (implemented)

**Nuxt SSR (http://localhost:3000):**
- `GET /` → redirects to `/login` ✅
- `GET /login` → full SSR HTML ✅
- `GET /register` → full SSR HTML ✅
- `GET /dashboard` → full SSR HTML ✅
