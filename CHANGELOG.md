# MUZN ERP API — Changelog

---

## v2 — 2026-06-18

### 1. Settings — New type aliases for dashboard dropdowns

**Problem:** Frontend was calling `GET /settings/emirate` and `GET /settings/platform` but these types did not exist — so the Emirate and Platform dropdowns were hidden on the dashboard.

**Fix:** Backend now accepts `emirate` and `platform` as valid type keys (aliases).

| Old (still works) | New alias (use this for dashboard) |
|-------------------|------------------------------------|
| `GET /settings/work_emirate` | `GET /settings/emirate` |
| `GET /settings/platform_name` | `GET /settings/platform` |

`GET /settings/types` now includes `emirate` and `platform` in the returned array — so `useSettingsOptions()` will find them and show the dropdowns.

**No change in response shape** — same `{ id, value, label, sort_order }` per row.

---

### 2. Dashboard — New `employee_id` filter

Three dashboard endpoints now accept `?employee_id=<integer>`:

```
GET /dashboard/overview?employee_id=42
GET /dashboard/status?employee_id=42
GET /dashboard/alerts?employee_id=42
```

- `employee_id` is the employee's numeric primary key (`id`), **not** the string `EMP-0001`.
- When set, all sections are scoped to that one rider:
  - `employees` stats → only that employee
  - `bikes` stats → only their assigned bike (`current_rider_id`)
  - `salary_summary`, `loans_fines_salik`, `net_profit` → only their payroll/fines
- Can be combined with other filters: `?employee_id=42&month=2026-05`

**How to use on frontend:**

```js
// 1. Fetch active employees for the picker dropdown
GET /employees?status=active&per_page=200

// 2. Map to options
const options = employees.map(e => ({ value: e.id, label: e.name }))

// 3. Send selected id to dashboard
GET /dashboard/overview?employee_id=42
```

---

### 3. Bulk Import — `mobile` is now required

```
POST /employees/bulk-import
```

Previously only `name` was required. **`mobile` is now also required.**

Rows missing a mobile number will fail with:
```
"Missing mobile number"
```

**Update your import template** to include a `mobile` column and mark it as required in your UI.

Required columns (updated): `name`, `mobile`
