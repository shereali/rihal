# Rihal — Project File Manifest

This document lists every file in the Rihal project as of Week 1 completion.

**Total files:** ~180

---

## Laravel Backend (146 files)

### Models (57 files)
- Tenant.php
- User.php
- Role.php
- Permission.php
- TenantBranch.php
- AcademicSession.php
- AcademicClass.php
- AcademicSection.php
- AcademicSubject.php
- AcademicTimetable.php
- Enrollment.php
- TeacherAssignment.php
- Student.php
- StudentGuardian.php
- StudentDocument.php
- StudentHealthRecord.php
- StudentMedicalHistory.php
- Teacher.php
- TeacherQualification.php
- TeacherContract.php
- EmergencyContact.php
- Exam.php
- ExamSeatPlan.php
- ExamQuestion.php
- MarkEntry.php
- Result.php
- ReportCardComment.php
- HomeworkAssignment.php
- HomeworkSubmission.php
- LessonPlan.php
- AttendanceRecord.php
- AttendanceDevice.php
- AttendancePattern.php
- Fund.php
- ChartOfAccount.php
- JournalEntry.php
- JournalEntryLine.php
- CashBook.php
- FeeStructure.php
- FeePayment.php
- Donor.php
- Donation.php
- Expense.php
- Budget.php
- Vendor.php
- Notice.php
- NoticeReadReceipt.php
- CommsTemplate.php
- EmergencyAlert.php
- EmergencyAlertAcknowledgement.php
- Holiday.php
- Event.php
- EventRegistration.php
- HostelRoom.php
- HostelVisitor.php
- TransportBus.php
- TransportRoute.php
- TransportAssignment.php
- Stock.php
- StockTransaction.php
- Recruitment.php
- JobApplication.php
- TrusteeShip.php
- Property.php
- PropertyDocument.php
- ActivityLog.php
- SystemSetting.php
- ReminderTask.php
- Plugin.php
- AuditLog.php
- Staff.php
- UserTokenAccess.php

### Controllers (4 files)
- Api/ApiController.php
- Api/V1/AuthController.php
- Api/V1/TenantController.php
- Api/V1/UserController.php

### Middleware (4 files)
- AuthenticateWithToken.php
- EnsureTenantScoped.php
- CheckRole.php
- TrimStrings.php

### Form Requests (4 files)
- Api/V1/RegisterRequest.php
- Api/V1/LoginRequest.php
- Api/V1/CreateTenantRequest.php
- Api/V1/CreateUserRequest.php

### Services (2 files)
- AuthService.php
- TenantService.php

### Providers (1 file)
- AppServiceProvider.php

### Exceptions (1 file)
- Handler.php

### Console Commands (1 file)
- Console/Commands/SetupDemoCommand.php

### Configs (16 files)
- app.php
- auth.php
- cache.php
- cors.php
- database.php
- logging.php
- queue.php
- sanctum.php
- session.php
- tenancy.php
- view.php
- pagination.php
- app_settings.php
- broadcasting.php
- failed_jobs.php
- jwt.php

### Routes (3 files)
- api.php
- web.php
- console.php

### Migrations (13 files)
- 2026_08_16_001_create_tenants_table.php
- 2026_08_16_002_create_users_table.php
- 2026_08_16_003_create_roles_permissions_table.php
- 2026_08_16_004_create_system_tables.php
- 2026_08_16_005_create_students_table.php
- 2026_08_16_006_create_academic_tables.php
- 2026_08_16_007_create_teachers_staff_table.php
- 2026_08_16_008_create_exams_results_table.php
- 2026_08_16_009_create_attendance_table.php
- 2026_08_16_010_create_homework_lesson_table.php
- 2026_08_16_011_create_finance_tables.php
- 2026_08_16_012_create_notice_communication_tables.php
- 2026_08_16_013_create_hostel_transport_tables.php

### Seeders (4 files)
- DatabaseSeeder.php
- PlatformAdminSeeder.php
- DemoTenantSeeder.php
- RolePermissionSeeder.php

### Tests (4 files)
- TestCase.php
- Feature/AuthTest.php
- Feature/TenantTest.php
- Feature/UserTest.php
- Unit/ExampleTest.php

### Other (7 files)
- bootstrap/app.php
- composer.json
- .env.example
- public/index.html
- Dockerfile
- composer-wrapper.sh

---

## Nuxt Frontend (20 files)

### Core
- nuxt.config.ts
- package.json
- app.vue
- app/css/main.css
- css/main.css (entry point)
- types/index.ts

### API Layer
- utils/api.ts
- composables/useApi.ts
- plugins/api.ts
- plugins/api.client.ts

### Auth
- composables/useAuth.ts
- stores/auth.ts

### Layouts
- layouts/default.vue
- layouts/auth.vue

### Components
- components/layout/AppSidebar.vue
- components/layout/AppTopBar.vue

### Pages
- pages/login.vue
- pages/dashboard.vue
- pages/register.vue

### Assets
- assets/css/main.css

---

## Root Project Files (7 files)

- README.md
- WEEKS.md
- FILES.md
- docker-compose.yml
- .gitignore
- pnpm-workspace.yaml
- package.json

---

## Documentation (3 files)

- docs/architecture/ADRs.md
- docs/scenarios/scenario-attendance.md
- docs/scenarios/scenario-fee-payment.md

---

*Last updated: August 16, 2026*
