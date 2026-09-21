# Aktivitetskalender — project profile

## Evidence and scope

Repository: https://github.com/creatonas/ms-aktivitetskalender

Inspected remote revision: `ed25079fb4794b8e8265524520ff76bda609dee5` on `main`, on 2026-09-21. This profile records source inspection, not a runtime or security audit. Recheck relevant facts against the revision being changed.

The plugin manages WordPress activities and displays a calendar, list, and a view switcher. The present instruction setup changes documentation only; it does not change plugin behavior, reconcile code versions, or authorize deployment.

## Confirmed repository structure

- Main plugin file: `ms-activities.php`; header name `Aktivitetskalender`; author `Creato Design AS`; header version `4.7` in the inspected remote revision.
- Additional files: `includes/helpers.php`, `assets/css/style.css`, `assets/js/calendar.js`, README, and `.cpanel.yml`.
- The inspected main file implements registration, ACF fields, rendering, shortcodes, AJAX, inline CSS, and inline JavaScript itself. It does not include the helper file or enqueue the separate asset files. Trace the real execution path before changing a file merely because its name suggests ownership of a feature.
- No Composer/npm manifest, automated test suite, coding-standard configuration, or GitHub Actions workflow was present in the inspected remote tree.

## Known discrepancies and local context

- README describes version `5.0`, a different main filename, and modules that are absent from the inspected tree. Treat that structure as unverified documentation, not the actual runtime architecture.
- The loose local file `ms-activities/ms-activities.php` in the enclosing activity-calendar workspace has header version `5.2` and differs from remote `main`. It is not a Git checkout. A larger version number does not establish that it is the correct release or a descendant of the remote version.
- Preserve the local copy and compare it with the chosen remote revision before any synchronization. Do not overwrite either version as part of documentation setup.

## Contracts to preserve

- Post type and rewrite slug: `aktiviteter`.
- Shortcodes: `[ms_calendar]`, `[ms_activities]`, `[ms_activities_toggle]`.
- AJAX action: `ms_load_calendar`, registered for logged-in and logged-out visitors. It currently reads month/year from `m` and `y` and returns rendered calendar HTML.
- ACF group key: `group_ms_activities`.
- ACF field key/name pairs: `f1` / `date_from`, `f2` / `date_to`, `f3` / `status`, `f4` / `link`.
- Configured date return format: `Y-m-d`. Do not assume this is identical to raw database storage.
- Status values: `ledig`, `fullt`, `lukket`; helper maps legacy `booket` to `fullt`.
- Existing identifiers use `ms_` / `ms-`; some presentation depends on `grve-*` and `et-icon-calendar` classes. The actual installed theme is unconfirmed.
- Content uses WordPress posts and ACF fields. No custom table or migration mechanism was observed in the inspected main file.

## Supported environment — not yet verified

- README states WordPress `5.0+` and ACF Pro. These are documented requirements, not a tested compatibility matrix.
- Supported/tested PHP, database, WordPress, and ACF versions: unknown.
- Main header does not declare `Requires PHP`, `Requires at least`, or a text domain. Do not invent supported versions or silently adopt newer APIs.
- Multisite, multilingual plugins, caching, page builders, browser support, site timezone, and production data volume: unknown.
- ACF field registration has an availability check, but rendering calls `get_field()`. Do not infer that operation without ACF is safe.

## Verification for future code changes

- No project-provided test/build command was found. Do not report CI or runtime tests as passing without execution.
- When a suitable PHP runtime is available, syntax-check every changed PHP file. A candidate command from the plugin root is `rg --files -g '*.php' -0 | xargs -0 -n 1 php -l`; verify the runtime against the intended PHP support range.
- For behavior changes, use an isolated WordPress environment with the intended ACF version and representative activities.
- Check affected calendar/list/toggle views, empty data, single-day and multi-day activities, month/year boundaries, missing/invalid dates, links, status display, AJAX failures, and mobile/keyboard behavior as relevant.
- Test malformed/out-of-range AJAX values, output escaping, guest access, and missing ACF when those paths change. Public read-only output does not by itself require privileged access.
- Check multiple shortcode instances and theme interactions when changing JavaScript, IDs, CSS, or rendering.
- For releases, verify install/activation, upgrade and data preservation, actual package contents, the agreed compatibility matrix, and deployment behavior.

## Operations and release

- `.cpanel.yml` defines a recursive copy of non-hidden root entries to a server path. Whether it is automatic, which branch triggers it, and whether its destination is a live WordPress plugin directory are unconfirmed.
- That broad copy may also include documentation. Establish the intended package/deployment file set before publishing operational or private information in the repository.
- Production/staging access, backup/restore procedure, release approval, versioning convention, and operational owner: unknown.
- Do not change the plugin version for this instruction/documentation-only setup.
- No production cron, email integration, telemetry, or uninstall retention policy was identified in the inspected main file. Reassess if introduced by future work.

## Maintenance

Update this profile when relevant project facts change. Record real commands and verified compatibility as they become available. Keep credentials and private deployment details outside the repository.
