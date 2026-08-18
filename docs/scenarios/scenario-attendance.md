# Scenario: Super Admin Creates a New Madrasa (Tenant Provisioning)

## Actors
- **Super Admin** (platform-level administrator)
- **System** (Sabaaq Next platform)

## Preconditions
- Super Admin is logged in with platform-level credentials
- No existing tenant with the same name/registration exists

## Flow

### 1. Initiate Tenant Creation
- Super Admin clicks "Create New Madrasa" in platform dashboard
- System displays tenant creation form

### 2. Fill Tenant Details
Super Admin provides:
- **Madrasa name** (required, unique) — e.g., "দারুল কিরাত মজিদিয়া ফুলতলী ট্রাস্ট"
- **Registration number** (optional)
- **Address** (required) — street, village, upazila, district, division
- **Contact email** (required) — for system notifications
- **Contact phone** (required) — for SMS notifications
- **Principal name** (required) — who will be the primary administrator
- **Principal email/phone** (required) — for creating principal's account
- **Branches** (optional) — add multiple branch locations now or later
- **Begin date** (required) — when the madrasa starts using the system
- **License tier** (required) — selected from available tiers (Free/Micro/Small/Medium/Large/Enterprise)

### 3. Configure Initial Settings
- **Currency** (default: BDT; can be USD, INR, etc.)
- **Timezone** (default: Asia/Dhaka)
- **Language** (default: bn_BD; can add en, ar, ur later)
- **Date format** (default: DD/MM/YYYY)
- **Academic year start month** (default: January; madrasas may start in different months)
- **Grading system** (default: Bangladesh Board; options: Custom, Cambridge, etc.)

### 4. Branding (Optional)
- Upload logo (PNG/SVG, recommended size 200x200)
- Choose accent color (color picker or preset)
- Set custom domain (optional, e.g., madrasa.sabaaq.app or custom domain)

### 5. Create Principal Account
- System auto-generates username (from principal's name or email prefix)
- Principal receives email with account activation link + temporary password
- OR: Super Admin can set initial password and share directly

### 6. Tenant Provisioned
System performs:
- [ ] Creates tenant record in `tenants` table
- [ ] Creates initial admin user (Principal) with `tenant_admin` role
- [ ] Sets up default Chart of Accounts for the tenant
- [ ] Creates default academic session (current year)
- [ ] Sets up default permissions for `tenant_admin` role
- [ ] Sends welcome email to Principal with:
  - Login credentials
  - Link to admin dashboard
  - Link to setup guide
  - Link to mobile app download (iOS + Android)

### 7. Post-Creation
- Super Admin sees tenant in platform dashboard with status "Active"
- Principal can now log in and configure the madrasa further

## Error Cases

### E1: Madrasa name already exists
- System shows error: "A madrasa with this name already exists."
- Super Admin must choose a different name or search for the existing madrasa

### E2: Principal email already has an account
- System shows error: "This email is already associated with an account."
- Super Admin must use a different email or merge accounts

### E3: Invalid email/phone format
- Form validation prevents submission
- Inline error messages in Bengali/English (per Super Admin's language)

### E4: License tier not available
- Some tiers may be sold out or require approval
- System shows available tiers; if selected tier is restricted, show message

## Postconditions
- New madrasa tenant exists in the system
- Principal has valid credentials and can log in
- Tenant has default configuration ready to use
- Platform Super Admin can manage the tenant (suspend, configure, view usage)

---

# Scenario: Principal (Tenant Admin) Onboards Their Madrasa

## Actors
- **Principal** (tenant administrator, just created)
- **System**

## Preconditions
- Principal has received account creation email
- Principal can log in

## Flow

### 1. First Login
- Principal clicks activation link or goes to login page
- Logs in with email + temporary password
- System forces password change on first login

### 2. Welcome Walkthrough
System shows an interactive onboarding walkthrough:
1. **Welcome** — "Welcome to Sabaaq Next! Let's set up your madrasa."
2. **Profile** — Verify/complete principal's profile (name, photo, contact)
3. **Madrasa Info** — Verify madrasa details (name, address, logo, branding)
4. **Academic Setup** — Set up current academic session, classes, sections
5. **Staff Setup** — Add yourself (if not auto-created), add other staff/teachers
6. **Student Import** — Option to import students via Excel/CSV or add manually
7. **Fee Setup** — Configure fee structure for the session
8. **Finish** — "You're ready! Start using Sabaaq Next."

Each step is optional (can be done later), but the walkthrough suggests a good order.

### 3. Configuration (Any Order, Any Time)
Principal can configure:
- Madrasa profile (name, logo, address, contact)
- Academic sessions (create new session, rollover previous session)
- Classes and sections
- Subjects and teacher assignments
- Fee structures
- User accounts (teachers, accountants, staff)
- Device settings (biometric, RFID)
- SMS/email gateway configuration
- Custom branding

### 4. Data Migration (Optional)
If migrating from another system or paper records:
- Import students via CSV (with template download)
- Import staff via CSV
- Import fee records via CSV
- System validates and reports import results (success count, errors with row numbers)

## Postconditions
- Madrasa is configured and ready for daily use
- Principal knows how to use the system (from walkthrough)
- Initial data (staff, students, fee structure) is in the system

---

# Scenario: Teacher Takes Attendance

## Actors
- **Teacher** (logged into web or mobile app)
- **Students** (in the teacher's class)
- **System**

## Preconditions
- Teacher has a class scheduled today
- Attendance module is enabled for the madrasa
- Attendance method is configured (manual, fingerprint, QR, facial — or combination)

## Flow (Manual Attendance — Primary Path)

### 1. Teacher Opens Attendance
- Teacher opens web app or mobile app
- Dashboard shows "Today's Classes" with class name, time, students count
- Teacher clicks on the class → attendance page

### 2. Teacher Views Student List
- System displays list of students in that class/section for today's session
- Each student shows: photo (if available), name, roll number, status (present/absent by default: absent)
- If previously marked today, status shows the marked value

### 3. Teacher Marks Attendance
- Teacher taps/clicks each student to toggle present/absent
- OR: "Mark All Present" button, then toggle exceptions
- Teacher can add remarks per student (optional): "late", "sick", "family event", etc.
- Teacher selects absence reason from dropdown if marking absent (optional based on madrasa settings)

### 4. Teacher Submits
- Teacher clicks "Save Attendance" or "Submit"
- System validates: all students have a status (no blanks)
- System records attendance with: teacher_id, class_id, section_id, session_id, date, time, student statuses, remarks
- System shows confirmation: "Attendance saved. X present, Y absent."

### 5. System Actions (After Save)
- [ ] Updates student attendance record in database
- [ ] Updates attendance analytics (daily stats)
- [ ] If a student is marked absent: triggers parent notification (SMS + app push) based on madrasa settings (immediate, after 30 min, at lunch break)
- [ ] If attendance integration with payroll: teacher's attendance record is updated (for staff attendance)
- [ ] Real-time dashboard updates (principal can see live attendance)

## Flow (Fingerprint / Biometric Attendance)

### 1. Student Scans Fingerprint
- Student places finger on biometric device at school entrance or classroom
- Device sends fingerprint data to Sabaaq Next (via local server or direct API)
- System matches fingerprint to student record
- System marks student as present with timestamp

### 2. Teacher Reviews (Optional)
- Teacher can see who has been marked present via fingerprint
- Teacher can override if needed (e.g., fingerprint failed, student marked absent by system but is actually present)

## Flow (Mobile QR Attendance)

### 1. Teacher Displays QR Code
- Teacher opens mobile app → "Start Attendance" → QR code displayed on teacher's phone
- QR code is unique per class + time window (valid for 10 minutes)
- QR code is geo-fenced (valid only near the school location)

### 2. Student Scans QR
- Student opens parent/student mobile app → "Scan for Attendance" → scans teacher's QR
- App validates: correct QR, within time window, geo-fenced location
- App marks student as present with timestamp
- App shows confirmation to student

### 3. Teacher Reviews
- Teacher's app shows count of students who checked in via QR
- Teacher can see who hasn't checked in and mark manually

## Error Cases

### E1: Student not in system
- Fingerprint/QR scan fails to match any student
- System logs: "Unknown fingerprint/QR attempt"
- Teacher manually adds student or reports issue

### E2: Attendance already taken for this class today
- Teacher tries to open attendance for a class already marked
- System shows: "Attendance for this class was already taken at [time] by [teacher]. View or override?"
- If override: teacher can modify (with audit log of change)

### E3: No internet connection (mobile app)
- Teacher's app detects offline
- Teacher can still mark attendance (saved locally)
- App shows: "Offline — attendance will sync when online"
- When connectivity returns, app syncs attendance to server
- If conflict (another device marked attendance for same class), show conflict resolution UI

### E4: Parent notification fails
- SMS gateway returns error
- System retries (with backoff)
- If still fails after retries: log error, notify admin (principal or system admin) that parent notification failed for specific students

## Postconditions
- Attendance is recorded for all students in the class for today's session
- Analytics are updated
- Parent notifications sent (if applicable)
- Teacher's attendance record is saved (for payroll linkage)
