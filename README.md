# Radaptor Portal

`radaptor-portal` is a registry-first consumer app built on the `radaptor-app` skeleton.

This repo is the lean Portal Demo slice:
- public landing page at `/`
- technical comparison page at `/comparison/`
- working request-access page with email confirmation at `/request-access/`
- working admin login at `/login.html`
- working admin shell at `/admin/index.html`

What is intentionally **not** in this slice:
- no real public signup
- no magic-link flow
- no account provisioning behind the request-access confirmation flow
- no standalone newsletter subscriber/admin surface

For internal maintainer workspace topics such as `packages-dev/...`, `radaptor.local.json`, and
the workspace package-dev Docker override, see the workspace-level
[README.md](../README.md).

## Quick start

1. Build the local PHP platform image:
   - `./docker/build-php-platform.sh dev`
2. Start the local stack:
   - `docker compose -f docker-compose-dev.yml up -d --build`
3. Run CLI commands in the `php` container:
   - Docker Desktop path: open a terminal for the `php` container and run `bash`
   - shell shortcut: `./php-shell.sh`
   - direct shortcuts:
     - `./composer.sh install`
     - `./radaptor.sh install --json`
4. Open the site:
   - homepage: `http://localhost:8020/`
   - comparison: `http://localhost:8020/comparison/`
   - request access: `http://localhost:8020/request-access/`
   - login: `http://localhost:8020/login.html`
   - admin: `http://localhost:8020/admin/index.html`

If you are running multiple local stacks in parallel, use `.env` to override ports and the compose
project name before `docker compose up`.

All supported CLI work happens inside Docker. Host PHP and host Composer are not part of the
supported workflow.

The `php` runtime image is intentionally thin. The slow PHP extension/tooling layer now lives in a
separate local platform image so routine runtime Dockerfile changes do not rebuild `swoole`,
`redis`, `brotli`, Composer, Phive, and `php-cs-fixer`.

Default ACL baseline after install:
- `/` is the public ACL baseline
- `/login.html` inherits that public baseline and is reconciled to a single `Form(UserLogin)` widget
- `/admin/` is explicitly non-inheriting and admin-only

Anonymous access to protected pages keeps the requested URL and renders the login page with `403`,
instead of redirecting to a different URL. Direct access to `/login.html` itself returns `200`.

What happens on first install:
- `docker compose up` gives you the supported PHP runtime
- `./radaptor.sh install --json` bootstraps the pinned framework package if it is still missing
- then the framework CLI continues the normal install/update/build/migrate/seed flow
- the committed `radaptor.json` points at the default public package registry:
  `https://packages.radaptor.com/registry.json`
- `RADAPTOR_REGISTRY_URL` remains available as a local override for scratch registries or isolated
  testing

### Local override example

For a local registry and a second app instance, override the default registry in shell env or `.env`:

```bash
export RADAPTOR_REGISTRY_URL=http://host.docker.internal:8091/registry.json
export COMPOSE_PROJECT_NAME=radaptor-portal-dev
export APP_HTTP_PORT=8085
export APP_HTTPS_PORT=8445
export APP_DB_PORT=3309
docker compose -f docker-compose-dev.yml up -d --build
```

### Parallel clone / playground example

If you want to validate a second copy without stopping an existing app instance, use a different
folder and give that copy its own compose project name and host ports via shell env or `.env`.

## Local PHP platform image

The PHP 8.4 stack is split into:
- a heavy local platform image built by `./docker/build-php-platform.sh`
- a thin runtime image used by `docker compose`

The default local platform tags are:
- `radaptor-portal-php-platform:8.4-dev-local`
- `radaptor-portal-php-platform:8.4-prod-local`

If you want to point the runtime at a different prebuilt base later, override:
- `RADAPTOR_PHP_PLATFORM_DEV_IMAGE`
- `RADAPTOR_PHP_PLATFORM_PROD_IMAGE`

Examples:

- build dev only: `./docker/build-php-platform.sh dev`
- build prod only: `./docker/build-php-platform.sh prod`
- build both: `./docker/build-php-platform.sh all`

## Default bootstrap credentials

The first `mandatory` app seed ensures a bootstrap admin user based on `.env`:
- `APP_BOOTSTRAP_ADMIN_USERNAME`
- `APP_BOOTSTRAP_ADMIN_PASSWORD`
- `APP_BOOTSTRAP_ADMIN_LOCALE`
- `APP_BOOTSTRAP_ADMIN_TIMEZONE`

Change the password after the first login.

## What is committed

This skeleton commits:
- `radaptor.json`
- `radaptor.lock.json`

The committed lockfile pins tested package versions. On first run, `radaptor.php install` uses the
locked `core.framework` package metadata and the configured registry URL to bootstrap the framework
package into `packages/registry/core/framework` before delegating into the framework CLI.

By default that registry URL is `https://packages.radaptor.com/registry.json`. Local development can
still override it with `RADAPTOR_REGISTRY_URL` or a local `.env`.
The first-run DB bootstrap currently relies on the MariaDB init schema shipped in `docker/mariadb/initdb.d/`.

### Maintainer note: immutable first-party package releases

When this skeleton is validated in registry-first mode, first-party package changes must be
released as new immutable versions before the consumer app is updated:

- committed `radaptor.json` stays registry-first
- maintainer-local first-party overrides live only in gitignored `radaptor.local.json`
- local dev mode does not need release/publish
- registry-first validation does need an immutable package release after first-party package changes
- the consumer app refresh stays the normal `./radaptor.sh update --json`, but only after the
  registry deploy completed
- then run a fresh clone / scratch bootstrap proof

The supported maintainer path is:

- stable release: `./radaptor.sh package:release <package-key> --json`
- prerelease: `./radaptor.sh package:prerelease <package-key> --channel alpha|beta|rc --json`

After that:

- commit the bumped `.registry-package.json` in the package repo
- commit + push the `radaptor_plugin_registry` repo
- pushes to `radaptor_plugin_registry/main` auto-deploy to `https://packages.radaptor.com/`
- only then run `./radaptor.sh update --json` so `radaptor.lock.json` and `packages/registry/...`
  pick up the new version

If you want to work on first-party packages locally, use the shared workspace repos:

- `/apps/_RADAPTOR/packages-dev/core/framework/`
- `/apps/_RADAPTOR/packages-dev/core/cms/`
- `/apps/_RADAPTOR/packages-dev/themes/<theme-id>/`

Standalone `docker-compose-dev.yml` stays registry-first. If you need first-party package dev mode,
start the Portal through the workspace helper so `/workspace/packages-dev/...` is mounted:

```bash
cd /apps/_RADAPTOR
./bin/docker-compose-packages-dev.sh radaptor-portal up -d --build
```

Keep committed `radaptor.json` registry-first. Put maintainer-local overrides into gitignored
`radaptor.local.json`, for example with:

```json
{
  "core": {
    "framework": { "source": { "type": "dev", "location": "core/framework" } },
    "cms": { "source": { "type": "dev", "location": "core/cms" } }
  },
  "themes": {
    "portal-admin": { "source": { "type": "dev", "location": "themes/portal-admin" } }
  }
}
```

While local overrides are active, the app writes `radaptor.local.lock.json` instead of mutating the
committed lockfile. Use `./radaptor.sh local-lock:refresh --json` to reseed the local lock from the
committed registry-first lock plus the active local overrides.

The workspace package-dev runtime makes dev mode explicit:

- `RADAPTOR_WORKSPACE_DEV_MODE=1`
- `RADAPTOR_DEV_ROOT=/workspace/packages-dev`
- only the literal `RADAPTOR_WORKSPACE_DEV_MODE=1` value enables workspace dev mode

If `radaptor.local.json` exists without that runtime mode, bootstrap and CLI now fail fast instead
of guessing.

`packages/registry/...` is install-owned runtime state. It is not the source of truth and should
not be edited for first-party package development.

Useful maintainer package commands:

- `./radaptor.sh package:status --json`
- `./radaptor.sh package:release <package-key> --json`
- `./radaptor.sh package:prerelease <package-key> --channel alpha|beta|rc --json`
- `cd /apps/_RADAPTOR && ./bin/check-workspace-package-state.sh --strict`
- `cd /apps/_RADAPTOR && ./bin/refresh-workspace-consumer-locks.sh`

## Docker CLI options

Use one of these supported approaches:

- Docker Desktop: open a terminal for the running `php` container, run `bash`, then use `composer`
  and `php radaptor.php ...` directly
- `./php-shell.sh`: open a shell in the running `php` container
- `./composer.sh <args>`: run Composer in the `php` container
- `./radaptor.sh <args>`: run `php radaptor.php ...` in the `php` container

When you use `--json`, progress chatter is suppressed from stdout and appended to
`.logs/cli_commands.log`, with each command separated by its own log section.

Examples:

- `./composer.sh install`
- `./radaptor.sh install --json`
- `./radaptor.sh update --json`
- `./radaptor.sh seed:status --seed-class SeedPortalPublicSurface --json`
- `./radaptor.sh seed:run --seed-class SeedPortalAdminSurface --json`
- `./radaptor.sh widget:list /login.html --json`
- `./radaptor.sh webpage:export-spec /comparison/ --json`
- `./radaptor.sh tree:check --tree all --json`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpunit`
- `docker compose -f docker-compose-dev.yml exec -T -e XDEBUG_MODE=off php phpstan analyze`

## Playwright E2E

The repo now carries its own minimal Playwright setup.

Install the Node-side test dependencies once:

- `npm install`
- `npx playwright install chromium`

Run the local portal e2e smoke:

- `npm run test:e2e`

Run the same tests against an alternate stack, for example the scratch rebuild proof:

- `E2E_BASE_URL=http://localhost:8120 npx playwright test tests/e2e/portal-public.spec.js tests/e2e/admin-smoke.spec.js`

## Portal Seed Specs

The portal surface is now defined by declarative JSON specs and reconciled through the CMS seed
helper instead of hand-maintained page/widget SQL assumptions.

- public surface spec: `app/seeds/specs/portal-public.json`
- admin surface spec: `app/seeds/specs/portal-admin.json`
- rich HTML content fragments can live beside the specs under `app/seeds/specs/content/`

Useful maintainer checks:

- `./radaptor.sh seed:status --seed-class SeedSkeletonBootstrap --json`
- `./radaptor.sh seed:status --seed-class SeedPortalPublicSurface --json`
- `./radaptor.sh seed:status --seed-class SeedPortalAdminSurface --json`
- `./radaptor.sh widget:list /login.html --json`
- `./radaptor.sh resource:acl-list /admin/ --json`
- `./radaptor.sh webpage:export-spec /comparison/ --json`

## Browser Event API Docs

The framework ships a public browser-event manual in both JSON and HTML.

- catalog JSON: `http://localhost:8020/?context=events&event=index&format=json`
- catalog HTML: `http://localhost:8020/?context=events&event=index&format=html`
- detail JSON example: `http://localhost:8020/?context=events&event=show&slug=resource:view&format=json`
- detail HTML example: `http://localhost:8020/?context=events&event=show&slug=resource:view&format=html`

If you add or change documented browser events, rebuild the generated registry inside the `php`
container:

- `./radaptor.sh build:event-docs`

## Notes

- The committed manifest is registry-first. Maintainer-local first-party package development is enabled through gitignored `radaptor.local.json`, but only when the workspace package-dev compose override is active.
- Package assets are generated under `public/www/assets/packages/` and are git-ignored.
- `framework`, `cms`, and `portal-admin` are expected to come from the registry, not from sibling working copies.
- The public portal theme is app-owned in this repo under `app/themes/RadaptorPortal/`.
- The request-access flow stores one request per normalized email and requires confirmation through the emailed link.
- The updates/newsletter checkbox is stored as consent on that same request record; there is no separate subscriber admin flow yet.
