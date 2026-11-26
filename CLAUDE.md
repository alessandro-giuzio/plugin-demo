# CLAUDE.md

## Stack
Two custom WordPress plugins (PHP 7.2+, no Composer) in a Local by Flywheel site. This repo is the `wp-content/plugins` directory.

## Parts
- `ag-employee-profiles/`: employee profile CPT, vCard download, AJAX filter, block editor panel (built with `@wordpress/scripts`)
- `expiration-manager/`: expiry date for posts and pages, with a daily cron

## Where things live
- Bootstrap: `<plugin>/<plugin>.php`
- Classes and hook loader: `<plugin>/includes` (register hooks through `class-<plugin>-loader.php`)
- Admin screens: `<plugin>/admin`
- Front end: `<plugin>/public` (its `js/` and `css/` are hand-written)
- Single profile template: `ag-employee-profiles/templates`
- Editor panel source: `ag-employee-profiles/src`
- Generated, do not edit: `ag-employee-profiles/build` (built from `src/`)
- Progress notes: `ag-employee-profiles/steps/README.md`

## Hard rules

### All plugins
- Edit only inside the plugin named in the task. Never WordPress core, other plugins, themes or wp-config.php.
- `hitzordu` is a symlink to a separate repo. Do not edit it from here.
- Escape on output (esc_html, esc_attr, esc_url, wp_kses) and sanitize on input.
- Every form, AJAX handler and REST route checks a nonce or permission callback and a capability. Public `nopriv` handlers check a nonce and expose only published data.
- Every query with variables goes through `$wpdb->prepare()`.
- Every PHP file exits on direct access: check `ABSPATH` or `WPINC` (`WP_UNINSTALL_PLUGIN` in `uninstall.php`). Keep existing `WPINC` guards.
- Never run WP-CLI against a live site. Local copy only.
- Never read or edit .env files.
- Never push, deploy or release without asking.

### ag-employee-profiles/
- Prefix functions, hooks and options with `ag_employee`, constants with `AG_EMPLOYEE_PROFILES_`. Existing post meta uses `_employee_*` and `_ag_employee_uuid`.
- User-facing strings go through `__()` / `_e()` with the text domain `ag-employee-profiles`.
- Changing the CPT or rewrite rules needs a rewrite flush.

### expiration-manager/
- Prefix functions, hooks and options with `expiration_manager`, constants with `EXPIRATION_MANAGER_`, post meta with `_em_`.
- User-facing strings go through `__()` / `_e()` with the text domain `expiration-manager`.

## How to work
- At session start, read STATE.md if it exists.
- Before coding, restate the task: goal, constraints, done when.
- Comments that record development notes start with `AG:`.
- Done means `./check.sh` prints `GATE: PASS`. Paste the output.
- Same error twice: stop and write what you tried to STATE.md.
