# Employee & Documents API — React Implementation Guide

**Base URL:** `https://api.muzndelivery.com/api`  
**Auth Header:** `Authorization: Bearer {token}`  
**JSON Header:** `Content-Type: application/json` (text fields ke liye)  
**File Header:** `Content-Type: multipart/form-data` (file upload ke liye)

---

## EMPLOYEE ENDPOINTS

### 1. List Employees
```
GET /employees
```

**Query Params (optional):**
| Param | Type | Values |
|-------|------|--------|
| `page` | number | 1, 2, 3... |
| `per_page` | number | default: 20 |
| `search` | string | name, employee_id, mobile, email |
| `status` | string | `active` / `inactive` |
| `work_emirate` | string | Dubai, Abu Dhabi... |
| `platform_name` | string | Talabat, Careem... |
| `wps_status` | string | `wps` / `no_wps` |
| `department` | string | Operations... |
| `job_title` | string | Delivery Rider... |

**Response:**
```json
{
  "success": true,
  "data": {
    "data": [
      {
        "id": 1,
        "employee_id": "EMP-0001",
        "name": "Ahmed Ali",
        "mobile": "+971501234567",
        "email": "ahmed@muzn.ae",
        "nationality": "Pakistani",
        "job_title": "Delivery Rider",
        "department": "Operations",
        "status": "active",
        "work_emirate": "Dubai",
        "zone": "Downtown",
        "platform_name": "Talabat",
        "platform_id": "TAL-001",
        "salary_amount": "2500.00",
        "salary_type": "monthly",
        "wps_status": "wps",
        "passport_number": "AB1234567",
        "passport_expiry": "2027-12-31",
        "passport_document_url": null,
        "emirates_id": "784-1990-1234567-1",
        "emirates_id_expiry": "2026-06-30",
        "visa_expiry": "2026-08-15",
        "visa_document_url": null,
        "labour_card_expiry": "2026-08-15",
        "driving_license": "DL-12345",
        "driving_license_expiry": "2027-03-20",
        "assigned_bike_id": null,
        "notes": null,
        "expiry_status": {
          "passport": { "expiry_date": "2027-12-31", "days_left": 568, "status": "ok" },
          "emirates_id": { "expiry_date": "2026-06-30", "days_left": 20, "status": "warning" },
          "visa": { "expiry_date": "2026-08-15", "days_left": 66, "status": "ok" },
          "labour_card": { "expiry_date": "2026-08-15", "days_left": 66, "status": "ok" },
          "driving_license": { "expiry_date": "2027-03-20", "days_left": 283, "status": "ok" }
        },
        "documents": [],
        "documents_by_type": {
          "passport": null,
          "emirates_id": null,
          "visa": null,
          "labour_card": null,
          "driving_license": null,
          "photo": null,
          "contract": null,
          "other": null
        },
        "created_at": "2026-06-10T08:00:00.000000Z",
        "updated_at": "2026-06-10T08:00:00.000000Z"
      }
    ],
    "current_page": 1,
    "last_page": 3,
    "per_page": 20,
    "total": 45
  }
}
```

**`expiry_status.status` values:**
| Value | Meaning |
|-------|---------|
| `ok` | 30+ days remaining |
| `warning` | 16–30 days remaining |
| `critical` | 1–15 days remaining |
| `expired` | Already expired |

---

### 2. Employee Stats
```
GET /employees/stats
```

**Response:**
```json
{
  "success": true,
  "data": {
    "total": 45,
    "active": 40,
    "inactive": 5,
    "wps": 30,
    "no_wps": 15,
    "by_emirate": { "Dubai": 30, "Abu Dhabi": 10, "Sharjah": 5 },
    "by_platform": { "Talabat": 25, "Careem": 15, "Noon": 5 }
  }
}
```

---

### 3. Expiry Alerts
```
GET /employees/expiry-alerts
GET /employees/expiry-alerts?days=15
```

**Response:**
```json
{
  "success": true,
  "data": {
    "expiring_in_30_days": 3,
    "expired": 1,
    "expiring_employees": [
      {
        "id": 1,
        "employee_id": "EMP-0001",
        "name": "Ahmed Ali",
        "expiry": {
          "emirates_id": { "expiry_date": "2026-06-20", "days_left": 10, "status": "critical" }
        }
      }
    ],
    "expired_employees": []
  }
}
```

---

### 4. Create Employee
```
POST /employees
Content-Type: application/json
```

**Request Body:**
```json
{
  "name": "Ahmed Ali",
  "mobile": "+971501234567",
  "email": "ahmed@muzn.ae",
  "nationality": "Pakistani",
  "job_title": "Delivery Rider",
  "department": "Operations",
  "status": "active",
  "work_emirate": "Dubai",
  "zone": "Downtown",
  "platform_name": "Talabat",
  "platform_id": "TAL-001",
  "salary_amount": 2500,
  "salary_type": "monthly",
  "wps_status": "wps",
  "passport_number": "AB1234567",
  "passport_expiry": "2027-12-31",
  "emirates_id": "784-1990-1234567-1",
  "emirates_id_expiry": "2026-06-30",
  "visa_expiry": "2026-08-15",
  "labour_card_expiry": "2026-08-15",
  "driving_license": "DL-12345",
  "driving_license_expiry": "2027-03-20",
  "notes": "Optional notes here"
}
```

**Required fields:** sirf `name`  
**Optional fields:** baaki sab  
**`wps_status` values:** `wps` / `no_wps` (default: `no_wps`)  
**`status` values:** `active` / `inactive` (default: `active`)  
**Date format:** `YYYY-MM-DD`

**Response (201):**
```json
{
  "success": true,
  "message": "Employee created successfully",
  "data": {
    "id": 5,
    "employee_id": "EMP-0005",
    "name": "Ahmed Ali",
    "..."  
  }
}
```

> ⚠️ Employee create hone ke baad `id` save karo — documents upload ke liye chahiye.

---

### 5. Get Single Employee
```
GET /employees/{id}
```

**Response:** Same as list item but with full `documents` and `documents_by_type` populated.

---

### 6. Update Employee
```
PUT /employees/{id}
Content-Type: application/json
```

Sirf woh fields bhejo jo update karni hain:

```json
{
  "salary_amount": 2800,
  "wps_status": "no_wps",
  "visa_expiry": "2027-08-15",
  "status": "inactive"
}
```

**Response (200):** Updated employee object.

---

### 7. Delete Employee
```
DELETE /employees/{id}
```

Soft delete hota hai — record DB mein rehta hai, sirf hidden ho jata hai.

---

---

## DOCUMENT ENDPOINTS

> Documents `employee_documents` table mein store hoti hain.  
> Har document type ki sirf **ek** file hoti hai per employee — dobara upload karo toh purani replace hoti hai.

### Document Types
```
passport        → Passport
emirates_id     → Emirates ID
visa            → Visa
labour_card     → Labour Card / Work Permit
driving_license → Driving License
photo           → Profile Photo
contract        → Employment Contract
other           → Other Documents
```

---

### 8. Upload / Replace Document by Type
```
POST /employees/{id}/documents/{type}/upload
Content-Type: multipart/form-data
```

**Form Fields:**
| Field | Type | Required |
|-------|------|----------|
| `file` | File | ✅ |
| `expiry_date` | string (YYYY-MM-DD) | optional |

**Allowed file types:** `jpg`, `jpeg`, `png`, `pdf`  
**Max size:** 5MB

**Examples:**
```
POST /employees/5/documents/passport/upload
POST /employees/5/documents/emirates_id/upload
POST /employees/5/documents/visa/upload
POST /employees/5/documents/labour_card/upload
POST /employees/5/documents/driving_license/upload
POST /employees/5/documents/photo/upload
POST /employees/5/documents/contract/upload
POST /employees/5/documents/other/upload
```

**Response (201):**
```json
{
  "success": true,
  "message": "Passport uploaded successfully",
  "data": {
    "id": 12,
    "document_type": "passport",
    "original_name": "ahmed_passport.pdf",
    "file_url": "https://api.muzndelivery.com/storage/employees/5/docs/ahmed_passport.pdf",
    "file_size": "1.2 MB",
    "expiry_date": "2027-12-31",
    "uploaded_at": "2026-06-10T09:30:00.000000Z"
  }
}
```

---

### 9. Get All Documents of Employee
```
GET /employees/{id}/documents
```

**Response:**
```json
{
  "success": true,
  "data": {
    "documents_by_type": {
      "passport": {
        "id": 12,
        "document_type": "passport",
        "original_name": "ahmed_passport.pdf",
        "file_url": "https://api.muzndelivery.com/storage/employees/5/docs/ahmed_passport.pdf",
        "file_size": "1.2 MB",
        "expiry_date": "2027-12-31",
        "uploaded_at": "2026-06-10T09:30:00.000000Z"
      },
      "emirates_id": {
        "id": 13,
        "document_type": "emirates_id",
        "original_name": "ahmed_eid.jpg",
        "file_url": "https://api.muzndelivery.com/storage/employees/5/docs/ahmed_eid.jpg",
        "file_size": "320 KB",
        "expiry_date": "2026-06-30",
        "uploaded_at": "2026-06-10T09:31:00.000000Z"
      },
      "visa": null,
      "labour_card": null,
      "driving_license": null,
      "photo": null,
      "contract": null,
      "other": null
    },
    "all_documents": [
      { "id": 12, "document_type": "passport", "file_url": "...", "..." },
      { "id": 13, "document_type": "emirates_id", "file_url": "...", "..." }
    ]
  }
}
```

> `documents_by_type` mein `null` matlab wo document abhi upload nahi hua.

---

### 10. Delete Document
```
DELETE /employees/{id}/documents/{documentId}
```

`documentId` = document ka `id` field (type nahi).

**Response:**
```json
{
  "success": true,
  "message": "Document deleted"
}
```

---

---

## REACT IMPLEMENTATION FLOW

### Employee Create Form
```javascript
// Step 1: Create employee with JSON
const response = await fetch(`${BASE_URL}/employees`, {
  method: 'POST',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({
    name: 'Ahmed Ali',
    mobile: '+971501234567',
    wps_status: 'wps',
    // ... baaki fields
  }),
});
const { data: employee } = await response.json();
const employeeId = employee.id;

// Step 2: Upload documents one by one
const uploadDoc = async (type, file, expiryDate = null) => {
  const formData = new FormData();
  formData.append('file', file);
  if (expiryDate) formData.append('expiry_date', expiryDate);

  return fetch(`${BASE_URL}/employees/${employeeId}/documents/${type}/upload`, {
    method: 'POST',
    headers: { 'Authorization': `Bearer ${token}` },
    body: formData,
  });
};

if (passportFile)   await uploadDoc('passport', passportFile, '2027-12-31');
if (emiratesFile)   await uploadDoc('emirates_id', emiratesFile, '2026-06-30');
if (visaFile)       await uploadDoc('visa', visaFile, '2026-08-15');
if (labourFile)     await uploadDoc('labour_card', labourFile);
if (licenseFile)    await uploadDoc('driving_license', licenseFile);
```

### Employee Edit Form — Load Existing Documents
```javascript
// GET employee — documents_by_type already included
const res = await fetch(`${BASE_URL}/employees/${id}`, {
  headers: { 'Authorization': `Bearer ${token}`, 'Accept': 'application/json' },
});
const { data: employee } = await res.json();

// Show existing documents
const passport = employee.documents_by_type.passport;
if (passport) {
  console.log(passport.file_url);     // show preview link
  console.log(passport.expiry_date);  // show expiry
}
```

### Employee Update
```javascript
// Only send changed fields
await fetch(`${BASE_URL}/employees/${id}`, {
  method: 'PUT',
  headers: {
    'Authorization': `Bearer ${token}`,
    'Content-Type': 'application/json',
    'Accept': 'application/json',
  },
  body: JSON.stringify({ salary_amount: 2800, wps_status: 'no_wps' }),
});
```

---

## ERROR RESPONSES

**Validation Error (422):**
```json
{
  "success": false,
  "message": "Validation failed.",
  "errors": {
    "name": ["The name field is required."],
    "passport_expiry": ["The passport expiry is not a valid date."]
  }
}
```

**Not Found (404):**
```json
{
  "success": false,
  "message": "Employee not found."
}
```

**Invalid Document Type (422):**
```json
{
  "success": false,
  "message": "Invalid document type. Allowed: passport, emirates_id, visa, labour_card, driving_license, photo, contract, other"
}
```

**Unauthenticated (401):**
```json
{
  "success": false,
  "message": "Unauthenticated. Please login."
}
```

**No Permission (403):**
```json
{
  "success": false,
  "message": "Access denied. You do not have permission to perform this action."
}
```
