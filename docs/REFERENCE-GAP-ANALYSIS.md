# Sabaq Reference Gap Analysis — Dashboard and Admin Surface

> Captured from the authenticated `demo.sabaaq.com/dashboard` HTML and compared with the Rihal repository on 2026-08-19. This is an implementation inventory, not a claim that every reference route is production-ready.

## Reference dashboard — visual and interaction inventory

### Global shell
- Bengali RTL-capable admin shell with branded logo, favicon, tenant label, collapsible sidebar, overlay for mobile, top navigation, footer.
- Multi-level sidebar tree navigation with active states and parent-module routes.
- Topbar: AI shortcut, notifications dropdown, theme switcher, language switcher (Bangla/English/Arabic), logout.
- Four themes: default, Islamic, professional, dark.
- Responsive/mobile sidebar toggle and mobile-specific sizing.
- PWA manifest, installable app metadata, mobile status-bar metadata.
- Font stack: Bengali font plus Latin fallback; Font Awesome icons.
- Toast/dropdown/modal interaction patterns.
- Floating quick actions: invoice search, student search, SMS balance, quick-actions launcher.
- Chatbot/AI floating control.

### Dashboard sections
1. **Institution banner** — madrasa name, support note, phone/contact box, support hours, WhatsApp emergency note, decorative Islamic emblem.
2. **Period filter** — all time, today, this week, this month, this year, last year.
3. **License status** — active badge, remaining days, expiry date, modal with total/used days, progress bar, renewal instructions, WhatsApp CTA.
4. **Primary metrics** — total students, total collection, total expense, current balance, total teachers, inactive students.
5. **All funds** — repeated fund cards with balance, fund category, icon and positive/negative values.
6. **Dashboard analysis**:
   - Current-month class-wise due card with empty/success state.
   - Monthly collection vs expense bar chart.
   - Monthly due bar chart.
   - Class-wise student distribution with ranked progress bars.
   - Class-wise present/absent/late/leave summary.
   - Daily class attendance taken vs pending split metric.
   - Student gender ratio visualization.
   - Top three funds by balance with ranked percentage bars.
7. **Footer** — copyright, policy warning, vendor/developer branding.

### Reference modules / navigation inventory
- Module dashboard
- System settings: site, invoice design, attendance, SMS settings/status, users, roles, global/additional admission fees, session, class, section, subjects, subject assignment, data delete, license, blank forms, cache manager, parent info settings, needy students, signatures, average-mark result settings.
- Digital attendance: attendance machine, device tools, biometric sync, ADMS commands, RFID cards, fingerprint verification.
- Student management: admission, student list, roll arrangement, dhakhela arrangement, inactive students, ID cards, fee management, homework, student attendance.
- Academic: exam creation, global/subject/exam grades, exam routine, seat numbers, admit cards, results, GPA/percentage summaries, teacher-wise results, comments, certificates, syllabus, books/curriculum, attestation/clearance certificates, admission register.
- Promotion/graduation: promotion, special promotion, pending fee, failed/success lists, graduation, foreign graduates, jobless, employed graduates, higher studies.
- Teacher management: list, IDs, salary, attendance, class routines, leave.
- Administration: staff, staff IDs, attendance, salary/raises, responsibilities, responsibility assignment, events, leave applications, complaints/suggestions, activity log, teacher discharge.
- Accounting: funds, expense types, expenses, general deposits/withdrawals, needy fund, daily balance, income/expense balance sheet, event/mahfil income/expense, annual report, properties/assets, audit report, collection-by-user, user-wise cashbook.
- Receipt management: stock, distribution, collection.
- Sponsors/donations: donor types, donors, donor payments, donation types, donations, monthly donors/payments.
- Loans/dues/shop: loans given/taken, shop, dues received/given.
- Orphan sponsorship: sponsors, sponsor payments, due/advance.
- Boarding: market purchases, boarding expenses.
- Notices: group, single, class, all notices, notice announcements.
- Changelog.

## Rihal comparison

### Already implemented in Rihal
- Auth, tenant-aware admin/teacher/student/guardian roles.
- Student/teacher CRUD and profiles.
- Attendance entry, bulk attendance, summaries.
- Exams, marks, publish/unpublish result workflow.
- Teacher assignments.
- Academic lookup page.
- Finance: funds, donors, donations, expenses, fee structures/payments, stocks, journal/cash book/summary APIs.
- Notices.
- Guardian portal and student/teacher self-service.
- Reports: attendance matrix, results, CSV exports and print views.
- Notifications: absence/fee-due in-app dispatch, read receipts and rate limits.
- Docker + CI, stateless Bearer authentication, Bengali-first global CSS, role-aware sidebar.

### Missing or incomplete versus reference
#### P0 — product shell and dashboard
- Reference-grade dashboard analytics: fund cards, period-aware dashboard data, class distribution, gender ratio, class attendance, monthly charts, top funds, due insight.
- Reference-grade global shell: theme switcher, language switcher, notification dropdown, AI/quick-action controls, support banner, polished responsive sidebar behavior.
- License/subscription status UX and renewal modal (backend status exists on tenants but no UI).
- Audit/activity log.

#### P1 — operational modules with backend routes already present
- Homework assignments and submissions UI.
- Lesson plans UI.
- Hostel rooms/visitors UI.
- Transport routes/buses UI.
- HR staff/recruitment/applications/holidays/events/registrations UI.
- Property/assets UI.
- Inventory/stock transactions UI beyond current finance page.
- User and role administration UI.

#### P2 — academic administration
- Sessions, classes, sections, subjects and subject assignment CRUD screens.
- Exam routine, seats, admit cards, GPA/percentage summaries.
- Certificates, clearance/attestation, syllabus/books/curriculum.
- Promotion/graduation workflows.

#### P3 — finance and communication depth
- Expense types, general deposits/withdrawals, needy fund, daily balance, annual reports, audit reports, user-wise collection/cashbook.
- Receipt stock/distribution/collection.
- Donor types and monthly donors.
- Loans, shop, dues, orphan sponsorship, boarding purchases/expenses.
- Group/single/class notice targeting.
- Real SMS/email gateway adapters (current code has the safe dispatch seam).

## Implementation order

1. Dashboard redesign and data density (started in `dashboard.vue`).
2. Shell polish: themes, language selector, notification center, quick actions, responsive sidebar. **Theme switching (light/dark), language menu, notification popover, student search, user menu, and quick-create controls are now implemented in `AppTopBar.vue`.**
3. CRUD pages for backend-backed operational modules (homework, lesson plans, hostel, transport, HR, property). **Homework, lesson plans, hostel rooms, transport routes, staff, and properties are now implemented** (`/homework`, `/homework/:id`, `/lesson-plans`, `/hostel`, `/transport`, `/hr`, `/properties`) and linked in the role-aware sidebar.
4. Academic administration and promotion flows. **Academic center UI plus tenant-scoped CRUD is now implemented**: `/academic` overview, `/academic/manage` setup, `/academic/timetable` weekly routine, and API create/update/delete for classes, sections, subjects, and timetable entries; promotion/exam-administration depth remains.
5. Deep finance/receipt/sponsor/loan/boarding modules.
6. End-to-end role UX, empty states, loading states, keyboard accessibility, mobile QA and visual regression.
