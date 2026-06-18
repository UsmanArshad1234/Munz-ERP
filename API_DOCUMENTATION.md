# MUZN ERP — API Documentation

**Base URL:** `https://api.muzndelivery.com/api`  
**Auth:** Bearer Token (Laravel Sanctum)  
**Format:** All requests/responses in JSON  
**Header required on all protected routes:**
```
Authorization: Bearer {token}
Accept: application/json
```

---

## Default Credentials

| Role | Email | Password |
|------|-------|----------|
| Owner | owner@muzn.ae | Muzn@Owner2025 |
| Superadmin | superadmin@muzn.ae | Muzn@Super2025 |
| Admin | admin@muzn.ae | Muzn@Admin2025 |

---

## Roles & Access

| Role | Access Level |
|------|-------------|
| `owner` | Full access — all modules including financial |
| `superadmin` | Elevated — same as owner except system config |
| `admin` | Operational — employees, bikes, assignments, fines, payroll |

---

## Standard Response Format

**Success:**
```json
{
  "success": true,
  "message": "Done",
  "data": { }
}
```

**Error:**
```json
{
  "success": false,
  "message": "Error message",
  "errors": { }
}
```

**Paginated:**
```json
{
  "success": true,
  "message": "...",
  "data": {
    "data": [ ],
    "current_page": 1,
    "last_page": 5,
    "per_page": 15,
    "total": 72
  }
}
```

---

---

# 🔐 AUTH

## Login
`POST /auth/login` — Public (no token needed)  
Rate limited: 5 attempts per minute

**Request:**
```json
{
  "email": "owner@muzn.ae",
  "password": "Muzn@Owner2025"
}
```

**Response:**
```json
{
  "success": true,
  "message": "Login successful",
  "data": {
    "token": "1|abc123...",
    "user": {
      "id": 1,
      "name": "MUZN Owner",
      "email": "owner@muzn.ae",
      "role": "owner",
      "status": "active"
    }
  }
}
```

---

## Logout
`POST /auth/logout` 🔒

## Logout All Devices
`POST /auth/logout-all` 🔒

## Get My Profile
`GET /auth/me` 🔒

## Update Profile
`PUT /auth/profile` 🔒

**Request:**
```json
{
  "name": "My Name",
  "email": "email@muzn.ae"
}
```

## Change Password
`PUT /auth/change-password` 🔒

**Request:**
```json
{
  "current_password": "oldpass",
  "password": "NewPass@123",
  "password_confirmation": "NewPass@123"
}
```

## My Permissions
`GET /auth/my-permissions` 🔒

---

---

# ⚙️ SETTINGS

> Used for dropdown values: zones, departments, platforms, bike types, etc.

## Get All Settings
`GET /settings` 🔒

## Get Setting Types
`GET /settings/types` 🔒

**Response:**
```json
{
  "data": ["zone", "department", "platform", "bike_type", "fine_type"]
}
```

## Get Settings by Type
`GET /settings/{type}` 🔒

Example: `GET /settings/zone`

## Create Setting
`POST /settings` 🔒 `permission: settings.manage`

**Request:**
```json
{
  "type": "zone",
  "value": "downtown",
  "label": "Downtown Dubai",
  "sort_order": 1
}
```

## Update Setting
`PUT /settings/{id}` 🔒 `permission: settings.manage`

## Delete Setting
`DELETE /settings/{id}` 🔒 `permission: settings.manage`

## Reorder Settings
`PUT /settings/{type}/reorder` 🔒 `permission: settings.manage`

**Request:**
```json
{
  "order": [3, 1, 4, 2]
}
```

---

---

# 👥 EMPLOYEES

## List Employees
`GET /employees` 🔒 `permission: employees.view`

**Query Params:**
| Param | Type | Example |
|-------|------|---------|
| page | int | 1 |
| per_page | int | 15 |
| status | string | active / inactive |
| search | string | Ahmed |
| department | string | Operations |
| work_emirate | string | Dubai |

## Employee Stats
`GET /employees/stats` 🔒 `permission: employees.view`

**Response:**
```json
{
  "data": {
    "total": 45,
    "active": 40,
    "inactive": 5,
    "on_leave": 2,
    "expiry_alerts": 3
  }
}
```

## Expiry Alerts
`GET /employees/expiry-alerts` 🔒 `permission: employees.view`

Returns employees whose documents expire within 30 days.

## Create Employee
`POST /employees` 🔒 `permission: employees.create`

**Request:**
```json
{
  "name": "Ahmed Ali",
  "mobile": "+971501234567",
  "email": "ahmed@muzn.com",
  "nationality": "Pakistani",
  "job_title": "Delivery Rider",
  "department": "Operations",
  "status": "active",
  "work_emirate": "Dubai",
  "zone": "Downtown",
  "platform_name": "Talabat",
  "platform_id": "TAL-001",
  "salary_amount": 2500.00,
  "salary_type": "monthly",
  "wps_status": "enrolled",
  "passport_number": "AB1234567",
  "passport_expiry": "2027-12-31",
  "emirates_id": "784-1990-1234567-1",
  "emirates_id_expiry": "2026-06-30",
  "visa_expiry": "2026-08-15",
  "labour_card_expiry": "2026-08-15",
  "driving_license": "DL-12345",
  "driving_license_expiry": "2027-03-20",
  "notes": "Optional notes"
}
```

**Response:** Returns created employee with auto-generated `employee_id` (EMP-0001).

## Get Employee
`GET /employees/{id}` 🔒 `permission: employees.view`

## Update Employee
`PUT /employees/{id}` 🔒 `permission: employees.update`

## Delete Employee
`DELETE /employees/{id}` 🔒 `permission: employees.delete`

---

### Employee Documents

## List Documents
`GET /employees/{id}/documents` 🔒 `permission: documents.view`

## Upload Document
`POST /employees/{id}/documents` 🔒 `permission: documents.upload`

**Request:** `multipart/form-data`
| Field | Type | Values |
|-------|------|--------|
| document_type | string | passport, emirates_id, visa, labour_card, driving_license, other |
| file | file | PDF/JPG/PNG (max 5MB) |
| notes | string | optional |

## Delete Document
`DELETE /employees/{id}/documents/{documentId}` 🔒 `permission: documents.delete`

---

---

# 🏍️ MOTORBIKES

## List Motorbikes
`GET /motorbikes` 🔒 `permission: motorbikes.view`

**Query Params:**
| Param | Type | Example |
|-------|------|---------|
| page | int | 1 |
| status | string | available / assigned / maintenance |
| emirate | string | Dubai |
| search | string | plate number |

## Motorbike Stats
`GET /motorbikes/stats` 🔒 `permission: motorbikes.view`

**Response:**
```json
{
  "data": {
    "total": 30,
    "available": 12,
    "assigned": 15,
    "maintenance": 3,
    "expiry_alerts": 2
  }
}
```

## Expiry Alerts
`GET /motorbikes/expiry-alerts` 🔒 `permission: motorbikes.view`

## Create Motorbike
`POST /motorbikes` 🔒 `permission: motorbikes.create`

**Request:**
```json
{
  "plate_number": "12345",
  "plate_code": "A",
  "emirate": "Dubai",
  "zone": "Downtown",
  "bike_type": "delivery",
  "brand": "Honda",
  "model": "CB150",
  "year": 2023,
  "color": "Red",
  "chassis_number": "CH123456789",
  "engine_number": "EN123456789",
  "insurance_company": "AXA Insurance",
  "insurance_expiry": "2026-12-31",
  "mulkiya_expiry": "2026-12-31",
  "status": "available",
  "notes": "Optional"
}
```

**Response:** Returns with auto-generated `bike_id` (BK-0001).

## Get Motorbike
`GET /motorbikes/{id}` 🔒 `permission: motorbikes.view`

## Update Motorbike
`PUT /motorbikes/{id}` 🔒 `permission: motorbikes.update`

## Delete Motorbike
`DELETE /motorbikes/{id}` 🔒 `permission: motorbikes.delete`

---

### Bike Documents

## List Documents
`GET /motorbikes/{id}/documents` 🔒 `permission: documents.view`

## Upload Document
`POST /motorbikes/{id}/documents` 🔒 `permission: documents.upload`

**Request:** `multipart/form-data`
| Field | Values |
|-------|--------|
| document_type | mulkiya, insurance, other |
| file | PDF/JPG/PNG |

## Delete Document
`DELETE /motorbikes/{id}/documents/{documentId}` 🔒 `permission: documents.delete`

---

---

# 🔄 ASSIGNMENTS

## List Assignments
`GET /assignments` 🔒 `permission: assignments.view`

**Query Params:** `page`, `status` (active/returned/cancelled), `employee_id`, `motorbike_id`

## Current Assignments
`GET /assignments/current` 🔒 `permission: assignments.view`

Returns all currently active assignments.

## Assignment Stats
`GET /assignments/stats` 🔒 `permission: assignments.view`

## Assign Bike to Employee
`POST /assignments/assign` 🔒 `permission: assignments.create`

**Request:**
```json
{
  "employee_id": 1,
  "motorbike_id": 1,
  "start_date": "2026-06-10",
  "handover_condition": "Good condition, no damage"
}
```

> Note: Employee must not have an active assignment. Bike must be available.

## Get Assignment
`GET /assignments/{id}` 🔒 `permission: assignments.view`

## Return Bike
`POST /assignments/{id}/return` 🔒 `permission: assignments.update`

**Request:**
```json
{
  "return_date": "2026-06-10",
  "return_condition": "Good condition",
  "remarks": "Returned on time"
}
```

## Mark Pending Return
`PATCH /assignments/{id}/pending-return` 🔒 `permission: assignments.update`

## Cancel Assignment
`PATCH /assignments/{id}/cancel` 🔒 `permission: assignments.update`

## Employee Assignment History
`GET /assignments/employee/{employeeId}/history` 🔒 `permission: assignments.view`

## Bike Assignment History
`GET /assignments/bike/{bikeId}/history` 🔒 `permission: assignments.view`

---

---

# 💰 LOANS

## Loan Stats
`GET /loans/stats` 🔒 `permission: loans.view`

**Response:**
```json
{
  "data": {
    "total_loans": 20,
    "active": 15,
    "paid": 4,
    "on_hold": 1,
    "total_disbursed": 50000.00,
    "total_outstanding": 30000.00
  }
}
```

## List Loans
`GET /loans` 🔒 `permission: loans.view`

**Query Params:** `page`, `status` (active/paid/cancelled/on_hold), `employee_id`

## Create Loan
`POST /loans` 🔒 `permission: loans.create`

**Request:**
```json
{
  "employee_id": 1,
  "loan_date": "2026-06-10",
  "loan_amount": 3000.00,
  "monthly_deduction": 500.00,
  "number_of_installments": 6,
  "notes": "Emergency loan"
}
```

> Note: Employee can only have ONE active loan at a time.

## Get Loan
`GET /loans/{id}` 🔒 `permission: loans.view`

## Update Loan
`PUT /loans/{id}` 🔒 `permission: loans.update`

```json
{
  "monthly_deduction": 600.00,
  "status": "on_hold",
  "notes": "On hold"
}
```

## Record Manual Payment
`POST /loans/{id}/payments` 🔒 `permission: loans.update`

```json
{
  "payment_date": "2026-06-10",
  "payment_amount": 500.00,
  "payment_method": "cash",
  "notes": "Cash received"
}
```

> Note: `payment_method` values: `cash`, `bank_transfer`, `payroll`

## Get Loan Payments
`GET /loans/{id}/payments` 🔒 `permission: loans.view`

## Upload Attachment
`POST /loans/{id}/attachment` 🔒 `permission: loans.update`

**Request:** `multipart/form-data` — field: `attachment`

---

---

# 💵 PAYROLL

> Payroll auto-calculates loan deduction and fines on creation.

## Payroll Stats
`GET /payroll/stats?month=6&year=2026` 🔒 `permission: payroll.view`

**Response:**
```json
{
  "data": {
    "total_payrolls": 40,
    "draft": 10,
    "approved": 28,
    "rejected": 2,
    "total_gross": 100000.00,
    "total_net": 92000.00,
    "total_deductions": 8000.00
  }
}
```

## List Payrolls
`GET /payroll` 🔒 `permission: payroll.view`

**Query Params:** `page`, `month`, `year`, `payroll_status` (draft/approved/rejected), `payment_status` (unpaid/paid), `employee_id`

## Create Payroll
`POST /payroll` 🔒 `permission: payroll.create`

**Request:**
```json
{
  "employee_id": 1,
  "month": 6,
  "year": 2026,
  "attendance_days": 26,
  "hours_compliance": true,
  "salik_deduction": 150.00,
  "penalty_deduction": 0,
  "other_deduction": 0,
  "notes": "June 2026"
}
```

> Auto-calculated on creation:
> - `gross_salary` = employee's `salary_amount`
> - `loan_deduction` = active loan's `monthly_deduction`
> - `fine_deduction` = sum of pending fines
> - `total_deductions` = sum of all deductions
> - `net_salary` = gross - total_deductions

## Get Payroll
`GET /payroll/{id}` 🔒 `permission: payroll.view`

## Update Payroll
`PUT /payroll/{id}` 🔒 `permission: payroll.update`

> Only draft payrolls can be updated.

## Approve Payroll
`POST /payroll/{id}/approve` 🔒 `permission: payroll.approve`

```json
{
  "notes": "Approved"
}
```

> On approval:
> - Pending fines are marked as `deducted`
> - Loan payment record is auto-created
> - Loan balance is updated automatically

## Reject Payroll
`POST /payroll/{id}/reject` 🔒 `permission: payroll.approve`

```json
{
  "notes": "Needs correction"
}
```

## Mark Paid
`PATCH /payroll/{id}/mark-paid` 🔒 `permission: payroll.update`

## Download Salary Slip (PDF)
`GET /payroll/{id}/slip` 🔒 `permission: payroll.view`

Returns: `application/pdf` file download.

---

---

# 🚨 FINES

## Fine Stats
`GET /fines/stats` 🔒 `permission: fines.view`

**Response:**
```json
{
  "data": {
    "total": 50,
    "pending": 20,
    "deducted": 25,
    "waived": 5,
    "total_amount": 3500.00,
    "pending_amount": 1200.00
  }
}
```

## List Fines
`GET /fines` 🔒 `permission: fines.view`

**Query Params:** `page`, `status` (pending/deducted/waived), `fine_type`, `employee_id`, `date_from`, `date_to`

## Create Fine
`POST /fines` 🔒 `permission: fines.create`

**Request:**
```json
{
  "employee_id": 1,
  "fine_date": "2026-06-10",
  "fine_type": "salik",
  "amount": 50.00,
  "description": "Salik charge - Al Maktoum Bridge",
  "notes": "Optional"
}
```

> `fine_type` values: `salik`, `traffic_fine`, `company_penalty`, `other`

## Pending Fines by Employee
`GET /fines/employee/{employeeId}/pending` 🔒 `permission: fines.view`

## Get Fine
`GET /fines/{id}` 🔒 `permission: fines.view`

## Update Fine
`PUT /fines/{id}` 🔒 `permission: fines.update`

> Cannot update if status is `deducted`.

## Waive Fine
`PATCH /fines/{id}/waive` 🔒 `permission: fines.update`

```json
{
  "notes": "Waived by management"
}
```

> Cannot waive if already `deducted`.

## Delete Fine
`DELETE /fines/{id}` 🔒 `permission: fines.delete`

> Cannot delete if status is `deducted`.

## Upload Receipt
`POST /fines/{id}/receipt` 🔒 `permission: fines.update`

**Request:** `multipart/form-data` — field: `receipt`

---

---

# 💸 EXPENSES

## Expense Stats
`GET /expenses/stats?month=6&year=2026` 🔒 `permission: expenses.view`

**Response:**
```json
{
  "data": {
    "total": 30,
    "pending": 10,
    "approved": 18,
    "rejected": 2,
    "total_amount": 25000.00,
    "approved_amount": 20000.00,
    "by_category": {
      "fuel": 5000.00,
      "maintenance": 8000.00,
      "office": 2000.00
    }
  }
}
```

## Expense Categories
`GET /expenses/categories` 🔒 `permission: expenses.view`

Returns: `["fuel", "maintenance", "office", "salary", "salik", "other"]`

## List Expenses
`GET /expenses` 🔒 `permission: expenses.view`

**Query Params:** `page`, `category`, `status` (pending/approved/rejected), `date_from`, `date_to`

## Create Expense
`POST /expenses` 🔒 `permission: expenses.create`

**Request:**
```json
{
  "expense_date": "2026-06-10",
  "category": "fuel",
  "amount": 200.00,
  "description": "Fuel for delivery bikes",
  "vendor_name": "ADNOC Station",
  "notes": "Optional"
}
```

> `category` values: `fuel`, `maintenance`, `office`, `salary`, `salik`, `other`

## Get Expense
`GET /expenses/{id}` 🔒 `permission: expenses.view`

## Update Expense
`PUT /expenses/{id}` 🔒 `permission: expenses.update`

> Cannot update if `approved`.

## Approve Expense
`POST /expenses/{id}/approve` 🔒 `permission: expenses.update`

```json
{ "notes": "Approved" }
```

## Reject Expense
`POST /expenses/{id}/reject` 🔒 `permission: expenses.update`

```json
{ "notes": "Duplicate entry" }
```

## Delete Expense
`DELETE /expenses/{id}` 🔒 `permission: expenses.delete`

> Cannot delete if `approved`.

## Upload Receipt
`POST /expenses/{id}/receipt` 🔒 `permission: expenses.update`

**Request:** `multipart/form-data` — field: `receipt`

---

---

# 📊 DASHBOARD

## Overview
`GET /dashboard/overview` 🔒 `permission: dashboard.view`

**Owner/Superadmin Response:**
```json
{
  "data": {
    "operational": {
      "total_employees": 45,
      "active_employees": 40,
      "total_bikes": 30,
      "available_bikes": 12,
      "active_assignments": 28
    },
    "financial": {
      "current_month_income": 85000.00,
      "current_month_expenses": 62000.00,
      "current_month_payroll": 45000.00,
      "net_profit": 23000.00
    },
    "alerts": [ ]
  }
}
```

**Admin Response:** Only `operational` section — no financial data.

## Alerts
`GET /dashboard/alerts` 🔒 `permission: dashboard.view`

**Response:**
```json
{
  "data": {
    "employee_document_expiry": [ ],
    "bike_document_expiry": [ ],
    "upcoming_maintenance": [ ]
  }
}
```

---

---

# 🔧 MAINTENANCE

## Maintenance Stats
`GET /maintenance/stats` 🔒 `permission: maintenance.view`

## Upcoming Maintenance
`GET /maintenance/upcoming` 🔒 `permission: maintenance.view`

Returns bikes with `next_maintenance_date` within 14 days.

## List Maintenance
`GET /maintenance` 🔒 `permission: maintenance.view`

**Query Params:** `page`, `status` (completed/pending/in_progress), `motorbike_id`, `maintenance_type`

## Create Maintenance
`POST /maintenance` 🔒 `permission: maintenance.create`

**Request:**
```json
{
  "motorbike_id": 1,
  "maintenance_date": "2026-06-10",
  "maintenance_type": "oil_change",
  "cost": 150.00,
  "description": "Regular oil change",
  "vendor_name": "Al Futtaim Workshop",
  "next_maintenance_date": "2026-09-10",
  "status": "completed",
  "notes": "Optional"
}
```

> `maintenance_type` values: `oil_change`, `tire`, `brake`, `engine`, `accident_repair`, `general`, `other`  
> `status` values: `completed`, `pending`, `in_progress`

## Get Maintenance
`GET /maintenance/{id}` 🔒 `permission: maintenance.view`

## Update Maintenance
`PUT /maintenance/{id}` 🔒 `permission: maintenance.update`

## Delete Maintenance
`DELETE /maintenance/{id}` 🔒 `permission: maintenance.delete`

## Upload Receipt
`POST /maintenance/{id}/receipt` 🔒 `permission: maintenance.update`

**Request:** `multipart/form-data` — field: `receipt`

---

---

# 💹 PLATFORM INCOME

> Owner & Superadmin access only.

## Platform List
`GET /platform-income/platforms` 🔒 `permission: platform_income.view`

Returns: `["Talabat", "Careem", "Noon", "InDrive", "Other"]`

## Income Stats
`GET /platform-income/stats?month=6&year=2026` 🔒 `permission: platform_income.view`

**Response:**
```json
{
  "data": {
    "total_income": 95000.00,
    "platform_income": 85000.00,
    "rider_income": 10000.00,
    "by_platform": {
      "Talabat": 45000.00,
      "Careem": 25000.00,
      "Noon": 15000.00
    }
  }
}
```

## List Platform Incomes
`GET /platform-income` 🔒 `permission: platform_income.view`

**Query Params:** `page`, `month`, `year`, `source_type` (platform/rider), `platform_name`

## Create Income — Platform Payout
`POST /platform-income` 🔒 `permission: platform_income.create`

```json
{
  "income_date": "2026-06-10",
  "source_type": "platform",
  "platform_name": "Talabat",
  "amount": 15000.00,
  "description": "Weekly payout"
}
```

## Create Income — Rider Cash
`POST /platform-income` 🔒 `permission: platform_income.create`

```json
{
  "income_date": "2026-06-10",
  "source_type": "rider",
  "employee_id": 1,
  "amount": 500.00,
  "description": "Cash collected from rider"
}
```

> `source_type`: `platform` (needs `platform_name`) or `rider` (needs `employee_id`)

## Get Income
`GET /platform-income/{id}` 🔒 `permission: platform_income.view`

## Update Income
`PUT /platform-income/{id}` 🔒 `permission: platform_income.update`

## Delete Income
`DELETE /platform-income/{id}` 🔒 `permission: platform_income.delete`

## Upload Receipt
`POST /platform-income/{id}/receipt` 🔒 `permission: platform_income.update`

---

---

# 📉 PROFIT & LOSS

> Owner & Superadmin only. `permission: profit_loss.view`

## P&L Summary
`GET /profit-loss/summary?month=6&year=2026` 🔒

**Response:**
```json
{
  "data": {
    "month": 6,
    "year": 2026,
    "income": {
      "platform_income": 85000.00,
      "rider_income": 10000.00,
      "total_income": 95000.00
    },
    "expenses": {
      "payroll": 45000.00,
      "operational_expenses": 12000.00,
      "loan_disbursements": 5000.00,
      "fines": 1000.00,
      "maintenance": 3000.00,
      "total_expenses": 66000.00
    },
    "net_profit": 29000.00,
    "profit_margin": "30.5%"
  }
}
```

## Monthly Trend
`GET /profit-loss/monthly-trend?year=2026` 🔒

Returns 12-month income vs expenses chart data.

---

---

# 📈 REPORTS

## Employees Excel
`GET /reports/employees/excel` 🔒 `permission: reports.export`

**Query Params:** `status`

Returns: `.xlsx` file download

## Payroll Excel
`GET /reports/payroll/excel?month=6&year=2026` 🔒 `permission: reports.export`

Returns: `.xlsx` file download

## Expenses Excel
`GET /reports/expenses/excel?month=6&year=2026` 🔒 `permission: reports.export`

Returns: `.xlsx` file download

## Fines Excel
`GET /reports/fines/excel` 🔒 `permission: reports.export`

**Query Params:** `status`, `fine_type`

Returns: `.xlsx` file download

## Payroll PDF Report
`GET /reports/payroll/pdf?month=6&year=2026` 🔒 `permission: reports.export`

Returns: `.pdf` file download (landscape, summary table)

## Profit & Loss PDF
`GET /reports/profit-loss/pdf?month=6&year=2026` 🔒 `permission: reports.financial`

Returns: `.pdf` file download

---

---

# 👤 USER MANAGEMENT

> Owner & Superadmin only.

## List Users
`GET /users` 🔒

## Create User
`POST /users` 🔒

**Request:**
```json
{
  "name": "New Admin",
  "email": "newadmin@muzn.ae",
  "password": "Admin@123",
  "password_confirmation": "Admin@123",
  "role": "admin"
}
```

> `role` values: `admin`, `superadmin`  
> Owner cannot be created via API.

## Get User
`GET /users/{id}` 🔒

## Update User
`PUT /users/{id}` 🔒

## Delete User
`DELETE /users/{id}` 🔒

## Toggle User Status
`PATCH /users/{id}/toggle-status` 🔒

---

---

# 🔑 PERMISSIONS

> Owner & Superadmin only.

## All Permissions
`GET /permissions` 🔒

Returns all 52 permission slugs grouped by module.

## Role Permissions
`GET /permissions/role/{role}` 🔒

> `role`: `owner`, `superadmin`, `admin`

## Update Role Permissions
`PUT /permissions/role/{role}` 🔒

**Request:**
```json
{
  "permissions": [
    "employees.view",
    "employees.create",
    "motorbikes.view",
    "assignments.view"
  ]
}
```

## User Permissions (Per-user Override)
`GET /permissions/user/{userId}` 🔒

## Update User Permissions
`PUT /permissions/user/{userId}` 🔒

```json
{
  "permissions": ["payroll.view", "payroll.create"]
}
```

## Reset User Permissions
`DELETE /permissions/user/{userId}/reset` 🔒

Removes per-user overrides — reverts to role defaults.

---

---

# 📋 AUDIT LOGS

> Owner & Superadmin only.

## List Audit Logs
`GET /audit-logs` 🔒

**Query Params:**
| Param | Example |
|-------|---------|
| page | 1 |
| model_type | Employee |
| action | created / updated / deleted |
| user_id | 1 |
| date_from | 2026-06-01 |
| date_to | 2026-06-30 |
| search | EMP-0001 |

## Model Types
`GET /audit-logs/model-types` 🔒

Returns all audited model names.

## Model Audit Trail
`GET /audit-logs/{modelType}/{modelId}` 🔒

Example: `GET /audit-logs/Employee/1`

> `modelType` values: `Employee`, `Motorbike`, `Assignment`, `Loan`, `Payroll`, `Fine`, `Expense`, `PlatformIncome`, `Maintenance`

**Response:**
```json
{
  "data": [
    {
      "id": 1,
      "action": "updated",
      "model_ref": "EMP-0001",
      "user_name": "MUZN Owner",
      "old_values": { "salary_amount": "2500.00" },
      "new_values": { "salary_amount": "2800.00" },
      "ip_address": "192.168.1.1",
      "created_at": "2026-06-10T14:30:00"
    }
  ]
}
```

---

---

# 📌 All Permissions Reference

```
employees.view          employees.create        employees.update
employees.delete        documents.view          documents.upload
documents.delete        motorbikes.view         motorbikes.create
motorbikes.update       motorbikes.delete       assignments.view
assignments.create      assignments.update      loans.view
loans.create            loans.update            payroll.view
payroll.create          payroll.update          payroll.approve
fines.view              fines.create            fines.update
fines.delete            expenses.view           expenses.create
expenses.update         expenses.delete         dashboard.view
maintenance.view        maintenance.create      maintenance.update
maintenance.delete      platform_income.view    platform_income.create
platform_income.update  platform_income.delete  profit_loss.view
reports.export          reports.financial       settings.manage
```

---

# 🚀 Quick Start for Frontend

```javascript
// 1. Login
const res = await fetch('https://api.muzndelivery.com/api/auth/login', {
  method: 'POST',
  headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
  body: JSON.stringify({ email: 'owner@muzn.ae', password: 'Muzn@Owner2025' })
});
const { data } = await res.json();
const token = data.token;

// 2. Authenticated request
const employees = await fetch('https://api.muzndelivery.com/api/employees', {
  headers: {
    'Authorization': `Bearer ${token}`,
    'Accept': 'application/json'
  }
});
```

---

*MUZN ERP API — Built with Laravel 13, Sanctum v4.3.2*  
*All dates format: YYYY-MM-DD | All amounts: decimal (2 places)*
