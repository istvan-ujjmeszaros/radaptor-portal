#!/usr/bin/env bash
set -euo pipefail

MODE="${1:-repair}"
APP_ROOT="${APP_ROOT:-/app}"

fail() {
	echo "dev-permissions: $*" >&2
	exit 1
}

require_host_identity() {
	if [ -z "${HOST_UID:-}" ] || [ -z "${HOST_GID:-}" ]; then
		fail "HOST_UID and HOST_GID must be provided."
	fi
}

ensure_directory_state() {
	local path="$1"
	local mode="$2"

	mkdir -p "$path"
	chown "$HOST_UID:$HOST_GID" "$path"
	chmod "$mode" "$path"
}

ensure_file_state() {
	local path="$1"
	local mode="$2"

	mkdir -p "$(dirname "$path")"
	touch "$path"
	chown "$HOST_UID:$HOST_GID" "$path"
	chmod "$mode" "$path"
}

ensure_runtime_tree_state() {
	local path="$1"

	ensure_directory_state "$path" 0775
	chown -R "$HOST_UID:$HOST_GID" "$path"
}

ensure_registry_state() {
	local parent path

	ensure_directory_state "$APP_ROOT/packages" 0775
	ensure_directory_state "$APP_ROOT/packages/registry" 0775
	ensure_directory_state "$APP_ROOT/packages/registry/core" 0775
	ensure_directory_state "$APP_ROOT/packages/registry/themes" 0775

	for parent in "$APP_ROOT/packages/registry/core" "$APP_ROOT/packages/registry/themes"; do
		[ -d "$parent" ] || continue

		while IFS= read -r -d '' path; do
			if [ -L "$path" ]; then
				fail "Registry package path must not be a symlink: $path"
			fi

			if [ ! -d "$path" ]; then
				[ "$(basename "$path")" = ".gitkeep" ] && continue
				fail "Registry package path must be a directory: $path"
			fi

			if find "$path" -type l -print -quit | grep -q .; then
				fail "Registry package path contains a symlink: $path"
			fi

			chown -R "$HOST_UID:$HOST_GID" "$path"
		done < <(find "$parent" -mindepth 1 -maxdepth 1 -print0)
	done
}

check_registry_state() {
	local parent path unexpected

	check_owned_path "$APP_ROOT/packages/registry" dir
	check_owned_path "$APP_ROOT/packages/registry/core" dir
	check_owned_path "$APP_ROOT/packages/registry/themes" dir

	for parent in "$APP_ROOT/packages/registry/core" "$APP_ROOT/packages/registry/themes"; do
		[ -d "$parent" ] || continue

		while IFS= read -r -d '' path; do
			if [ -L "$path" ]; then
				fail "Registry package path must not be a symlink: $path"
			fi

			if [ ! -d "$path" ]; then
				[ "$(basename "$path")" = ".gitkeep" ] && continue
				fail "Registry package path must be a directory: $path"
			fi

			if find "$path" -type l -print -quit | grep -q .; then
				fail "Registry package path contains a symlink: $path"
			fi

			unexpected="$(find "$path" \( ! -uid "$HOST_UID" -o ! -gid "$HOST_GID" \) -print -quit)"

			if [ -n "$unexpected" ]; then
				fail "Unexpected owner for registry package content: $unexpected"
			fi
		done < <(find "$parent" -mindepth 1 -maxdepth 1 -print0)
	done
}

check_owned_tree() {
	local path="$1"
	local unexpected

	check_owned_path "$path" dir
	unexpected="$(find "$path" \( ! -uid "$HOST_UID" -o ! -gid "$HOST_GID" \) -print -quit)"

	if [ -n "$unexpected" ]; then
		fail "Unexpected owner under $path: $unexpected"
	fi
}

check_owned_path() {
	local path="$1"
	local expected_type="$2"

	if [ ! -e "$path" ]; then
		fail "Expected path is missing: $path"
	fi

	case "$expected_type" in
		dir)
			[ -d "$path" ] || fail "Expected directory but found something else: $path"
			;;
		file)
			[ -f "$path" ] || fail "Expected file but found something else: $path"
			;;
		*)
			fail "Unsupported expected type: $expected_type"
			;;
	esac

	if [ "$(stat -c '%u' "$path")" != "$HOST_UID" ]; then
		fail "Unexpected owner for $path"
	fi

	if [ "$(stat -c '%g' "$path")" != "$HOST_GID" ]; then
		fail "Unexpected group for $path"
	fi
}

preflight() {
	require_host_identity

	ensure_runtime_tree_state "$APP_ROOT/.logs"
	ensure_runtime_tree_state "$APP_ROOT/generated"
	ensure_runtime_tree_state "$APP_ROOT/tmp"
	ensure_directory_state "$APP_ROOT/var" 0775
	ensure_runtime_tree_state "$APP_ROOT/var/cache"
	ensure_file_state "$APP_ROOT/.logs/cli_commands.log" 0664
	ensure_registry_state
}

check() {
	require_host_identity

	check_owned_tree "$APP_ROOT/.logs"
	check_owned_path "$APP_ROOT/.logs/cli_commands.log" file
	check_owned_tree "$APP_ROOT/generated"
	check_owned_tree "$APP_ROOT/tmp"
	check_owned_path "$APP_ROOT/var" dir
	check_owned_tree "$APP_ROOT/var/cache"
	check_registry_state

	for path in \
		"$APP_ROOT/tmp/pr-sync" \
		"$APP_ROOT/.profiler" \
		"$APP_ROOT/_UPLOADS" \
		"$APP_ROOT/vendor"; do
		if [ -e "$path" ]; then
			check_owned_path "$path" dir
		fi
	done

	echo "dev-permissions: check passed"
}

repair() {
	require_host_identity
	preflight

	for path in \
		"$APP_ROOT/tmp/pr-sync" \
		"$APP_ROOT/.profiler" \
		"$APP_ROOT/_UPLOADS" \
		"$APP_ROOT/vendor"; do
		if [ -e "$path" ]; then
			chown -R "$HOST_UID:$HOST_GID" "$path"
		fi
	done

	ensure_registry_state

	echo "dev-permissions: repair completed"
}

case "$MODE" in
	preflight)
		preflight
		;;
	check)
		check
		;;
	repair)
		repair
		;;
	*)
		fail "Usage: ./docker/dev-permissions.sh preflight|check|repair"
		;;
esac
