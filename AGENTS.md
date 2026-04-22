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

- Editable first-party repos live under `/apps/_RADAPTOR/packages-dev/...`.
- Inside Docker they are available under `/workspace/packages-dev/...`, but only when the workspace package-dev compose override is active.
- The committed Portal manifest must stay registry-first.
- Maintainer-local dev mode is enabled only through `radaptor.local.json`, not by editing committed `radaptor.json`.
- `packages/registry/...` and `packages-dev/...` must never be connected with symlinks.
- `radaptor.local.json` without the package-dev compose override is an invalid runtime state and must fail hard.
- Host-side workflow is Git-only. Hooks and helper scripts must dispatch every non-Git check into the supported container; never require host PHP, Composer, Python, php-cs-fixer, or Radaptor CLI.
- App-local transient QA outputs belong under `tmp/`. Do not leave `playwright-report/`, `test-results/`, proof clones, restore sandboxes, or scratch verification directories at repo root.

## Repo Baseline Minimums

- This repo keeps the tracked baseline files for the `php-consumer-app` profile.
- The worktree must have `core.hooksPath=.githooks`.
- This is a PHP-heavy repo, so it must keep:
  - `.php-cs-fixer.php`
  - `phpstan.neon`
- The local-override guard is part of the baseline and must stay enabled.

## Supported Commands

- `./docker/build-php-platform.sh dev`
- `docker compose -f docker-compose-dev.yml up -d --build`
- `./bin/docker-compose-packages-dev.sh radaptor-portal up -d --build`
- `./composer.sh install`
- `./radaptor.sh install --json`
- `./radaptor.sh seed:run --seed-class SeedPortalPublicSurface --json`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpunit`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpstan analyze`
- `docker compose -f docker-compose-dev.yml exec -T php bash -lc 'cd /app && ./php-cs-fixer.sh --config=.php-cs-fixer.php'`

## Container Bring-up Rule

- After a reboot and before general verification, start the full dev stack with `docker compose -f docker-compose-dev.yml up -d --build`.
- Do not bring this app up with a handpicked service subset unless the task explicitly needs a narrower diagnostic setup.
- The queue worker service name is `swoole-queue-worker`; Swoole itself is built into the `php` image.

## Verification

- `bin/check-repo-baseline.sh`
- `../bin/cleanup-workspace-ephemera.sh --phase registry-first`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpunit`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpstan analyze`
- Browser smoke: `/`, `/comparison/`, `/request-access/`, `/roadmap/`, `/login.html`, `/admin/index.html`
