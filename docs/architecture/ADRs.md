This file documents the architecture decisions for Sabaaq Next.

## Decision Record Index

| ID | Date | Title | Status | Context |
|----|------|-------|--------|---------|
| 001 | 2026-08-16 | Multi-tenancy via PostgreSQL RLS | Accepted | ADR-001.md |
| 002 | 2026-08-16 | Monorepo with pnpm workspaces | Accepted | ADR-002.md |
| 003 | 2026-08-16 | React + Next.js for web | Accepted | ADR-003.md |
| 004 | 2026-08-16 | React Native (Expo) for mobile | Accepted | ADR-004.md |
| 005 | 2026-08-16 | Go for performance services | Accepted | ADR-005.md |
| 006 | 2026-08-16 | Node.js/TypeScript for BFF | Accepted | ADR-006.md |
| 007 | 2026-08-16 | GraphQL + REST dual API | Accepted | ADR-007.md |
| 008 | 2026-08-16 | Offline-first with queue-and-sync | Accepted | ADR-008.md |
| 009 | 2026-08-16 | i18n with ICU message format | Accepted | ADR-009.md |
| 010 | 2026-08-16 | Tailwind + shadcn/ui for UI | Accepted | ADR-010.md |

---

## ADR-001: Multi-tenancy via PostgreSQL Row-Level Security

**Status:** Accepted

**Context:** Sabaaq Next must serve multiple madrasas (tenants) from a single deployment, with strong data isolation.

**Decision:** Use PostgreSQL Row-Level Security (RLS) to enforce tenant isolation at the database level.

**Consequences:**
- Every table has a `tenant_id` column
- RLS policies ensure queries only return rows matching the current tenant
- Application never needs to manually filter by tenant_id (database enforces it)
- Simpler application code, fewer bugs, stronger security guarantee
- Single deployment serves all tenants → lower infrastructure cost
- If a tenant requires absolute isolation, we can provision a separate schema or database later

**Alternatives considered:**
- **Schema-per-tenant:** More isolation, but harder to maintain (migrations run per schema), more expensive
- **Application-level filtering:** Simpler to start, but bug-prone (forgetting to filter = data leak), can't guarantee isolation
- **Separate databases per tenant:** Maximum isolation, but operationally complex (hundreds of DBs), expensive

**Why RLS:** Best balance of isolation, simplicity, and cost for our target market (madrasas with 50-1000 students). We can escalate to schema-per-tenant for enterprise customers who demand it.

---

## ADR-002: Monorepo with pnpm Workspaces

**Status:** Accepted

**Context:** We have multiple packages (web, mobile, API, multiple services) that share types, utilities, and configurations.

**Decision:** Use pnpm workspaces in a single monorepo.

**Consequences:**
- Shared code (types, i18n strings, UI components) is easy to share across packages
- Single CI/CD pipeline
- Atomic commits across packages (e.g., change a type definition + update all consumers in one commit)
- pnpm's disk-efficient node_modules (hard links)
- Tooling: pnpm + changesets for versioning (if we publish packages) or Turborepo for build caching

**Alternatives considered:**
- **Separate repos:** Simpler per-repo but painful for shared code, versioning, and cross-package changes
- **Nx monorepo:** More features (computation caching, affected graphs) but heavier; pnpm + Turborepo is lighter and sufficient for now

---

## ADR-003: React + Next.js for Web Application

**Status:** Accepted

**Context:** We need a modern, responsive, performant web application for madrasa administrators, accountants, and teachers.

**Decision:** React 18 + Next.js 14 (App Router) + TypeScript.

**Consequences:**
- SSR/SSG for fast initial loads and SEO (for public pages like admission forms)
- Large ecosystem, large talent pool
- App Router gives us server components for data fetching, client components for interactivity
- Tailwind CSS + shadcn/ui for consistent, modern UI
- PWA capability via next-pwa or custom service worker for offline support

**Why not alternatives:**
- **Vue/Nuxt:** Good, but React has larger talent pool in Bangladesh and more component libraries
- **Angular:** Too heavy for this use case, smaller talent pool
- **Svelte/SvelteKit:** Promising but smaller ecosystem; would make hiring harder

---

## ADR-004: React Native (Expo) for Mobile

**Status:** Accepted

**Context:** We need iOS and Android apps for admins, teachers, and parents.

**Decision:** React Native via Expo SDK.

**Consequences:**
- One TypeScript codebase → iOS + Android
- Expo provides easy access to native features (camera, biometrics, notifications, offline storage)
- Fast iteration (Expo Go for development, EAS Build for production)
- Can publish to App Store + Play Store
- Shared business logic with web (types, validation, API client) via shared packages

**Alternatives considered:**
- **Flutter:** Good performance, single codebase, but Dart is less familiar to our web team; React Native lets us share TypeScript expertise
- **Native Swift/Kotlin:** Best performance but double the work, double the team; only justified for premium features later
- **Capacitor/Cordova:** Web-tech wrapper; not suitable for a quality mobile app experience

---

## ADR-005: Go for Performance-Critical Services

**Status:** Accepted

**Context:** Some services will handle high-throughput, low-latency requests (e.g., attendance ingestion during morning rush when hundreds of students check in simultaneously).

**Decision:** Use Go for performance-critical microservices.

**Consequences:**
- Excellent concurrency (goroutines) for handling many simultaneous connections
- Low memory footprint, fast startup
- Strong typing, easy to deploy (single binary)
- Good PostgreSQL driver (pgx), Redis client, gRPC support
- Services: attendance, possibly finance (high-volume transactions)

**Trade-offs:**
- Smaller talent pool in Bangladesh than Node.js/Python
- Less rapid prototyping than Python/Node.js
- Mitigation: Start with Node.js for all services; rewrite performance-critical ones in Go when we hit scale issues. Premature optimization is the root of all evil.

---

## ADR-006: Node.js/TypeScript for BFF (Backend for Frontend)

**Status:** Accepted

**Context:** We need an API layer that aggregates data from multiple services, handles authentication, rate limiting, and provides a developer-friendly API for web and mobile clients.

**Decision:** Node.js + TypeScript + GraphQL (Apollo) + REST (Express/Fastify).

**Consequences:**
- TypeScript shared with frontend → type safety across the stack
- GraphQL for flexible client queries (web dashboard can request exactly what it needs)
- REST for simple CRUD and mobile (easier caching, simpler clients)
- Large ecosystem (middleware, auth libraries, logging)
- Easy to hire/contract Node.js developers in Bangladesh

---

## ADR-007: GraphQL + REST Dual API

**Status:** Accepted

**Context:** Different clients have different API needs. Web dashboards benefit from GraphQL's flexibility; mobile apps and third-party integrations prefer REST's simplicity.

**Decision:** Provide both GraphQL (primary for web) and REST (primary for mobile, public API, webhooks).

**Consequences:**
- GraphQL endpoint: `/graphql` — typed schema, introspection, subscriptions for real-time
- REST endpoint: `/api/v1/*` — versioned, documented with OpenAPI, JSON
- Both backed by the same service layer (no duplicate business logic)
- BFF layer resolves GraphQL queries by calling service APIs
- Public REST API is the integration point for third parties (payment gateways, SMS providers, plugins)

**Trade-off:** Maintaining two API surfaces adds some complexity. We mitigate by having a single source of truth for business logic, with GraphQL resolvers and REST handlers calling the same service functions.

---

## ADR-008: Offline-First with Queue-and-Sync

**Status:** Accepted

**Context:** Madrasas in rural Bangladesh often have unreliable internet. The mobile app and PWA must work offline.

**Decision:** Offline-first architecture with action queue and background sync.

**Consequences:**
- Mobile app + PWA cache data locally (SQLite/Realm on mobile, IndexedDB on web)
- All write actions are queued when offline; synced when online
- Queue is persistent (survives app restart)
- Conflict resolution: last-write-wins for simple cases; manual merge UI for conflicting edits
- Sync status indicator in UI
- Background sync when connectivity returns (even if app is backgrounded)

**Trade-offs:**
- Significant complexity in sync logic, conflict resolution, queue management
- Testing offline scenarios is harder
- Mitigation: Start with online-first + offline queue (simpler). Full offline-first (read cached data without network) comes in V2 when we have real offline usage data.

---

## ADR-009: i18n with ICU Message Format

**Status:** Accepted

**Context:** Sabaaq Next must support Bengali (primary), English, Arabic, and eventually Urdu, Hindi, and more.

**Decision:** Use ICU message format for all user-facing strings, with JSON translation files per language.

**Consequences:**
- ICU handles plurals, gender, number formatting correctly across languages
- JSON translation files are easy to edit, version, and crowdsource
- Per-user language preference (stored in user profile)
- Auto-detect browser language on first visit (with override)
- RTL support for Arabic (CSS logical properties, dir="rtl" on root)
- Translation management: we can use Crowdin or similar for community translations later

**Implementation:**
```
packages/shared/i18n/
├── en.json
├── bn.json
├── ar.json
├── types.ts (typing for translation keys)
└── useTranslation() hook
```

---

## ADR-010: Tailwind CSS + shadcn/ui for UI

**Status:** Accepted

**Context:** We need a modern, consistent, customizable UI that works for madrasa administrators who may be using it on desktop, tablet, or phone.

**Decision:** Tailwind CSS + shadcn/ui (copy-paste components, not a dependency).

**Consequences:**
- Tailwind: utility-first CSS, small bundle, highly customizable, responsive by default
- shadcn/ui: accessible, well-designed components (based on Radix UI primitives), fully customizable since we own the code
- Dark mode support built-in
- Consistent design system across web and mobile (shared design tokens)
- Easy to implement madrasa-specific branding (colors, logo) per tenant

**Alternatives considered:**
- **MUI/Chakra:** Good component libraries but harder to customize deeply, larger bundle
- **Bootstrap:** Too old-looking, harder to make feel modern and premium
- **Custom CSS from scratch:** Too much work, harder to maintain consistency
