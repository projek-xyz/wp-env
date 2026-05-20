#!/usr/bin/env bash

set -euo pipefail
shopt -s nullglob

. "$(dirname "$0")/_util.sh"

make_pot() {
    local pkg_dir="${1%/}"
    local pkg="${pkg_dir##*/}"

    if [ ! -d "$pkg_dir" ]; then
        echo -e "\e[1;33mNotice:\e[0m No such directory: $pkg_dir, skipping"
        return 0
    fi

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;33mNotice:\e[0m No .distignore found for $pkg, skipping"
        continue
    fi

    mkdir -p "$pkg_dir/languages"

    local pot_temp=$(mktemp)
    local pot_file="$pkg_dir/languages/$pkg.pot"

    _wp i18n make-pot "$pkg_dir" "$pot_temp"

    mv "$pot_temp" "$pot_file"
}

# If no arguments are passed, loop through all packages
if [ $# -eq 0 ]; then
    for pkg_dir in packages/*/; do
        make_pot "$pkg_dir"
    done

    exit 0
fi

declare -A pkgs

for arg in "$@"; do
    # Capture the path up to and including the first directory after 'packages/'
    if [[ "$arg" =~ (.*packages/[^/]+) ]]; then
        pkgs["${BASH_REMATCH[1]}"]=1
    fi
done

# Output the unique package paths with quotes
if [ ${#pkgs[@]} -gt 0 ]; then
    # Get unique keys and sort them for consistency
    IFS=$'\n' sorted_pkgs=($(sort <<<"${!pkgs[*]}"))
    unset IFS

    for pkg_dir in "${sorted_pkgs[@]}"; do
        make_pot "$pkg_dir"
    done
fi
