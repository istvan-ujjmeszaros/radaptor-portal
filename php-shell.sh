#!/usr/bin/env bash
set -euo pipefail

source "$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)/docker/dev-wrapper-common.sh"

PROJECT_ROOT="$(radaptor_wrapper_project_root "${BASH_SOURCE[0]}")"
cd "$PROJECT_ROOT"

radaptor_wrapper_run_preflight

if [[ "$#" -eq 0 ]]; then
	radaptor_wrapper_exec_in_php_as_host_user bash
fi

radaptor_wrapper_exec_in_php_as_host_user bash -lc "$*"
