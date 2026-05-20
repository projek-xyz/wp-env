#!/usr/bin/env bash

set -euo pipefail
shopt -s nullglob

. "$(dirname "$0")/_util.sh"

make_pot() {
    local pkg_dir="${1%/}"
    local pkg="${pkg_dir##*/}"
    local pot_file="$pkg_dir/languages/$pkg.pot"
    local pot_temp=$(mktemp)

    if [ ! -d "$pkg_dir" ]; then
        echo -e "\e[1;33mNotice:\e[0m No such directory: $pkg_dir, skipping"
        rm "$pot_temp"
        return 0
    fi

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;33mNotice:\e[0m No .distignore found for $pkg, skipping"
        rm "$pot_temp"
        return 0
    fi

    mkdir -p "$pkg_dir/languages"

    _wp i18n make-pot "$pkg_dir" "$pot_temp"

    if [ -f "$pot_file" ]; then
        # Capture ONLY the date value, removing labels, quotes, and the trailing \n
        local creation_date=$(grep "POT-Creation-Date:" "$pot_file" | head -n 1 | sed 's/.*POT-Creation-Date: \(.*\)\\n.*/\1/')

        if [[ -n "$creation_date" ]]; then
            local pot_temp_fixed=$(mktemp)
            # Use | as delimiter and match the exact PO header format
            sed "s|\"POT-Creation-Date: .*\\\\n\"|\"POT-Creation-Date: $creation_date\\\\n\"|" "$pot_temp" > "$pot_temp_fixed"
            mv "$pot_temp_fixed" "$pot_temp"
        fi

        if cmp -s "$pot_temp" "$pot_file"; then
            rm "$pot_temp"
            return 0
        fi
    fi

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
