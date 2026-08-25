# Rihal — Production Operations & Hardening

This document covers the operational steps required before and after deploying
Rihal (Laravel 11 API + Nuxt 4 frontend) to a production environment.

## 1. Environment configuration

Copy `.env.example` to `.env` and set production values:

- `APP_ENV=production`, `APP_DEBUG=false`
- `DB_*` — production MySQL 8.4 credentials
- `MAIL_*` — a real SMTP relay (e.g. Mailtrap, SES, Postmark)
- `SMS_GATEWAY_URL` / `SMS_GATEWAY_TOKEN` — Bangladesh SMS gateway.
  If left empty, SMS deliveries are safely marked `skipped` (no crash).
- `QUEUE_CONNECTION=redis` (recommended) with a running `queue:work` worker
- `SANCTUM_STATEFUL_DOMAINS` — leave empty (stateless Bearer API)

Generate the key: `php artisan key:generate`.

## 2. Database & audit integrity

- Run `php artisan migrate --force`.
- The `audit_logs` table is **append-only** at both the application layer
  (`AuditLog` model events) and the database layer (MySQL `BEFORE UPDATE`/
  `BEFORE DELETE` triggers created by migration
  `2026_08_25_000003_make_audit_logs_append_only`). Only a MySQL superuser
  with `SUPER`/`TRIGGER` privilege and explicit `SET sql_log_bin` bypass can
  alter it — restrict such accounts in production.

## 3. Scheduler (overdue loan notifications)

The `financial:send-overdue-notifications` command is scheduled daily at 08:00
(see `routes/console.php`). Add to crontab on the Laravel host:

```
* * * * * cd /path/to/laravel && php artisan schedule:run >> storage/logs/scheduler.log 2>&1
```

## 4. Queue worker (recommended for notification throughput/retries)

```
php artisan queue:work --queue=notifications --tries=3 --max-time=3600
```

Failed `notification_deliveries` rows are retried automatically (status cycles
`pending → sending → failed → pending` with an `attempts` counter and a stale
`last_attempted_at` recovery window of 15 minutes).

## 5. File storage

Uploaded orphan photos live under `storage/app/public/tenants/{id}/photos`.
Run `php artisan storage:link` so the `public/storage` symlink is created and
served by the web server.

## 6. Backups

`scripts/backup.bat` performs a `mysqldump` (single-transaction, with routines
and triggers) plus a ZIP of uploaded files into `storage/backups/`. Schedule it
via Task Scheduler / cron. Test restore on a staging instance periodically.

## 7. Web server

- Laravel served via `php artisan serve` only for local dev. In production use
  PHP-FPM behind Nginx with `public/` as the web root and `index.php` router.
- Nuxt 4 built with `npx nuxt build`; serve `node .output/server/index.mjs`
  behind Nginx (reverse proxy) or a process manager (PM2/systemd).
- Force HTTPS and set secure session/cookie flags.
- Rate limiting is applied via `throttle:api` (global) and a `financial`
  limiter (30/min) — backed by the cache driver, so use Redis in production.

## 8. Verification checklist

- [ ] `php artisan migrate:status` shows all migrations run
- [ ] `php artisan schedule:list` shows the overdue command
- [ ] `php vendor/bin/phpunit` passes (target: 0 failures, 0 skips)
- [ ] `npx nuxt build` completes with "Build complete"
- [ ] Login at `/login` works; loan/orphan/audit pages render in browser
- [ ] Mobile viewport shows no horizontal overflow; sidebar toggles via hamburger
- [ ] CSV exports download from loan/orphan list pages
