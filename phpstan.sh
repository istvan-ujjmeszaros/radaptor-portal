#!/usr/bin/env bash

set -euo pipefail

PROJECT_ROOT="$(cd "$(dirname "${BASH_SOURCE[0]}")" && pwd)"
cd "$PROJECT_ROOT"

level=""
config=""

# Optional explicit paths to analyze. When none are provided, PHPStan uses
# the paths declared in phpstan.neon.
paths=()

# Function to display help message
show_help() {
cat << EOF
Usage: $0 [options] [paths]

Options:
  -l, --level LEVEL       Set PHPStan analysis level (0-9).
  -c, --config FILE       Specify PHPStan configuration file.
  -h, --help              Display this help message.

Paths:
  Additional paths to analyze. If not provided, uses paths from phpstan.neon.
EOF
}

# Parse optional arguments
while [[ $# -gt 0 ]]; do
  key="$1"
  case $key in
    -l|--level)
      level="$2"
      shift # past argument
      shift # past value
      ;;
    -c|--config)
      config="$2"
      shift # past argument
      shift # past value
      ;;
    -h|--help)
      show_help
      exit 0  # Exit after showing help
      ;;
    *)    # unknown option
      paths+=("$1") # add path to analyze
      shift # past argument
      ;;
  esac
done

# Build the PHPStan command with options and paths
command=(vendor/bin/phpstan analyse)

if [[ -n "$level" ]]; then
    command+=(-l "$level")
fi

if [[ -n "$config" ]]; then
    command+=(-c "$config")
fi

if [[ ${#paths[@]} -gt 0 ]]; then
    command+=("${paths[@]}")
fi

exec_args=(exec)
if [[ ! -t 0 || ! -t 1 ]]; then
    exec_args+=(-T)
fi

# Execute the PHPStan command
echo "Running PHPStan with command: ${command[*]}"
exec docker compose -f docker-compose-dev.yml "${exec_args[@]}" php "${command[@]}"
