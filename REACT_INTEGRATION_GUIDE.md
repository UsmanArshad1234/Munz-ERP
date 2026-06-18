# MUZN ERP — React Integration Guide

> **Audience:** Frontend / React developers  
> **Last updated:** 2026-06-18  
> **Base URL:** `https://api.muzndelivery.com/api`  
> **Auth:** Bearer token via `Authorization: Bearer <token>` header on every request

---

## 1. Authentication

```
POST /auth/login
Body: { email, password }
Response: { data: { token, user } }
```

Store the token (localStorage / Context). Send it with every subsequent request.

---

## 2. Dashboard

### 2.1 Main overview

```
GET /dashboard/overview
```

**Query params (all optional):**

| Param | Type | Description |
|-------|------|-------------|
| `month` | string | Format `YYYY-MM` — defaults to current month |
| `emirate` | string | Setting value from `GET /settings/emirate` |
| `zone` | string | Setting value from `GET /settings/zone` |
| `platform` | string | Setting value from `GET /settings/platform` |
| `employee_status` | string | `active` or `inactive` (fixed enum) |
| `bike_status` | string | `available`, `assigned`, or `maintenance` (fixed enum) |
| `employee_id` | integer | Scope entire dashboard to one rider (use employee's numeric `id`) |

**Owner/Superadmin response:**
```json
{
  "data": {
    "month": "2026-06",
    "filters_applied": { },
    "employees": {
      "total_employees": 45,
      "active_employees": 40,
      "inactive_employees": 5,
      "noon_riders": 12,
      "talabat_riders": 18,
      "careem_riders": 7,
      "keeta_riders": 2,
      "other_riders": 1
    },
    "bikes": {
      "total_bikes": 50,
      "assigned_bikes": 38,
      "available_bikes": 10,
      "inactive_bikes": 2,
      "bikes_without_worker": 3
    },
    "emirate_breakdown": [
      { "emirate": "dubai", "employees": 30, "bikes": 35 },
      { "emirate": "sharjah", "employees": 10, "bikes": 12 }
    ],
    "salary_summary": { "gross_payroll": 95000, "loans_deduction": 5000, "fines_deduction": 1200, "salik_deduction": 800, "net_payroll": 88000, "payroll_rows": 40 },
    "loans_fines_salik": { "active_loans_remaining": 15000, "fines_this_month": 2500, "salik_this_month": 800, "pending_deductions": 3000 },
    "platform_income": { "noon": 42000, "talabat": 65000, "careem": 28000, "keeta": 9000, "other": 1500, "total": 145500 },
    "expenses_by_category": { "breakdown": [{ "category": "fuel", "amount": 3200 }], "total": 8500 },
    "net_profit": { "platform_income": 145500, "payroll": 88000, "expenses": 8500, "company_fines": 500, "company_salik": 800, "muzn_net_profit": 47700 },
    "status_checks": { "income_rows": 4, "expense_rows": 12, "payroll_rows": 40, "fine_rows": 8, "salik_rows": 40 },
    "expiry_alerts": { ... },
    "alerts": { ... }
  }
}
```

**Admin response:** only `employees`, `bikes`, `emirate_breakdown`, `expiry_alerts`.

### 2.2 Alerts only

```
GET /dashboard/alerts?month=2026-06
```

Returns expiry alerts for employee documents + bike documents + financial alerts (owner only).

### 2.3 Status checks

```
GET /dashboard/status?month=2026-06&emirate=dubai&employee_id=5
```

Returns row counts: `income_rows`, `expense_rows`, `payroll_rows`, `fine_rows`, `salik_rows`.

---

## 3. Dashboard Filter Dropdowns

All dynamic dropdowns on the dashboard use the Settings API. The flow is:

```
GET /settings/types          → check if the type key exists in the array
GET /settings/{type}         → fetch the options for that type
```

### 3.1 Types the dashboard uses

| Dropdown | Type key for Settings API | Param sent to dashboard |
|----------|--------------------------|------------------------|
| Emirate | `emirate` | `?emirate=` |
| Zone | `zone` | `?zone=` |
| Platform | `platform` | `?platform=` |

> **Note:** `emirate` is a backend alias for `work_emirate` and `platform` is a backend alias for `platform_name`. You can call `GET /settings/emirate` and `GET /settings/platform` directly — the backend resolves them.

### 3.2 Fetching options (pattern)

```js
async function useSettingsOptions(type) {
  const types = await api.get('/settings/types');           // returns string[]
  if (!types.data.includes(type)) return [];               // hidden if type missing

  const res = await api.get(`/settings/${type}`);
  return res.data.map(row => ({
    value: row.value,   // send this as the filter param value
    label: row.label,   // display this in the dropdown
  }));
}
```

### 3.3 Fixed enum filters (hardcode these, no API call needed)

```js
const EMPLOYEE_STATUS_OPTIONS = [
  { value: 'active',   label: 'Active' },
  { value: 'inactive', label: 'Inactive' },
];

const BIKE_STATUS_OPTIONS = [
  { value: 'available',    label: 'Available' },
  { value: 'assigned',     label: 'Assigned' },
  { value: 'maintenance',  label: 'Under Maintenance' },
];
```

### 3.4 Employee / Rider picker (employee_id filter)

To let users pick a specific rider:

```
GET /employees?status=active&per_page=200
```

Map the response to `{ value: employee.id, label: employee.name }`. Send the selected `id` as `?employee_id=<integer>` to the dashboard. When `employee_id` is set, all dashboard sections (employee stats, bike stats, payroll, fines) are scoped to that one rider.

---

## 4. Settings API (Full Reference)

```
GET  /settings               → all settings grouped by type
GET  /settings/types         → array of valid type keys
GET  /settings/{type}        → rows for one type (use for dropdowns)
POST /settings               → create (owner/superadmin only)
PUT  /settings/{id}          → update label / sort_order
DELETE /settings/{id}        → delete (non-default only)
PUT  /settings/{type}/reorder → reorder: body { ids: [3,1,4,2] }
```

`GET /settings/{type}` response per row:
```json
{ "id": 1, "value": "dubai", "label": "Dubai", "sort_order": 0, "is_default": true }
```

---

## 5. Employees

### 5.1 List

```
GET /employees?page=1&per_page=20&status=active&search=Ahmed
       &work_emirate=dubai&platform_name=noon&department=operations
```

### 5.2 Create

```
POST /employees
Content-Type: application/json

Required: name
Optional: mobile, email, nationality, job_title, department, status,
          work_emirate, zone, platform_name, platform_id,
          salary_amount, salary_type, wps_status,
          passport_number, passport_expiry,
          emirates_id, emirates_id_expiry,
          visa_expiry, labour_card_expiry,
          driving_license, driving_license_expiry
```

### 5.3 Bulk Import

```
POST /employees/bulk-import
Content-Type: multipart/form-data
file: <xlsx | xls | csv>
```

**Required columns in the file:** `name`, `mobile`

All other columns are optional. Date columns must be `YYYY-MM-DD`.

**Possible row error messages:**
- `Missing name`
- `Missing mobile number`
- `Wrong date format in {field} (use YYYY-MM-DD)`
- `Duplicate Emirates ID {eid} in file`
- `Emirates ID {eid} already exists in system`
- `Invalid email format`

**Response 200** — all rows imported  
**Response 207** — partial success; use `error_report_token` to download CSV:

```
GET /employees/bulk-import/error-report/{error_report_token}
→ downloads error-report-{token}.csv
```

### 5.4 Bulk Update

```
POST /employees/bulk-update
Content-Type: multipart/form-data
file: <xlsx | xls | csv>
```

Required column: `employee_id` (e.g. `EMP-0001`). Only columns present in the file are updated.

Updatable columns: `mobile`, `email`, `work_emirate`, `zone`, `platform_name`, `salary_amount`, `status`, `passport_expiry`, `emirates_id_expiry`, `visa_expiry`, `labour_card_expiry`, `driving_license_expiry`

---

## 6. Documents

### Employee documents

```
GET    /employees/{id}/documents                        → all docs grouped by type
POST   /employees/{id}/documents                        → upload (multipart: file, document_type, expiry_date)
POST   /employees/{id}/documents/{type}/upload          → upload or replace by type
DELETE /employees/{id}/documents/{docId}
```

Document types: `passport`, `visa`, `emirates_id`, `labour_card`, `driving_license`, `other`

### Motorbike documents

```
GET    /motorbikes/{id}/documents
POST   /motorbikes/{id}/documents                       → multipart: file, document_type, expiry_date
DELETE /motorbikes/{id}/documents/{docId}
```

---

## 7. Expiry Alerts

```
GET /employees/expiry-alerts?days=30
GET /motorbikes/expiry-alerts
```

Each alert: `{ entity_name, document, expiry_date, days_left, severity }`  
Severity values: `expired` | `critical` (≤15 days) | `warning` (≤60 days)

---

## 8. Changed Endpoints (v2 — 2026-06-18)

These endpoints were updated. If you integrated before this date, review:

| Endpoint | What changed |
|----------|-------------|
| `GET /settings/types` | Now includes `emirate` and `platform` in the returned array |
| `GET /settings/emirate` | **New alias** — returns same rows as `work_emirate` |
| `GET /settings/platform` | **New alias** — returns same rows as `platform_name` |
| `GET /dashboard/overview` | **New param:** `employee_id` (integer) — scope to single rider |
| `GET /dashboard/status` | **New param:** `employee_id` (integer) |
| `GET /dashboard/alerts` | **New param:** `employee_id` (integer) |
| `POST /employees/bulk-import` | **`mobile` is now required** (was optional) — rows without mobile return error "Missing mobile number" |

---

## 9. Error Response Shape

All errors follow the same structure:

```json
{
  "success": false,
  "message": "Validation failed",
  "errors": {
    "field": ["error message"]
  }
}
```

HTTP codes:
- `200` success
- `201` created
- `207` partial success (bulk import with some errors)
- `401` unauthenticated
- `403` forbidden (missing permission)
- `404` not found
- `422` validation error

---

## 10. Pagination

List endpoints return:

```json
{
  "data": {
    "data": [...],
    "current_page": 1,
    "last_page": 5,
    "per_page": 20,
    "total": 95
  }
}
```

Use `?page=N&per_page=N` to paginate.
