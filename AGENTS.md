# Work Activity Project Rules & Specification Reference

All development and implementation in this repository **MUST ALWAYS BE BASED ON** the specifications in [Work Activity.pdf](file:///c:/Codings/Laravel/Work-Activity/dummypdf/Work Activity.pdf).

---

## ⚠️ Contradiction & Deviation Protocol

If the user requests any feature, flow, design, or architecture that contradicts or deviates from the specifications in `Work Activity.pdf`:
1. **Never implement the contradicting feature immediately.**
2. **Respond with a clear confirmation prompt**:
   `"Contradict from module: <Detailed context of what contradicts the PDF specification>"`
3. **Double Confirmation Requirement**: A **minimum of 2 consecutive confirmation chats** from the user is required before implementing any contradicting requirement.

---

## Summary of Core Specifications from Module (`Work Activity.pdf`)

### 1. Core Principles
- **Purpose**: Internal web application for recording, monitoring, and documenting employee work activity (`https://work.rodja.studio`).
- **Language**: Always use **Bahasa Indonesia** for the user interface, labels, buttons, messages, validation errors, and empty states.
- **NOT an attendance / clock-in / presence app**.
- **Orientation**: `Activity → Result → Impact → Constraint`.
- **Priorities**:
  - `Correct business logic > UI appearance`
  - `Security > Convenience`
  - `Maintainability > unnecessary complexity`

### 2. Roles & Permissions (RBAC)
- **Employee**:
  - View own activities, create activity, edit own activity, view monthly historical activity, view detail.
  - CANNOT view other employees' activities.
- **Supervisor / Manager**:
  - All Employee permissions.
  - View direct subordinates' activities via dropdown (Default dropdown: `Myself`).
  - View monthly activities and details of subordinates.
- **Administrator**:
  - User Management, Role Management, Division Management, Position Management, Supervisor Assignment, Account Activation/Deactivation.
- **Management / Director (Optional Future Role)**:
  - Can view all divisions, employees, supervisors, organization-wide monthly activities.
- **Rule**: Implement RBAC (e.g. `activity.create`, `activity.read.own`, `activity.read.subordinate`, `activity.read.division`, `activity.read.all`, etc.). Avoid hardcoded `if (role == 'manager')`.

### 3. Hierarchy & Data Structure
- `users`: `id`, `username`, `password`, `full_name`, `position_id`, `division_id`, `supervisor_id` (FK to `users.id`), `role_id`, `status` (`Active`/`Inactive`), `last_login_at`, `created_at`, `updated_at`.
- `roles`: `id`, `name`, `description`, timestamps.
- `permissions`: `id`, `name`, `description`.
- `role_permissions`: `role_id`, `permission_id`.
- `divisions`: `id`, `name`, `head_user_id`, `status`, timestamps.
- `positions`: `id`, `division_id`, `name`, `level`, `status`, timestamps.
- `activities`: `id`, `user_id`, `activity_date`, `activity`, `requested_by`, `result`, `constraint`, `status`, `created_by`, `updated_by`, `deleted_by`, `created_at`, `updated_at`, `deleted_at`.
- `activity_request_sources` (future optional).

### 4. Activity Handling & Dashboard
- **Main Dashboard**: Default loads current month (e.g. August 2026).
- **Navigation**: Month navigation (`< Prev Month | Current Month | Next Month >` or Month Selector).
- **Timeline**: Scrollable monthly timeline, ordered newest first.
- **Add / Edit Activity**: Must be inside a **Modal Dialog** (Full screen modal on mobile). NOT a new page.
  - Activity Date (Date picker, default today).
  - Activity (Rich Text Editor: TinyMCE/CKEditor).
  - Requested By (Dropdown + text field for "Other").
  - Result / Outcome (Rich Text Editor).
  - Constraint / Issue (Rich Text Editor, optional/nullable).
- **Activity Status**: `Draft`, `Submitted` (default after save: `Submitted`).
- **Deletion**: Soft delete (`deleted_at`, `deleted_by`) with confirmation dialog.
- **Audit Trail**: `created_by`, `created_at`, `updated_by`, `updated_at`, `deleted_by`, `deleted_at`.
- **Search**: Search activities (by activity, result, constraint, requested by) scoped to selected employee and selected month.
- **Empty State**: `"No activity recorded for this month."` + `[+ Add First Activity]` button.

### 5. Security & Backend Architecture
- Passwords must be hashed (never plain-text).
- Server-side authorization on every request (Prevent IDOR: Employee A cannot access Employee B's activity even if ID is known).
- CSRF protection, session expiration, login rate limiting, input validation, and HTML sanitization for WYSIWYG content.
- Clean Laravel architecture: Controller, Service, Model, Policy/Authorization, Request Validation.
