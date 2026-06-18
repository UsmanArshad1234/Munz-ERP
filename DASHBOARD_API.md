# MUZN ERP — Dashboard & Bulk Import API Guide
### React Developer Implementation Reference

---

## Quick Start

```
Base URL: https://api.muzndelivery.com/api
Auth:     Bearer token (set in Authorization header)
```

```js
// Axios base setup
import axios from 'axios'

const api = axios.create({
  baseURL: 'https://api.muzndelivery.com/api',
  headers: {
    Authorization: `Bearer ${localStorage.getItem('token')}`,
    Accept: 'application/json',
  },
})
```

---

## 1. Dashboard Overview

### Endpoint
```
GET /dashboard/overview
```

### Query Parameters (saare optional)

| Param | Type | Example | Description |
|---|---|---|---|
| `month` | string | `2026-04` | Format: YYYY-MM. Default: current month |
| `emirate` | string | `Dubai` | Filter by emirate |
| `zone` | string | `Z1` | Filter by zone |
| `platform` | string | `Noon` | Filter by platform |
| `employee_status` | string | `active` | `active` or `inactive` |
| `bike_status` | string | `assigned` | `assigned`, `available`, `inactive` |

### Usage Examples

```js
// Current month, no filters
const res = await api.get('/dashboard/overview')

// Specific month
const res = await api.get('/dashboard/overview', {
  params: { month: '2026-04' }
})

// Month + Emirate + Platform filter
const res = await api.get('/dashboard/overview', {
  params: { month: '2026-04', emirate: 'Dubai', platform: 'Noon' }
})
```

### Response Structure

> **Owner / Superadmin** ko full response milta hai.
> **Admin** ko sirf: `month`, `employees`, `bikes`, `emirate_breakdown`, `expiry_alerts`.

```json
{
  "success": true,
  "message": "Dashboard data retrieved",
  "data": {

    "month": "2026-06",

    "filters_applied": {
      "emirate": "Dubai",
      "platform": "Noon"
    },

    "employees": {
      "total_employees":    38,
      "active_employees":   35,
      "inactive_employees":  3,
      "noon_riders":         4,
      "talabat_riders":      0,
      "keeta_riders":        0,
      "careem_riders":       0,
      "other_riders":        0
    },

    "bikes": {
      "total_bikes":          41,
      "assigned_bikes":       12,
      "available_bikes":      29,
      "inactive_bikes":        0,
      "bikes_without_worker": 29
    },

    "emirate_breakdown": [
      { "emirate": "Dubai",     "employees": 7,  "bikes": 8  },
      { "emirate": "UAQ",       "employees": 15, "bikes": 16 },
      { "emirate": "Fujairah",  "employees": 16, "bikes": 17 },
      { "emirate": "Abu Dhabi", "employees": 0,  "bikes": 0  }
    ],

    "salary_summary": {
      "gross_payroll":   12000.00,
      "loans_deduction":   750.00,
      "fines_deduction":   650.00,
      "salik_deduction":   300.00,
      "net_payroll":     10300.00,
      "payroll_rows":    4
    },

    "loans_fines_salik": {
      "active_loans_remaining": 13000.00,
      "fines_this_month":         650.00,
      "salik_this_month":         300.00,
      "pending_deductions":      1850.00
    },

    "platform_income": {
      "noon":    60000.00,
      "talabat": 20000.00,
      "careem":  10000.00,
      "keeta":       0.00,
      "other":    5000.00,
      "total":   95000.00
    },

    "expenses_by_category": {
      "breakdown": [
        { "category": "accommodation", "amount": 4000.00 },
        { "category": "sim",           "amount":  300.00 },
        { "category": "office",        "amount":  500.00 },
        { "category": "bike",          "amount": 1200.00 },
        { "category": "other",         "amount":  750.00 }
      ],
      "total": 6750.00
    },

    "net_profit": {
      "platform_income": 95000.00,
      "payroll":         45000.00,
      "expenses":         6750.00,
      "company_fines":     500.00,
      "company_salik":     300.00,
      "muzn_net_profit": 42450.00
    },

    "status_checks": {
      "month":        "2026-06",
      "income_rows":  5,
      "expense_rows": 5,
      "payroll_rows": 4,
      "fine_rows":    3,
      "salik_rows":   10
    },

    "expiry_alerts": {
      "employee_documents": [
        {
          "entity_type": "employee",
          "entity_id":   1,
          "entity_ref":  "EMP-0001",
          "entity_name": "Ahmed Ali",
          "document":    "Visa",
          "expiry_date": "2026-06-19",
          "days_left":   7,
          "severity":    "critical",
          "message":     "Ahmed Ali - Visa - Expiring in 7 days"
        }
      ],
      "motorbike_documents": [
        {
          "entity_type": "motorbike",
          "entity_id":   3,
          "entity_ref":  "BIKE-0003",
          "entity_name": "DXB 12345",
          "document":    "Insurance",
          "expiry_date": "2026-07-01",
          "days_left":   19,
          "severity":    "warning",
          "message":     "Bike DXB 12345 - Insurance - Expiring in 19 days"
        }
      ],
      "total_alerts":   5,
      "expired_count":  1,
      "critical_count": 2,
      "warning_count":  2
    },

    "alerts": {
      "employee_documents":  [],
      "motorbike_documents": [],
      "total_alerts": 5,
      "expired_count": 1,
      "critical_count": 2,
      "warning_count": 2,
      "financial_alerts": {
        "pending_expenses":     3,
        "pending_fines_amount": 650.00,
        "unpaid_payrolls":      2
      }
    }

  }
}
```

---

## 2. Dashboard Status Checks

### Endpoint
```
GET /dashboard/status
```

### Usage
```js
const res = await api.get('/dashboard/status', {
  params: { month: '2026-04' }
})
```

### Response
```json
{
  "data": {
    "month":        "2026-04",
    "income_rows":  5,
    "expense_rows": 5,
    "payroll_rows": 4,
    "fine_rows":    3,
    "salik_rows":   10
  }
}
```

> **Use case:** Month select karo aur check karo kya us month ka data enter hua hai ya nahi. Agar `income_rows: 0` hai to income data nahi daala gaya abhi.

---

## 3. Dashboard Alerts

### Endpoint
```
GET /dashboard/alerts
```

### Usage
```js
const res = await api.get('/dashboard/alerts', {
  params: { month: '2026-06' }
})
```

### Response
Same as `expiry_alerts` + `financial_alerts` (owner only) from overview.

---

## 4. Severity Colors (Expiry Alerts)

```js
const severityColor = {
  expired:  '#ef4444',  // red
  critical: '#f97316',  // orange  (<=15 days)
  warning:  '#eab308',  // yellow  (16-60 days)
}
```

---

## 5. Month Filter — React Component Pattern

```jsx
import { useState } from 'react'

function MonthFilter({ onChange }) {
  const [month, setMonth] = useState(() => {
    const now = new Date()
    return `${now.getFullYear()}-${String(now.getMonth() + 1).padStart(2, '0')}`
  })

  const handleChange = (e) => {
    setMonth(e.target.value)
    onChange(e.target.value)
  }

  return (
    <input
      type="month"
      value={month}
      onChange={handleChange}
      className="border rounded px-3 py-2"
    />
  )
}

// Usage in Dashboard
function Dashboard() {
  const [month, setMonth] = useState('2026-06')
  const [filters, setFilters] = useState({})

  const { data } = useQuery({
    queryKey: ['dashboard', month, filters],
    queryFn: () => api.get('/dashboard/overview', {
      params: { month, ...filters }
    }).then(r => r.data.data)
  })

  return (
    <>
      <MonthFilter onChange={setMonth} />
      {/* render data sections */}
    </>
  )
}
```

---

## 6. Bulk Import Employees

### Endpoint
```
POST /employees/bulk-import
Content-Type: multipart/form-data
```

### Excel/CSV Template Columns

| Column | Required | Format | Example |
|---|---|---|---|
| `name` | ✅ Yes | text | Ahmed Ali |
| `mobile` | No | text | +971501234567 |
| `email` | No | email | ahmed@muzn.com |
| `nationality` | No | text | Pakistani |
| `work_emirate` | No | text | Dubai |
| `zone` | No | text | Z1 |
| `platform_name` | No | text | Noon |
| `salary_amount` | No | number | 2500 |
| `salary_type` | No | text | monthly |
| `emirates_id` | No | text | 784-1990-1234567-1 |
| `passport_number` | No | text | AB1234567 |
| `passport_expiry` | No | YYYY-MM-DD | 2027-12-31 |
| `emirates_id_expiry` | No | YYYY-MM-DD | 2026-06-30 |
| `visa_expiry` | No | YYYY-MM-DD | 2026-08-15 |
| `labour_card_expiry` | No | YYYY-MM-DD | 2026-08-15 |
| `driving_license_expiry` | No | YYYY-MM-DD | 2027-03-20 |
| `status` | No | text | active |
| `notes` | No | text | any notes |

> **Important:** Date columns must be **text formatted** in Excel (not date cells). Use `YYYY-MM-DD` format.

### React Upload Component

```jsx
function BulkImportButton() {
  const [loading, setLoading] = useState(false)
  const [result, setResult]   = useState(null)

  const handleUpload = async (e) => {
    const file = e.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)

    setLoading(true)
    try {
      const res = await api.post('/employees/bulk-import', formData, {
        headers: { 'Content-Type': 'multipart/form-data' }
      })
      setResult(res.data.data)
    } catch (err) {
      console.error(err)
    } finally {
      setLoading(false)
    }
  }

  return (
    <div>
      <input type="file" accept=".xlsx,.xls,.csv" onChange={handleUpload} />
      {loading && <p>Importing...</p>}
      {result && (
        <div>
          <p>Found: {result.rows_found} | Imported: {result.valid_rows} | Failed: {result.error_rows}</p>

          {result.error_rows > 0 && (
            <>
              {/* Show errors inline */}
              <ul>
                {result.errors.map((e, i) => (
                  <li key={i}>Row {e.row}: {e.errors.join(', ')}</li>
                ))}
              </ul>

              {/* Download error report CSV */}
              {result.error_report_token && (
                <a
                  href={`/api/employees/bulk-import/error-report/${result.error_report_token}`}
                  download
                >
                  Download Error Report CSV
                </a>
              )}
            </>
          )}
        </div>
      )}
    </div>
  )
}
```

### Import Response

```json
{
  "success": true,
  "message": "Import complete: 45 imported, 5 failed.",
  "data": {
    "rows_found":         50,
    "valid_rows":         45,
    "error_rows":          5,
    "errors": [
      { "row": 8,  "errors": ["Missing name"] },
      { "row": 12, "errors": ["Emirates ID 784-1234 already exists in system"] },
      { "row": 20, "errors": ["Wrong date format in passport_expiry (use YYYY-MM-DD)"] },
      { "row": 35, "errors": ["Invalid email format"] },
      { "row": 48, "errors": ["Duplicate Emirates ID 784-5678 in file"] }
    ],
    "error_report_token": "550e8400-e29b-41d4-a716-446655440000"
  }
}
```

> HTTP 200 = all rows successful. HTTP 207 = partial success (some errors).

---

## 7. Bulk Update Employees

### Endpoint
```
POST /employees/bulk-update
Content-Type: multipart/form-data
```

### Excel/CSV Template Columns

| Column | Required | Description |
|---|---|---|
| `employee_id` | ✅ Yes | EMP-0001 format — used to match existing employee |
| `mobile` | No | Updated mobile |
| `email` | No | Updated email |
| `work_emirate` | No | Updated emirate |
| `zone` | No | Updated zone |
| `platform_name` | No | Updated platform |
| `salary_amount` | No | Updated salary |
| `status` | No | active / inactive |
| `passport_expiry` | No | YYYY-MM-DD |
| `emirates_id_expiry` | No | YYYY-MM-DD |
| `visa_expiry` | No | YYYY-MM-DD |
| `labour_card_expiry` | No | YYYY-MM-DD |
| `driving_license_expiry` | No | YYYY-MM-DD |

> **Only columns present in the file are updated.** Empty cells are skipped.

### React Upload Component

```jsx
function BulkUpdateButton() {
  const handleUpload = async (e) => {
    const file = e.target.files[0]
    if (!file) return

    const formData = new FormData()
    formData.append('file', file)

    const res = await api.post('/employees/bulk-update', formData, {
      headers: { 'Content-Type': 'multipart/form-data' }
    })

    const { updated_rows, error_rows, errors, error_report_token } = res.data.data
    console.log(`Updated: ${updated_rows}, Failed: ${error_rows}`)
  }

  return <input type="file" accept=".xlsx,.xls,.csv" onChange={handleUpload} />
}
```

### Update Response

```json
{
  "success": true,
  "message": "Update complete: 48 updated, 2 failed.",
  "data": {
    "rows_found":   50,
    "updated_rows": 48,
    "error_rows":    2,
    "errors": [
      { "row": 5,  "errors": ["Missing employee_id"] },
      { "row": 23, "errors": ["Employee EMP-9999 not found"] }
    ],
    "error_report_token": "660f9511-f30c-52e5-b827-557766551111"
  }
}
```

---

## 8. Download Error Report

### Endpoint
```
GET /employees/bulk-import/error-report/{token}
```

### Usage

```js
// Direct browser download
window.location.href = `/api/employees/bulk-import/error-report/${token}`

// OR with axios (blob)
const res = await api.get(`/employees/bulk-import/error-report/${token}`, {
  responseType: 'blob'
})
const url = window.URL.createObjectURL(res.data)
const a   = document.createElement('a')
a.href    = url
a.download = 'error-report.csv'
a.click()
```

---

## 9. Filter Combinations Reference

| Use Case | Params |
|---|---|
| Current month, all data | No params |
| Specific month | `?month=2026-04` |
| Dubai data only | `?month=2026-04&emirate=Dubai` |
| Dubai Noon riders only | `?month=2026-04&emirate=Dubai&platform=Noon` |
| Active employees only | `?employee_status=active` |
| Available bikes only | `?bike_status=available` |
| UAQ zone Z2 | `?emirate=UAQ&zone=Z2` |

---

## 10. API Permissions

| Endpoint | Minimum Role |
|---|---|
| `GET /dashboard/overview` | Any (requires `dashboard.view` permission) |
| `GET /dashboard/status` | Any (requires `dashboard.view` permission) |
| `GET /dashboard/alerts` | Any (requires `dashboard.view` permission) |
| Financial sections in overview | Owner / Superadmin only |
| `POST /employees/bulk-import` | `employees.create` permission |
| `POST /employees/bulk-update` | `employees.update` permission |
| `GET /employees/bulk-import/error-report/{token}` | `employees.view` permission |

---

## 11. React Query Setup (Recommended)

```js
import { useQuery } from '@tanstack/react-query'

// Dashboard hook
export function useDashboard(month, filters = {}) {
  return useQuery({
    queryKey: ['dashboard', 'overview', month, filters],
    queryFn:  () => api.get('/dashboard/overview', {
      params: { month, ...filters }
    }).then(r => r.data.data),
    staleTime: 1000 * 60 * 5, // 5 min cache
  })
}

// Status checks hook
export function useDashboardStatus(month, filters = {}) {
  return useQuery({
    queryKey: ['dashboard', 'status', month, filters],
    queryFn:  () => api.get('/dashboard/status', {
      params: { month, ...filters }
    }).then(r => r.data.data),
  })
}

// Usage
function DashboardPage() {
  const [month]   = useState('2026-06')
  const [filters] = useState({ emirate: 'Dubai' })

  const { data, isLoading } = useDashboard(month, filters)

  if (isLoading) return <Spinner />

  return (
    <>
      <StatCard label="Total Employees" value={data.employees.total_employees} />
      <StatCard label="Active Riders"   value={data.employees.active_employees} />
      <StatCard label="Total Bikes"     value={data.bikes.total_bikes} />
      <StatCard label="Net Profit"      value={`AED ${data.net_profit.muzn_net_profit}`} />
    </>
  )
}
```

---

## 12. All New Endpoints Summary

| Method | Endpoint | Auth | Description |
|---|---|---|---|
| GET | `/dashboard/overview?month=YYYY-MM&emirate=&zone=&platform=&employee_status=&bike_status=` | Bearer | Main dashboard with all sections |
| GET | `/dashboard/alerts?month=YYYY-MM` | Bearer | Expiry alerts only |
| GET | `/dashboard/status?month=YYYY-MM` | Bearer | Row count status checks |
| POST | `/employees/bulk-import` | Bearer | Import employees from Excel/CSV |
| POST | `/employees/bulk-update` | Bearer | Update employees from Excel/CSV |
| GET | `/employees/bulk-import/error-report/{token}` | Bearer | Download error CSV |
