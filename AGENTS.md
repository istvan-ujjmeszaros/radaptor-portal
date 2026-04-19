# Radaptor Portal — Agent Rules

## Project Overview

This is `radaptor-portal`, the public-facing Radaptor tech-demo application.
It is a registry-first consumer app with a lean public surface and an admin shell.

The current public routes are:
- `/`
- `/comparison/`
- `/request-access/`

The Portal public surface is seed-driven:
- `app/seeds/specs/portal-public.json`
- optional HTML content fragments under `app/seeds/specs/content/`

## Repo Baseline Minimums

- This repo must keep the tracked repo baseline files committed:
  - `.repo-baseline-profile`
  - `.githooks/install.sh`
  - `.githooks/pre-commit`
  - `bin/check-repo-baseline.sh`
  - `.github/workflows/repo-checks.yml`
- The worktree must have `core.hooksPath=.githooks`.
- Because this is a PHP-heavy repo, it must also keep:
  - `.php-cs-fixer.php`
  - `phpstan.neon`

## Supported Commands

- `./docker/build-php-platform.sh dev`
- `docker compose -f docker-compose-dev.yml up -d --build`
- `./composer.sh install`
- `./radaptor.sh install --json`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpunit`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpstan analyze`
- `docker compose -f docker-compose-dev.yml exec -T php bash -lc 'cd /app && ./php-cs-fixer.sh --config=.php-cs-fixer.php'`
- `./radaptor.sh seed:run --seed-class SeedPortalPublicSurface --json`

## Container Bring-up Rule

- After a reboot and before general verification, start the full dev stack with `docker compose -f docker-compose-dev.yml up -d --build`.
- Do not bring this app up with a handpicked service subset unless the task explicitly needs a narrower diagnostic setup.
- The queue worker service name is `swoole-queue-worker`; Swoole itself is built into the `php` image.

## Content Source Of Truth

- Public portal pages are source-controlled through the seed spec and content files.
- If a page uses `settings.content_file`, the file content is the canonical source of truth.
- Re-running `SeedPortalPublicSurface` will sync that file-backed content back into the CMS state.

## Verification

- `bin/check-repo-baseline.sh`: repo baseline + formatting check
- CI: `.github/workflows/repo-checks.yml`
- PHPUnit: `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpunit`
- PHPStan: `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpstan analyze`
