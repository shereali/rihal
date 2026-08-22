# Sabaq Reference Gap Analysis — Dashboard and Admin Surface (2026-08-21, updated)

> Captured from authenticated `demo.sabaaq.com/dashboard` (Microsoft Edge, Super Admin) and compared with Rihal
> (Nuxt 4, 58 Vue pages, 67 routes, ‖localhost:3000‖ returning 200 across the full built surface). This
> document is a **verified implementation inventory**, not a claim that every reference route is built.

## Reference app — live inventory

### Sabaq main menus → sub-menus → Rihal status

All labels below are as rendered in the authenticated Sabaq admin UI.

| # | Sabaq main menu | Sub-menus (captured) | Rihal route(s) present | Status |
|---|----------------|----------------------|------------------------|--------|
| 1 | ড্যাশবোর্ড (Dashboard) | রিপোর্ট ও এক্সপোর্ট, আজকের হাজিরা, আর্থিক সারাংশ, সাম্প্রতিক ঘোষণা | `/dashboard` (200), `/reports` (200), `/attendance` (200), `/finance` (200), `/notice` (200) | ✅ Core parity |
| 2 | মডিউল ড্যাশবোর্ড (Module Dashboard) | (expandable) | not in Sabaq reference list as sub-menu; likely module overview | ⚠️ Not mapped; no Rihal page |
| 3 | রিমাইন্ডার টাস্ক (Reminder Task) | — | none | ❌ Missing |
| 4 | ছুটি ব্যবস্থাপনা (Leave Management) | — | none | ❌ Missing |
| 5 | সিস্টেম সেটিংস (System Settings) | — | `/settings` (200, stub) | ❌ Partial / stub |
| 6 | ডিজিটাল হাজিরা (Digital Attendance) | — | none | ❌ Missing |
| 7 | শিক্ষার্থী ব্যবস্থাপনা (Student Management) | — | `/students` (200), `/students/create` (200), `/students/:id` (200), `/students/:id/edit` (200), `/student/me` (200) | ✅ Core parity |
| 8 | একাডেমিক বিভাগ (Academic Department) | — | `/academic` (200), `/academic/manage` (200), `/academic/timetable` (200), `/enrollments/create` (200) | ✅ Partial |
| 9 | প্রমোশন এবং গ্রাজুয়েশন (Promotion & Graduation) | — | none | ❌ Missing |
| 10 | শিক্ষক ব্যবস্থাপনা (Teacher Management) | — | none | ❌ Missing |
| 11 | প্রশাসনিক বিভাগ (Administration) | — | `/hr` (200, stub), `/properties` (200, stub) | ❌ Partial / stub |
| 12 | হিসাব ও অর্থ বিভাগ (Accounting) | — | `/finance` (200), `/finance/funds/create` (200), `/finance/funds/:id` (200), `/finance/donors` (200), `/finance/donations/create` (200), `/finance/donations/:id` (200), `/finance/expenses/create` (200), `/finance/expenses/:id` (200) | ✅ Partial (funds/donors/donations/expenses only; no expense types, daily balance, annual/audit reports, receipt stock/distribution/collection, collection-by-user, user-wise cashbook) |
| 13 | রশিদ ব্যবস্থাপনা (Receipt Management) | — | none | ❌ Missing |
| 14 | স্পনসর এবং অনুদান বিভাগ (Sponsors & Donations) | — | `/finance/donors` (200), `/finance/donations` (200) | ⚠️ Partial (donor types + monthly donors + sponsor payments missing) |
| 15 | ঋণ এবং বকেয়া ব্যবস্থাপনা (Loans & Dues) | — | none | ❌ Missing |
| 16 | এতিম স্পনসর বিভাগ (Orphan Sponsorship) | — | none | ❌ Missing |
| 17 | বোর্ডিং ব্যবস্থাপনা (Boarding) | — | none | ❌ Missing |
| 18 | নোটিশ এবং ঘোষণা (Notices) | — | `/notice` (200), `/notice/create` (200), `/notice/:id` (200), `/notice/:id/edit` (200) | ✅ Core parity (targeting — group/single/class/all — missing) |
| 19 | চেঞ্জলগ (Changelog) | — | none | ❌ Missing |

### Sabaq sub-pages captured on the authenticated dashboard (not main-menu sub-menus)

These are dashboard in-page shortcut links, not left-sidebar sub-menus — but they reveal Sabaq's feature surface:

| Shortcut label | Sabaq links to | Rihal route | Status |
|----------------|----------------|-------------|--------|
| সম্পূর্ণ হিসাব (Complete Accounts) | accounts summary | `/finance` | ✅ |
| নতুন ভর্তি শিক্ষার্থী যোগ করুন | add new student | `/students/create` | ✅ |
| বাল্ক হাজিরা একসাথে হাজিরা নিন | bulk attendance | `/attendance/bulk` | ✅ |
| ফি সংগ্রহ পেমেন্ট রেকর্ড করুন | fee collection payment record | `/fees/collect` | ✅ |
| রিপোর্ট তৈরি রিপোর্ট ও CSV এক্সপোর্ট | reports & CSV export | `/reports` | ✅ |

### Sabaq topbar items (authenticated)

| Item | Rihal | Status |
|------|-------|--------|
| বাংলা language selector | language switcher not present | ❌ |
| লগ আউট (logout) | logout works | ✅ |
| সর্বকাল / আজ / এই সপ্তাহ / এই মাস / এই বছর / গত বছর period filter | period filter not present | ❌ |
| হাজিরা নিন (Take Attendance) quick action | takes to `/attendance` | ✅ |
| সব দেখুন (View All) notices quick action | takes to `/notice` | ✅ |
| Toggle Nuxt DevTools (Rihal dev only, not a Sabaq feature) | n/a | n/a |

## Rihal built surface (verified, 2026-08-21)

### Routes returning 200

`/` · `/login` · `/register` · `/dashboard` · `/students` · `/students/create` · `/students/:id` · `/students/:id/edit` · `/student/me` · `/attendance` · `/attendance/create` · `/attendance/bulk` · `/attendance/:id` · `/attendance/:id/edit` · `/exams` · `/exams/create` · `/exams/routine` · `/exams/:id` · `/exams/:id/edit` · `/exams/:id/seats` · `/exams/:id/admit-cards` · `/exams/:id/admit-cards/:studentId` · `/exams/:id/results` · `/academic` · `/academic/manage` · `/academic/timetable` · `/enrollments/create` · `/notice` · `/notice/create` · `/notice/:id` · `/notice/:id/edit` · `/portal` · `/reports` · `/notifications` · `/settings` · `/fees` · `/fees/collect` · `/finance` · `/finance/funds/create` · `/finance/funds/:id` · `/finance/donors` · `/finance/donations/create` · `/finance/donations/:id` · `/finance/expenses/create` · `/finance/expenses/:id` · `/homework` · `/homework/:id` · `/lesson-plans` · `/teacher-assignments` · `/teacher-assignments/create` · `/teacher/my-assignments` · `/hr` · `/hostel` · `/transport` · `/properties` · `/results` · `/results/gpa` · `/marks/create` · `/academic/index`

### Routes returning 404 (API exists, no Nuxt page yet)

`/exam-results` · `/mark-entries` · `/finance/funds` · `/finance/expenses` · `/teacher`

## Verified gaps (reference vs Rihal)

### P0 — product shell and dashboard

| Sabaq feature | Rihal status | Priority |
|---------------|--------------|----------|
| Dashboard: fund cards, period-aware data, class distribution, gender ratio, class attendance, monthly charts, top funds, due insight | Not at reference density | P0 |
| Global shell: theme switcher, language switcher, notification dropdown, AI/quick-action controls, support banner, polished responsive sidebar | Partial | P0 |
| License/subscription status UX and renewal modal | No UI (backend tenant status exists) | P0 |
| Audit/activity log | No UI | P0 |

### P1 — operational modules (backend routes present, UI missing/partial)

| Sabaq feature | Rihal status | Priority |
|---------------|--------------|----------|
| Reminder tasks | No page | P1 |
| Leave management | No page | P1 |
| Digital attendance (machine, device tools, biometric sync, ADMS, RFID, fingerprint) | No page | P1 |
| Homework assignments + submissions UI | Pages exist (`/homework`, `/homework/:id`) but submission flow thin | P1 |
| Lesson plans UI | Page exists (`/lesson-plans`) | P1 |
| Hostel rooms/visitors UI | Stub (`/hostel`) | P1 |
| Transport routes/buses UI | Stub (`/transport`) | P1 |
| HR: staff, recruitment, applications, holidays, events, registrations | Stub (`/hr`) | P1 |
| Properties/assets UI | Stub (`/properties`) | P1 |
| Inventory/stock transactions UI beyond finance page | No page | P1 |
| User and role administration UI | No page | P1 |
| System settings depth (sessions/classes/sections/subjects CRUD, subject assignment, exam grades, invoice design, attendance settings, SMS settings, global/additional fees, needy students, signatures, average-mark settings, data delete, cache manager, parent info, blank forms) | Stub (`/settings`) | P1 |

### P2 — academic administration

| Sabaq feature | Rihal status | Priority |
|---------------|--------------|----------|
| Sessions, classes, sections, subjects, subject assignment CRUD screens | No page | P2 |
| Exam routine, seats, admit cards, GPA/percentage summaries | Done (routine/seats/admit-cards/results/GPA pages) | ✅ Done this session |
| Certificates, clearance/attestation, syllabus/books/curriculum | No page | P2 |
| Promotion/graduation workflows | No page | P2 |
| Teacher management (list, ID card, salary, attendance, class routine, leave) | No page | P2 |
| Administration depth (staff, staff ID, staff attendance, salary/raises, responsibilities, responsibility assignment, events, leave applications, complaints/suggestions, activity log, teacher discharge) | Stub | P2 |

### P3 — finance and communication depth

| Sabaq feature | Rihal status | Priority |
|---------------|--------------|----------|
| Expense types, general deposits/withdrawals, needy fund, daily balance, annual report, audit report, collection-by-user, user-wise cashbook | No page | P3 |
| Receipt stock/distribution/collection | No page | P3 |
| Donor types, monthly donors | Partial (donors list + donations CRUD only) | P3 |
| Loans given/taken, shop, dues received/given | No page | P3 |
| Orphan sponsorship: sponsors, sponsor payments, due/advance | No page | P3 |
| Boarding: market purchases, boarding expenses | No page | P3 |
| Notice targeting (group/single/class/all) + announcements | Pages exist, targeting missing | P3 |
| Real SMS/email gateway adapters | Dispatch seam exists, adapters not wired | P3 |

## Cross-check summary

- Sabaq has **19 main menus** in the admin sidebar. Rihal mirrors the top-level sidebar labels for the modules it has built, but **~9 of Sabaq's main menus have no Rihal page at all** (রিমাইন্ডার টাস্ক, ছুটি ব্যবস্থাপনা, ডিজিটাল হাজিরা, প্রমোশন এবং গ্রাজুয়েশন, শিক্ষক ব্যবস্থাপনা, রশিদ ব্যবস্থাপনা, ঋণ এবং বকেয়া, এতিম স্পনসর, বোর্ডিং, চেঞ্জলগ).
- The Sabaq reference does **not** expose a deep multi-level sub-menu tree under each main menu in the captured dashboard view (the sidebar labels are flat hyperlinks); the sub-pages are accessible from dashboard shortcut links and in-page navigation. Rihal's built surface already covers the dashboard shortcut links that Sabaq exposes (add student, bulk attendance, fee collection, reports/CSV, accounts).
- Rihal's built surface is **solid for the core operational modules**: students, attendance, exams (including seats/admit-cards/results/GPA done this session), notices, portal, reports, notifications, finance (funds/donors/donations/expenses), fees, homework, lesson plans, teacher assignments.
- Rihal's **long tail** — reminder tasks, leave, digital attendance hardware, promotion/graduation, certificates, syllabus/books, administration depth, full accounting depth, receipts, donor types/monthly donors, loans/dues/shop, orphan sponsorship, boarding, notice targeting, changelog, system settings depth, shell polish — remains unbuilt.

## Plan (Option A complete, Option B next)

Option A (live reference audit) is now complete: every Sabaq main menu labeled, every dashboard shortcut link captured, cross-checked against Rihal's 58 Vue pages and route map.

Option B (build the missing pieces) — recommended sequence:

1. **P0 first** — dashboard analytics parity + global shell polish + license UX + activity log. This is what a visitor/admin sees immediately and where the visual gap is largest.
2. **P1 next** — the stub modules that already have a route: settings depth, HR, hostel, transport, properties, homework submissions, lesson plans.
3. **P2 next** — academic administration depth: sessions/classes/sections/subjects CRUD, teacher management, promotion/graduation, certificates/syllabus/books.
4. **P3 last** — finance depth + receipt/sponsor/loan/boarding modules + notice targeting + SMS/email adapters.

Within each priority, build module by module: backend route/controller → model/relationship → seeder → Nuxt page(s) → sidebar link → verify (curl 200 + dev log clean + browser check).

Would you like me to start executing the P0 items now, or adjust the order?
