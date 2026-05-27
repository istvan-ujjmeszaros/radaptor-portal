#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/docker/dev-wrapper-common.sh"

PROJECT_ROOT="$(radaptor_wrapper_project_root "${BASH_SOURCE[0]}")"
cd "$PROJECT_ROOT"

radaptor_wrapper_export_host_identity
radaptor_wrapper_compose_base_args compose_args

exec docker compose "${compose_args[@]}" "$@"
