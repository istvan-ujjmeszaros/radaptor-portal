# Radaptor Portal Notes

The canonical repo workflow rules live in [`AGENTS.md`](./AGENTS.md). This file is the short
Claude-facing version for future sessions. Treat `AGENTS.md` as source of truth.

## Project Overview

`radaptor-portal` is the public-facing Radaptor tech-demo app. Current public surface:
- `/`
- `/comparison/`
- `/request-access/`
- `/roadmap/`

Public page structure source of truth: `app/seeds/specs/portal-public.json` with content fragments
under `app/seeds/specs/content/`. If a page uses `settings.content_file`, that file wins.

## Package Modes

- Registry-first is the committed state:
  - manifest: `radaptor.json`
  - lockfile: `radaptor.lock.json`
- Maintainer-local first-party package development uses gitignored:
  - `radaptor.local.json`
  - `radaptor.local.lock.json`
  - Runtime: `../bin/docker-compose-packages-dev.sh radaptor-portal ...`
- `packages/registry/...` and `packages-dev/...` must never be connected with symlinks.
- `radaptor.local.json` without the package-dev compose override is an invalid runtime state and
  must fail hard.

## Editable Package Repos

First-party editable repos live at workspace root, mounted into the package-dev runtime:
- `/apps/_RADAPTOR/packages-dev/core/framework` → `/workspace/packages-dev/core/framework`
- `/apps/_RADAPTOR/packages-dev/core/cms` → `/workspace/packages-dev/core/cms`
- `/apps/_RADAPTOR/packages-dev/themes/portal-admin` → `/workspace/packages-dev/themes/portal-admin`
- `/apps/_RADAPTOR/packages-dev/themes/so-admin` → `/workspace/packages-dev/themes/so-admin`

## Supported Runtime Rule

- All PHP/Composer/Radaptor CLI work runs inside the `php` container. No host PHP/Composer/CLI.
- Default bring-up: `docker compose -f docker-compose-dev.yml up -d --build`. Do not bring the app
  up with a handpicked subset unless a task explicitly needs it.
- Queue worker service: `swoole-queue-worker`. Swoole itself is built into the `php` image; there
  is no separate `swoole` service.
- App-local transient QA outputs (`playwright-report/`, `test-results/`, proof clones, scratch
  dirs) belong under `tmp/`, not at repo root.

## Runtime Response, I18n, HTMX

- New or touched visible runtime messages must use i18n keys through `t()`.
- Use `./radaptor.sh i18n:scan-hardcoded --json` to find hardcoded UI text in `.php`, `.blade.php`,
  `.twig`. Warnings by default; `i18n:doctor` surfaces them as `hardcoded_ui`.
- API, JSON, HTMX, MCP, CLI-web flows must return structured response data or headers instead of
  session messages.
- Use `Request::wantsNonHtmlResponse()` for response-family detection. Do not hand-read
  `HTTP_ACCEPT`, `HTTP_X_REQUESTED_WITH`, `HTTP_HX_REQUEST`, or add `ajax=1` fallbacks.
- For HTMX admin flows, use header-detected server-rendered fragments and stable swap targets. If
  an OOB swap inserts new `hx-*` markup outside the original target, verify the current HTMX
  runtime processes it; if not, explicitly process the inserted root and cover with a browser smoke.

## Repo Baseline

- `php-consumer-app` profile baseline files must stay committed.
- Worktree must have `core.hooksPath=.githooks`.
- PHP-heavy repo, so keep `.php-cs-fixer.php` and `phpstan.neon`.
- The local-override guard is part of the baseline and must stay enabled.

## Verification

- `bin/check-repo-baseline.sh`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpunit`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpstan analyze`
- `docker compose -f docker-compose-dev.yml exec -T php bash -lc 'cd /app && ./php-cs-fixer.sh --config=.php-cs-fixer.php'`
- Browser smoke: `/`, `/comparison/`, `/request-access/`, `/roadmap/`, `/login.html`, `/admin/index.html`

## Commit & PR

- Do not commit without explicit maintainer approval.
- After opening or updating a GitHub PR, add a PR comment containing exactly `@codex review`.
- Thread-aware review reads; resolve threads that pushed commits address; never resolve to clear
  the list. Re-check unresolved count before requesting another review, merging, or publishing.
- After publishing a first-party package, update dependent consumer lockfiles in separate commits.
