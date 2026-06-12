# Radaptor Portal — Agent Rules

## Project Overview

This is `radaptor-portal`, the public-facing Radaptor tech-demo application.
Its public surface is currently:
- `/`
- `/comparison/`
- `/request-access/`
- `/roadmap/`

Committed state is registry-first:
- `radaptor.json`
- `radaptor.lock.json`

Maintainer-local first-party package overrides are gitignored:
- `radaptor.local.json`
- `radaptor.local.lock.json`

## Public Surface Source Of Truth

- Public page structure lives in `app/seeds/specs/portal-public.json`.
- File-backed content fragments live under `app/seeds/specs/content/`.
- If a page uses `settings.content_file`, that file is the source of truth.
- The roadmap page currently uses file-backed content on purpose; it is not admin-CRUD managed.

## First-Party Package Workflow

- Editable first-party repos live in the app-local, gitignored `packages-dev/` directory.
- Clone first-party repos from their GitHub SSH origins:
  - `packages-dev/core/framework`: `git@github.com:istvan-ujjmeszaros/radaptor-framework.git`
  - `packages-dev/core/cms`: `git@github.com:istvan-ujjmeszaros/radaptor-cms.git`
  - `packages-dev/themes/portal-admin`: `git@github.com:istvan-ujjmeszaros/radaptor-portal-admin.git`
  - `packages-dev/themes/so-admin`: `git@github.com:istvan-ujjmeszaros/radaptor-so-admin.git`
- Inside Docker they are available under `/workspace/packages-dev/...`, but only when the package-dev compose override is active.
- The committed Portal manifest must stay registry-first.
- Maintainer-local dev mode is enabled only through `radaptor.local.json`, not by editing committed `radaptor.json`.
- `packages/registry/...` and `packages-dev/...` must never be connected with symlinks.
- `radaptor.local.json` without the package-dev compose override is an invalid runtime state and must fail hard.
- Host-side workflow is Git-only. Hooks and helper scripts must dispatch every non-Git check into the supported container; never require host PHP, Composer, Python, php-cs-fixer, or Radaptor CLI.
- App-local transient QA outputs belong under `tmp/`. Do not leave `playwright-report/`, `test-results/`, proof clones, restore sandboxes, or scratch verification directories at repo root.
- The primary review gate is a local Codex CLI review worker: after opening or updating a GitHub PR, run `codex exec review --base origin/main` (or `--commit <sha>` for follow-up passes) on the PR branch before merging, publishing, releasing, or treating the PR as approved dependency input. The worker is review-only: no edits, commits, pushes, or merges.
- Claude-driven sessions should run Claude's internal review agents (for example `/code-review`) on the branch before the Codex pass, so obvious findings are fixed before the primary gate runs.
- Review results must be visible on the PR, using inline comments for line-tied findings when possible and a top-level PR comment otherwise. A no-findings result must also be posted for the reviewed HEAD.
- A GitHub-hosted `@codex review` comment is an optional extra signal when account quota allows; it is not required for merge. If the local Codex CLI is unavailable or fails to produce a usable result, fall back to GitHub `@codex review` and document the reason on the PR.
- If the maintainer asks for Claude review, use `claudee` from the CLI for one PR at a time.

## GitHub PR Review Workflow

- When addressing review feedback, use a thread-aware read of GitHub review threads; flat comment lists are not enough because they lose resolved/outdated state.
- After implementing, validating, committing, and pushing a fix, always mark every review thread resolved that the pushed commit actually addresses.
- If the fresh review pass posts any actionable finding, fix it, validate, push, re-read thread-aware state, resolve only addressed threads, and run another fresh local Codex review pass. Repeat until the current HEAD has an explicit no-findings result posted on the PR.
- Never resolve a thread just to clear the list. If a thread remains unresolved intentionally, say why and include the next concrete fix.
- Before requesting a fresh review pass, merging, or publishing, re-check unresolved review threads and report the count. Before merging or publishing, also verify that the latest review result was posted for the current HEAD.
- Merge and publish only after the relevant PR has a completed Codex review result for the current HEAD, no unresolved review threads, required checks are green or explicitly accepted, any dependent lockfile/runtime update plan is clear, and the maintainer explicitly approves the merge/publish step.
- After publishing a first-party package, update every dependent consumer lockfile/runtime that should consume the new immutable version, then commit those dependency updates separately.

## Runtime Response, I18n, And HTMX Rules

- New or touched runtime/user-facing messages must use i18n keys through `t()`.
- Use `./radaptor.sh i18n:scan-hardcoded --json` to find visible UI literals in supported templates (`.php`, `.blade.php`, `.twig`) that bypass i18n keys. These are warnings by default; `i18n:doctor` exposes them as `hardcoded_ui` and only fails on them with `--strict-hardcoded`.
- API, JSON, HTMX, MCP, CLI-web, and other non-HTML flows must return structured response data or headers instead of session messages.
- Use `Request::wantsNonHtmlResponse()` for response-family detection. Do not hand-read `HTTP_ACCEPT`, `HTTP_X_REQUESTED_WITH`, or `HTTP_HX_REQUEST`, and do not add query-parameter fallbacks such as `ajax=1`.
- For HTMX admin flows, use header-detected server-rendered fragments and stable swap targets. If an OOB swap inserts new `hx-*` markup outside the original target, verify whether the current HTMX runtime processes it automatically; if not, explicitly process the inserted root and cover it with a browser smoke.

## Repo Baseline Minimums

- This repo keeps the tracked baseline files for the `php-consumer-app` profile.
- The worktree must have `core.hooksPath=.githooks`.
- This is a PHP-heavy repo, so it must keep:
  - `.php-cs-fixer.php`
  - `phpstan.neon`
- The local-override guard is part of the baseline and must stay enabled.

## Supported Commands

- `./docker/build-php-platform.sh dev`
- `./docker-compose.sh up -d --build`
- `./bin/docker-compose-packages-dev.sh radaptor-portal up -d --build`
- `./composer.sh install`
- `./radaptor.sh install --json`
- `./radaptor.sh seed:run --seed-class SeedPortalPublicSurface --json`
- `./docker-compose.sh exec -T -e XDEBUG_MODE=off php phpunit`
- `./docker-compose.sh exec -T -e XDEBUG_MODE=off php phpstan analyze`
- `./docker-compose.sh exec -T php bash -lc 'cd /app && ./php-cs-fixer.sh --config=.php-cs-fixer.php'`

## Container Bring-up Rule

- Before implementation, commits, hooks, CLI work, browser smoke, Playwright, or package-dev verification, start every Docker Compose stack that is relevant to the repos/worktrees you will touch.
- Clean proof/tmp app worktrees, PR-sync clones, and other app checkouts are separate Docker Compose projects because Docker labels include the worktree path. A running `php` container from another checkout does not satisfy this worktree's hooks.
- Bring up this worktree's own stack with `./docker-compose.sh up -d --build` before relying on its hooks or tests. Set non-conflicting ports in `.env` when multiple app stacks run at once.
- Do not bypass Git hooks only because the expected Docker Compose project is not running. Start the relevant stack first; if a hook still cannot run, state the reason and the equivalent checks that were run before committing.
- If the Docker daemon / Docker Desktop is not running, do not work around it: ask the user to start Docker Desktop, wait until the daemon is up, and only then continue.
- After a reboot and before general verification, start the full dev stack with `./docker-compose.sh up -d --build`.
- Do not bring this app up with a handpicked service subset unless the task explicitly needs a narrower diagnostic setup.
- The queue worker service name is `swoole-queue-worker`; Swoole itself is built into the `php` image.

## Verification

- `bin/check-repo-baseline.sh`
- `../bin/cleanup-workspace-ephemera.sh --phase registry-first`
- `./docker-compose.sh exec -T -e XDEBUG_MODE=off php phpunit`
- `./docker-compose.sh exec -T -e XDEBUG_MODE=off php phpstan analyze`
- Browser smoke: `/`, `/comparison/`, `/request-access/`, `/roadmap/`, `/login.html`, `/admin/index.html`
