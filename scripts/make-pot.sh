#!/usr/bin/env bash

set -euo pipefail
shopt -s nullglob

. "$(dirname "$0")/_util.sh"

make_pot() {
    local pkg_dir="$1"
    local pkg="${pkg_dir##*/}"

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;33mNotice:\e[0m No .distignore found for $pkg, skipping"
        continue
    fi

    _wp i18n make-pot "$pkg_dir" "$pkg_dir/languages/$pkg.pot"
}

if [[ -n $1 ]]; then
    make_pot "$1"
    exit 0
fi

for pkg_dir in packages/*/; do
    make_pot "$pkg_dir"
done
