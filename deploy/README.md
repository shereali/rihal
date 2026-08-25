# Rihal — Deployment Guide

How to run the full Rihal stack (Laravel 11 API + Nuxt 4 frontend) in a
production-ready configuration. The repository already contains a compiled
Nuxt build (`.output/`) and the append-only audit DB triggers.

This directory (`deploy/`) holds reference configs for both **Linux** (Nginx +
PM2/systemd) and **Windows** (Task Scheduler + NSSM) hosts.

---

## 1. Environment

Copy `laravel/.env.example` → `laravel/.env` and set production values:

```
APP_ENV=production
APP_DEBUG=false
APP_URL=https://yourdomain.com

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=rihal_next
DB_USERNAME=rihal_app
DB_PASSWORD=<strong-random>

MAIL_MAILER=smtp
MAIL_HOST=smtp.example.com
MAIL_PORT=587
MAIL_USERNAME=postmaster@example.com
MAIL_PASSWORD=<redacted>
MAIL_ENCRYPTION=tls
MAIL_FROM_ADDRESS=noreply@example.com
MAIL_FROM_NAME="Rihal"

# SMS gateway — empty = SMS deliveries safely marked "skipped"
SMS_GATEWAY_URL=
SMS_GATEWAY_TOKEN=

QUEUE_CONNECTION=redis        # recommended; falls back to "sync"
CACHE_DRIVER=redis
SESSION_DRIVER=redis

# Nuxt talks to the API through this base (set at build/runtime)
NUXT_PUBLIC_API_BASE=https://yourdomain.com/api/v1
```

Generate the app key: `php artisan key:generate`.

Run migrations + cache:

```bash
php artisan migrate --force
php artisan config:cache
php artisan route:cache
php artisan storage:link
```

---

## 2. Laravel (API)

### Linux — PHP-FPM + Nginx

- Install PHP 8.3 FPM, point the FPM pool at `laravel/` with `APP_ENV=production`.
- Copy `deploy/nginx-rihal.conf` into `/etc/nginx/sites-available/` (symlink to
  `sites-enabled/`), adjust `server_name`, TLS paths, and root, then
  `nginx -t && systemctl reload nginx`.
- `php artisan serve` is **NOT** for production — use FPM.

### Windows — current dev/host stage

- The API currently runs via `php artisan serve --host=127.0.0.1 --port=8000`.
  For a more robust local service, wrap it with NSSM or run PHP-FPM for Windows.
- Scheduler: import `deploy/rihal-scheduler-task.xml` via Task Scheduler
  (`schtasks /Create /TN "Rihal Scheduler" /XML deploy\rihal-scheduler-task.xml`)
  or run `deploy/run-scheduler.bat` every minute.
- Queue worker: `deploy/run-queue.bat` (wrap with NSSM for a service).

---

## 3. Nuxt 4 (frontend)

Build is already produced via `npx nuxt build` (output in `.output/`). To run:

```bash
cd nuxt
NUXT_PUBLIC_API_BASE=https://yourdomain.com/api/v1 node .output/server/index.mjs
```

- **Linux:** use `deploy/ecosystem.config.cjs` with PM2:
  `pm2 start deploy/ecosystem.config.cjs && pm2 save`
- **Windows:** run `deploy/run-nuxt.bat` (wrap with NSSM for a service).
- Front the Node server with Nginx (see `deploy/nginx-rihal.conf` → `location /`).

---

## 4. Scheduler (overdue loan notifications)

The `financial:send-overdue-notifications` command is scheduled daily at 08:00
(`routes/console.php`). Ensure the scheduler runs every minute:

```bash
# Linux cron
* * * * * cd /var/www/rihal/laravel && php artisan schedule:run >> storage/logs/scheduler.log 2>&1

# Windows — Task Scheduler task (every minute)
schtasks /Create /TN "Rihal Scheduler" /XML deploy\rihal-scheduler-task.xml
```

Verify: `php artisan schedule:list`.

---

## 5. Queue worker (notification retries/throughput)

```bash
# Linux — systemd
cp deploy/rihal-queue.service /etc/systemd/system/
systemctl enable --now rihal-queue

# or PM2 (see ecosystem.config.cjs)
pm2 start deploy/ecosystem.config.cjs

# Windows
deploy\run-queue.bat   (wrap with NSSM: nssm install rihal-queue deploy\run-queue.bat)
```

Failed `notification_deliveries` rows auto-retry (status cycles
`pending → sending → failed → pending`, 15-minute stale recovery window).

---

## 6. Backups & restore drill

`laravel/scripts/backup.bat` creates a `mysqldump` (+ routines/triggers) and a
ZIP of uploaded files into `laravel/storage/backups/`.

**Restore drill (run periodically on staging):**

```bash
# 1. Restore DB
mysql -h <host> -u <user> -p rihal_next < storage/backups/rihal-db-<ts>.sql

# 2. Verify the audit trigger survived the restore
mysql -u <user> -p rihal_next -e "SHOW TRIGGERS LIKE 'audit_logs';"

# 3. Restore uploaded files
powershell -command "Expand-Archive -Path storage/backups/rihal-files-<ts>.zip -DestinationPath storage/app/public -Force"
```

The audit append-only trigger is part of the dump (routines+triggers) and must
be present after restore — the drill confirms it.

---

## 7. Security checklist

- [ ] `APP_DEBUG=false`, `APP_ENV=production`
- [ ] HTTPS enforced (Nginx 301 → 443)
- [ ] `.env` outside web root, `storage/` & `vendor/` not web-exposed
- [ ] `php artisan config:cache` + `route:cache` run
- [ ] DB user is least-privilege (no `SUPER`/`TRIGGER` for the app connection)
- [ ] Scheduler + queue worker supervised and logging
- [ ] Backup restore drill verified on staging
