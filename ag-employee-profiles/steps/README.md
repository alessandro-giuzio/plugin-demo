# AG Employee Profiles — Progress Notes (Steps 1–2)

This document summarises what has been implemented so far for the **/team/{uuid}** employee profile routing and UUID storage.

---

## Step 1 — CPT + `/team/{uuid}` routing ✅

### Files

- [admin/class-ag-employee-profiles-admin.php](../admin/class-ag-employee-profiles-admin.php)
- [public/class-ag-employee-profiles-public.php](../public/class-ag-employee-profiles-public.php)
- [includes/class-ag-employee-profiles.php](../includes/class-ag-employee-profiles.php)

### What was done

- **CPT rewrite base updated to `/team`**
  - The Employee Profile CPT uses `rewrite => ['slug' => 'team']` so profiles live under the `/team` URL base.

- **Routing added for `/team/{uuid}`**
  - `init`: adds a rewrite rule that matches UUID format.
  - `query_vars`: registers `employee_uuid` so WordPress accepts it.
  - `pre_get_posts`: if `employee_uuid` is present, resolves it to the matching `employee_profile` by meta key **`_ag_employee_uuid`** and converts the main query into a proper single post query.

- **Hook wiring**
  - In the core plugin class, the public hooks were registered so the routing logic runs:
    - `init`
    - `query_vars`
    - `pre_get_posts`

---

## Step 2 — UUID generation + storage ✅

### File

- [admin/class-ag-employee-profiles-admin.php](../admin/class-ag-employee-profiles-admin.php)

### What was done

- **UUID generated once on save**
  - On `save_post`, if UUID meta is missing, generate via `wp_generate_uuid4()` and store in post meta key:
    - **`_ag_employee_uuid`**
  - UUID is **not regenerated** on subsequent updates.

- **UUID displayed in wp-admin**
  - Added a **read-only “Profile UUID”** field in the existing “Employee Profile Details” metabox.
  - This lets editors copy the UUID for testing `/team/{uuid}` directly from wp-admin.

---
