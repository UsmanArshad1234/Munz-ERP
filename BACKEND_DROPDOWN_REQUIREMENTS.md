# Backend Requirements — Dashboard Filter Dropdowns

The dashboard filters no longer contain any hardcoded option lists. Every
location/business dropdown is now driven by the **Settings API**, and the two
status dropdowns use the fixed API enums. This document lists what the backend
must provide so each dropdown shows data.

The frontend reads dynamic options via `useSettingsOptions(type)`
([src/hooks/useSettings.js](../src/hooks/useSettings.js)), which:
1. Calls `GET /settings/types` and only proceeds if `type` is in the returned list.
2. Calls `GET /settings/{type}` and maps each row to `{ value, label }`.
3. If the type is missing or returns no rows, the filter is **hidden** on the dashboard.

So a dropdown is empty/hidden unless BOTH conditions below are met for its type.

---

## 1. Settings-driven filters (ACTION REQUIRED)

For each type, `GET /settings/types` must include the exact key, and
`GET /settings/{type}` must return rows shaped `{ value, label }`
(or at least `value`; `label` falls back to `value`).

| Dashboard filter | Settings type key | `/settings/types` includes it? | Rows seeded in `/settings/{type}`? | Suggested values |
|------------------|-------------------|-------------------------------|------------------------------------|------------------|
| **Emirate**  | `emirate`  | **must add** | **must seed** | Dubai, Abu Dhabi, Sharjah, Ajman, Fujairah, Ras Al Khaimah, Umm Al Quwain |
| **Platform** | `platform` | **must add** | **must seed** | Talabat, Careem, Noon, InDrive, Other |
| **Zone**     | `zone`     | **must add** | **must seed** | (your operational zones, e.g. Deira, Bur Dubai, Marina, JLT …) |

**Notes for backend team**
- The `value` is what gets sent to `GET /dashboard/overview` as the filter
  param (`emirate`, `platform`, `zone`). It must match the value actually stored
  on Employee/Motorbike records, otherwise filtering returns nothing.
  - e.g. if employees store `work_emirate = "Dubai"`, the `emirate` setting's
    `value` must be `"Dubai"` (case-sensitive).
- Each row should ideally include `sort_order` so the dropdown ordering is stable.
- Until a type is added to `/settings/types` AND has rows, that filter is hidden
  on the dashboard (by design — no empty dropdowns).

---

## 2. Fixed enum filters (NO backend action needed)

These are part of the documented API contract and come from
[src/constants/options.js](../src/constants/options.js). They are **not**
configurable via Settings and require no backend change — listed here only so
the values the dashboard sends are explicit.

| Dashboard filter | Param sent to `/dashboard/overview` | Allowed values |
|------------------|-------------------------------------|----------------|
| **Emp. Status**  | `employee_status` | `active`, `inactive` |
| **Bike Status**  | `bike_status`     | `available`, `assigned`, `maintenance` |

> If the backend actually accepts a **different** set of `bike_status` values for
> the dashboard filter (e.g. `inactive` instead of `maintenance`), tell us and
> we'll align `BIKE_STATUS` in `constants/options.js`.

---

## 3. Still-open backend gap (from earlier review)

- **Employee / Rider filter (requirement #12):** `GET /dashboard/overview` has no
  `employee_id` (and/or `rider` / `type`) parameter yet. The dashboard cannot add
  a per-rider filter until the endpoint accepts it.
- **Bulk import `mobile`:** confirm whether `mobile` is required on employee bulk
  import, so the import template/validation can match.
