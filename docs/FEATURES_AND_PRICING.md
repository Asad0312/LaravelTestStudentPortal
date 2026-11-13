# IRAA Student Portal — Features & Pricing Proposal

**Iqra Riffat ull Atfal Academy — School + Madarsa Management System**

---

## 1. What this system does

A complete web-based school management system that runs in the browser (no installation on client PCs), works on mobile, and covers the daily work of a school and madarsa from admission to result card.

It supports three student types in one system:

- **School** — Montessori + Class 1 to 8
- **Madarsa** — Sections A, B, C, D
- **Both** — a student enrolled in school and madarsa at the same time

---

## 2. Admin Panel Features

### 2.1 Dashboard
- Total students, teachers, fee collected, pending fee
- New admissions and recent activity at a glance
- Class-wise student count

### 2.2 Students
- Add / edit / delete students
- Auto Student ID (`STU-1`, `STU-2`, …)
- School / Madarsa / Both stream
- Montessori + Class 1–8, Madarsa Section A–D
- Monthly fee per student (admin editable)
- Auto portal password (`name123`) or custom password
- Phone / WhatsApp in both formats (`03XXXXXXXXX` or `+923XXXXXXXXX`)
- Family filter — same contact number par jitne students hain sab aik saath
- Student profile with complete fee, attendance, test and marks history

### 2.3 Admissions
- Online admission form with stream, class/section, fee and password
- Approve / reject admission
- Approved admission automatically creates a student record

### 2.4 Student Lookup
- Search any student by ID, name or phone
- One-screen view: profile, attendance, tests, marks, class teacher

### 2.5 Attendance
- Daily Present / Absent marking
- QR code attendance (student ID card scan)
- “Already marked / updated” safe handling — no duplicate entries
- Attendance history and per-student percentage

### 2.6 Fee Management
- Monthly fee rows auto-generated for all active students
- **Auto carry-forward:** agar pichle mahine ki fee baqi hai to next month ki fee me add ho jati hai, with a clear “Carried from …” indicator
- Partial payments supported
- Fee history per student (month by month)
- Print fee slip
- Contact number column
- Filter by Student ID / Name / Phone / WhatsApp
- CSV export

### 2.7 Exams & Exam Fees
- Announce exam for a stream / class / section
- Exam fee automatically generated for the matching students
- Fee list with Paid / Unpaid status
- One-click “Mark Paid”
- Student portal par exam fee show

### 2.8 Tests & Marks
- Weekly / Monthly test plan per stream, class and section
- Marks entry per student
- Automatic class / student alerts
- Portal par test aur marks update

### 2.9 Books
- Book list per stream / class / section
- Price per book
- Ready-made subject templates + auto-fill
- Student portal par assigned books

### 2.10 Messages / Announcements
- Send message to all students or a specific class
- Priority and expiry date
- Student portal par announcement

### 2.11 Teachers
- Full teacher profile: name, father name, gender, phone/WhatsApp, email, designation, subject, qualification, CNIC, joining date, monthly salary, status
- Teacher directory with search
- **Salary management:** monthly salary, paid, remaining, Paid / Pending / Unpaid status
- Salary payment with method and note
- Salary history per teacher

### 2.12 Office Expenses
- Daily / monthly office expense entry
- Categories: Rent, Utilities, Stationery, Repair, Transport, Cleaning, Furniture, Marketing, Misc
- Paid to, payment method, voucher/reference and note
- Monthly filter, category filter and search
- Monthly total, today total, all-time total
- Category-wise breakdown
- Edit / delete records

### 2.13 Settings & Reports
- School settings
- CSV export for students, fees and attendance
- Activity and notification logs

---

## 3. Student Portal

- Secure student login
- Dashboard with profile and class
- Attendance record
- Fee record and pending balance
- Test schedule and marks
- Books list
- Exam fees
- Messages / announcements
- **Digital ID card** — CR80 (credit-card size), photo, student ID, class, contact, session, printable QR code

---

## 4. Technical & Security

- Laravel + MySQL/PostgreSQL
- Responsive UI (mobile, tablet, desktop)
- Password-protected admin, hashed passwords
- Role-based access (admin; teacher panel in progress)
- Cloud-ready (Vercel + Neon PostgreSQL)
- Data backup/export

---

## 5. Why a client will like it

1. **One system for school + madarsa** — no separate registers.
2. **Fee recovery is automatic** — carry-forward makes pending fee impossible to forget.
3. **Family fee collection** — same phone number wale sab bachay aik screen par.
4. **QR attendance** — fast and error-free.
5. **Parent-facing portal** — fee, attendance, marks, books, ID card sab online.
6. **Print-ready fee slips and ID cards** — professional look.
7. **Teacher salary + office expenses** — complete school accounting in one place.
8. **No software installation** — browser par chalta hai, mobile par bhi.
9. **White-label branding** — client ke school ka naam aur logo.
10. **CSV export** — data client ke paas, locked-in nahi.

---

## 6. Recommended Add-ons (Phase 2 — to impress clients)

| # | Feature | Client benefit |
|---|---------|----------------|
| 1 | WhatsApp / SMS alerts (fee due, absent, result) | Parents ko automatic updates |
| 2 | Online fee payment (JazzCash / Easypaisa / bank) | Fee collection without cash |
| 3 | Parent login app / PWA | Parents ko mobile app jaisa experience |
| 4 | Bulk student import (Excel/CSV) | Purana data minutes me import |
| 5 | Result card / report card PDF | Term result professional print |
| 6 | Monthly income vs expense (P&L) | Owner ko profit/loss report |
| 7 | Teacher attendance + payroll | Staff management complete |
| 8 | Multi-branch support | Chain of schools |
| 9 | Timetable & subject management | Daily routine |
| 10 | Certificates (leaving, character) | One-click certificates |
| 11 | RFID / biometric attendance | Faster than QR |
| 12 | Automatic cloud backup + audit log | Data safety |

---

## 7. Pricing Model

**Rate:** Rs **18 per student per month**

**Billing:** 1-year installation (annual billing)

**Your share:** **20% of the 1-year amount**

### Formula

```
Annual amount  = Students × Rs 18 × 12 months
Your 20% share = Annual amount × 0.20
```

### Examples

| Students | Monthly (18/student) | 1-Year Amount | Your 20% |
|---------:|---------------------:|--------------:|---------:|
| 100 | Rs 1,800 | Rs 21,600 | Rs 4,320 |
| 150 | Rs 2,700 | Rs 32,400 | Rs 6,480 |
| 200 | Rs 3,600 | Rs 43,200 | Rs 8,640 |
| 300 | Rs 5,400 | Rs 64,800 | Rs 12,960 |
| 400 | Rs 7,200 | Rs 86,400 | Rs 17,280 |
| 500 | Rs 9,000 | Rs 108,000 | Rs 21,600 |
| 1,000 | Rs 18,000 | Rs 216,000 | Rs 43,200 |

### Notes / Suggestions

- Minimum contract: **100 students** (recommended) ta ke per-client support cost cover ho.
- Price should be reviewed every 12 months.
- Domain, hosting and WhatsApp/SMS API cost client ke account par ho (recommended).
- 20% share “of the 1-year amount” hai — client full annual amount pay karega, aap apna 20% rakhenge.
- Agar 20% **advance** lena hai to installation ke waqt hi invoice karein.
- Tax / govt charges extra.

---

## 8. Demo Checklist (client meeting)

1. Login → Dashboard
2. Add Student (School + Madarsa + Montessori)
3. Same phone par 2 students → fee filter
4. Fee payment + carry-forward indicator
5. Fee history + print slip
6. QR attendance scan
7. Test + marks → student portal
8. Books + exam fee
9. Teacher add + salary pay
10. Office expense entry + monthly report
11. Student portal login + ID card print
12. CSV export

---

*Prepared for IRAA Student Portal — Iqra Riffat ull Atfal Academy.*
