# Sabaaq Next
## Laravel 13 API + Nuxt 4 Frontend
## Multi-tenant Madrasha Management Platform

### Quick Start

```bash
# Start everything (needs Docker)
docker-compose up -d

# Or develop locally (needs PHP 8.2+ and Node 20+)

# Backend
cd laravel
composer install
cp .env.example .env
php artisan key:generate
php artisan migrate
php artisan serve --port 8000

# Frontend
cd nuxt
npm install
npm run dev
```

### API

Base URL: `http://localhost:8000/api/v1`
Auth: Bearer token (Laravel Sanctum)

### Tech Stack

- **Backend:** Laravel 13 (PHP 8.3+)
- **Frontend:** Nuxt 4 (Vue 3 + Nitro)
- **Database:** MySQL 8.0
- **Cache/Queue:** Redis 7
- **Auth:** Laravel Sanctum (API tokens)
- **Container:** Docker + Docker Compose

### Project Structure

```
SabaaqNext/
├── laravel/           # Laravel 13 API
│   ├── app/
│   │   ├── Http/
│   │   │   ├── Controllers/Api/V1/
│   │   │   ├── Middleware/
│   │   │   └── Requests/Api/V1/
│   │   ├── Models/
│   │   ├── Services/
│   │   └── Actions/
│   ├── database/
│   │   └── migrations/
│   ├── routes/
│   │   └── api.php
│   └── tests/
│       └── Feature/
├── nuxt/              # Nuxt 4 Frontend
│   ├── app/
│   │   ├── pages/
│   │   ├── components/
│   │   ├── composables/
│   │   ├── utils/
│   │   └── plugins/
│   ├── public/
│   └── nuxt.config.ts
├── docker-compose.yml
└── README.md
```

### Week 1 Overview

- [x] Project scaffold (Laravel + Nuxt)
- [x] Docker Compose (MySQL + Redis + Laravel + Nuxt)
- [x] Database migrations (tenants, users, roles, permissions)
- [ ] Eloquent models (Tenant, User, Role, Permission)
- [ ] Auth controller (register, login, logout, me)
- [ ] Tenant controller (CRUD)
- [ ] User controller (CRUD within tenant)
- [ ] Auth middleware (token validation)
- [ ] Tenant scoping middleware
- [ ] REST API routes
- [ ] Nuxt pages (login, dashboard, layout)
- [ ] Nuxt auth composable
- [ ] Nuxt API client

### Review Checkpoint (End of Week 1)

1. `docker-compose up -d` starts all services
2. Laravel API responds on `http://localhost:8000`
3. Nuxt app responds on `http://localhost:3000`
4. You can register a platform admin, create a tenant, create a user
5. Login returns a token, subsequent requests use the token
6. Tenant scoping works — users can't cross-tenant
