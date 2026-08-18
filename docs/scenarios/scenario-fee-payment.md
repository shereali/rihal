# Scenario: Accountant Records a Fee Payment

## Actors
- **Accountant** (logged into web app)
- **Student/Parent** (fee payer)
- **System**

## Preconditions
- Fee structure is configured for the current academic session
- Student has fee dues (or is paying admission fee, etc.)
- Accountant has permission to record fee payments

## Flow

### 1. Accountant Opens Fee Module
- Accountant navigates to Finance → Fee Payments
- Dashboard shows: total outstanding fees, today's collected, recent payments

### 2. Accountant Initiates Payment Recording
Options:
- **Search by student:** Type student name/roll/number → select student
- **Create new payment:** Click "Record Payment" → fill details
- **Bulk import:** Upload CSV of multiple payments (for batch data entry)

### 3. Accountant Enters Payment Details
- **Student** (auto-filled if searched)
- **Fee head** — which fee this payment is for (admission fee, monthly fee, exam fee, etc.)
- **Amount** — amount paid
- **Payment date** — date of payment (default: today)
- **Payment method** — Cash, bKash, Nagad, Bank Transfer, Card, Cheque, Other
- **Reference number** — transaction ref (for bKash/Nagad/bank): optional but recommended
- ** Receipt number** — auto-generated sequential receipt number (e.g., REC-2026-00001)
- **Notes** — optional notes (e.g., "partial payment for July")

If payment is partial: system shows remaining balance after this payment.

### 4. Accountant Saves Payment
- System validates:
  - Amount > 0
  - Fee head exists and is applicable to this student's class
  - Payment date is valid (not in future, not before student enrollment)
- System creates fee payment record: student_id, fee_head_id, amount, date, method, reference, receipt_number, notes, recorded_by (accountant), created_at
- System updates:
  - Student's fee balance (reduces outstanding for this fee head)
  - Fee collection daily statistics
  - Fee collection this month/this session
- System generates receipt (PDF) — downloadable, printable
- Receipt includes: madrasa name/logo, receipt number, student name/roll, fee head, amount, date, payment method, reference number, madrasa seal/signature (if configured)

### 5. System Notifications (Optional, Configurable)
- [ ] SMS to parent: "Your fee payment of BDT X has been received. Balance: BDT Y. Receipt: REC-2026-00001"
- [ ] App push to parent (if parent has app)
- [ ] Email receipt to parent (if email provided)
- [ ] Notification to accountant: "Payment recorded — receipt REC-2026-00001"

### 6. Accountant Verification
- Accountant can view the receipt (PDF preview in app)
- Accountant can print receipt (if printer connected)
- Accountant can download receipt as PDF
- Receipt is stored in the system (linked to payment record)

## Flow: Online Payment (bKash/Nagad Integration)

### 1. Parent Initiates Payment
- Parent logs into mobile app
- Navigates to Fees → Pay Now
- Sees outstanding fees with breakdown
- Selects fee head(s) to pay
- Clicks "Pay with bKash" (or Nagad, etc.)

### 2. Payment Flow
- System generates payment request to bKash/Nagad API
- Parent receives payment instruction (account number, amount, reference)
- Parent completes payment on their bKash/Nagad app
- bKash/Nagad sends payment confirmation callback to Sabaaq Next
- System validates callback (signature verification)
- System records payment automatically (same as manual recording above)

### 3. Parent Receives Receipt
- App shows: "Payment successful! Receipt: REC-2026-00001"
- Receipt available in app (download/view)
- SMS sent to parent with receipt details

## Error Cases

### E1: Payment amount exceeds outstanding
- Student's outstanding for this fee head is BDT 500; accountant enters BDT 600
- System shows error: "Amount exceeds outstanding balance (BDT 500)."
- Accountant must enter correct amount (or system can auto-adjust to outstanding)

### E2: Duplicate receipt number
- Receipt numbers are sequential and unique per madrasa
- System prevents duplicate: "Receipt number REC-2026-00001 already exists."
- Auto-generate next available receipt number

### E3: Payment for wrong fee head
- Accountant selects wrong fee head (e.g., admission fee instead of monthly fee)
- To correct: Accountant can edit the payment (if within grace period/permission) or create a refund + re-record
- All edits are audit-logged (who changed what, when)

### E4: Online payment callback fails
- bKash/Nagad callback doesn't arrive (network issue)
- System shows: "Payment confirmation pending. We'll check and update shortly."
- Background job polls payment status with bKash/Nagad every 30 seconds for up to 10 minutes
- If still not confirmed: mark as "pending verification", notify accountant to manually verify

### E5: Offline — no connectivity
- Accountant's web app is offline
- Accountant can't record payment (write requires server)
- Shows: "You are offline. Payment recording requires internet connection."
- Mitigation: Allow offline data entry into a local queue? (Complex — financial transactions need server-side validation. Defer to V2.)

## Postconditions
- Fee payment recorded in system
- Student's fee balance updated
- Receipt generated and available
- Notifications sent (if configured)
- Financial reports (daily collection, monthly collection) updated
- Account is audit-logged
