# Aktivitetskalender — project instructions

## Scope and required context

For WordPress plugin work in this project, read these files before making changes:

1. `WORDPRESS-AGENT-INSTRUCTIONS.md` — shared engineering standards and task applicability.
2. `PROJECT-PROFILE.md` — project facts, contracts, known discrepancies, and verification requirements.

The revised WordPress instruction is the project standard in place of older WordPress engineering guidance inherited from a parent directory. Higher-priority instructions still apply. For unrelated tasks, skip WordPress-specific rules and the plugin profile.

The file `WORDPRESS-PROJECT-PROFILE.template.md`, if present, is only a reusable blank template. Use the populated `PROJECT-PROFILE.md` for this project. Recheck facts against the working revision; do not treat the profile as proof of compatibility or successful testing.

## GitHub workflow

- Repository: https://github.com/creatonas/ms-aktivitetskalender ; observed default branch: `main`.
- Confirm the working directory, repository, branch, and existing changes before editing. A loose local plugin file is not necessarily synchronized with GitHub. Preserve local changes and compare before copying or replacing code.
- Use a focused branch and pull request for repository changes unless the user requests a different workflow. Follow repository protections and required checks.
- Review the full diff, include only related files, and explain behavior, validation, and relevant risks in the pull request. Distinguish local tests from GitHub CI results.
- Check the current commit's required CI results before an authorized merge. Do not bypass failing checks or treat missing CI as a pass. If no CI is configured, report the relevant checks actually performed.
- Commit/push/PR creation, merging, release publication, and production deployment are separate operations. Stay within the user's authorized scope; creating a PR does not authorize its merge or deployment.
- `.cpanel.yml` contains a broad copy-based deployment configuration. Determine actual deployment behavior before a release; a documentation change is not authorization to run deployment.
- Do not commit credentials, customer data, local environment files, or personal machine configuration.

## Code Review Rules

- Flag changes that unintentionally break the `aktiviteter` post type, existing shortcodes, ACF keys/field names, or public calendar access.
- Check escaping at each HTML/attribute/JavaScript boundary, bounded calendar inputs, and behavior with missing ACF or invalid dates.
- Check date spans, month/year transitions, timezone behavior, and multiple shortcode instances when affected. Do not require an authenticated capability for deliberately public read-only output without a requirement to restrict it.
- Treat README/version/structure discrepancies as facts to investigate; do not assume listed modules are loaded or that a declared version has been tested.
