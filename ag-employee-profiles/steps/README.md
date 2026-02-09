# AG Employee Profiles — Progress Notes (Steps 1–2)

This document summarises what has been implemented so far for the **/team/{uuid}** employee profile routing and UUID storage.

## Step 1 — CPT + `/team/{uuid}` routing ✅

**Date finished:** Not recorded

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

**Date finished:** Not recorded

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

## Step 3 — Fix `/team/{uuid}` routing + template selection ✅

**Date finished:** 9 Feb 2026

### Files

- [public/class-ag-employee-profiles-public.php](../public/class-ag-employee-profiles-public.php)
- [includes/class-ag-employee-profiles.php](../includes/class-ag-employee-profiles.php)
- [includes/class-ag-employee-profiles-activator.php](../includes/class-ag-employee-profiles-activator.php)
- [includes/class-ag-employee-profiles-deactivator.php](../includes/class-ag-employee-profiles-deactivator.php)

### What was done (plain English)

- **Make the rewrite rule reliable and flush it on activation**
  - On plugin activation, the Employee Profile CPT and the `/team/{uuid}` rule are registered and permalinks are flushed.
  - On deactivation, permalinks are flushed again to remove the custom rule.

- **Ensure WordPress recognizes the UUID in the URL**
  - A rewrite tag is added for `employee_uuid` and the rewrite rule now passes both `post_type=employee_profile` and the UUID into the query.
  - A request-level fallback was added so `/team/{uuid}` still works even if rewrite rules are bypassed (it inspects the request path and injects `employee_uuid`).

- **Force a real single post query when a UUID matches**
  - The UUID lookup runs very early and, when a match is found, the main query is converted into a single `employee_profile` query by ID.
  - The query is explicitly pushed out of archive/home mode and given a real `queried_object` so WordPress chooses the single template instead of the archive.

---

## Step 4 — vCard download endpoint + button ✅

**Date finished:** 9 Feb 2026

### Files

- [public/class-ag-employee-profiles-public.php](../public/class-ag-employee-profiles-public.php)
- [includes/class-ag-employee-profiles.php](../includes/class-ag-employee-profiles.php)
- [templates/single-employee_profile.php](../templates/single-employee_profile.php)

### What was done (plain English)

- **Added a vCard download handler**
  - A public AJAX endpoint builds a vCard for the employee profile found by UUID.
  - The handler validates the UUID, looks up the profile by `_ag_employee_uuid`, and returns a `.vcf` file.

- **Hooked the AJAX action for logged-in and public users**
  - The action is registered for both `wp_ajax_ag_employee_vcard` and `wp_ajax_nopriv_ag_employee_vcard` so it works for everyone.

- **Added a download button on the single profile template**
  - The template now builds a URL like `admin-ajax.php?action=ag_employee_vcard&uuid=...` and renders a "Download vCard" button.

- **Escaped vCard fields safely**
  - A small helper ensures commas, semicolons, and line breaks are escaped to keep the vCard valid.

---
