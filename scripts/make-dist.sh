#!/usr/bin/env bash

set -euo pipefail
shopt -s nullglob

. "$(dirname "$0")/_util.sh"

ASSET_DIR=${ASSET_DIR:-"$PWD/assets"}
DIST_DIR="$ASSET_DIR/dist"

mkdir -p "$DIST_DIR"

# Detect GitHub context for URLs
REPO=${GITHUB_REPOSITORY:-"your-user/your-repo"}
TAG=${GITHUB_REF_NAME:-"v0.0.0"}

echo '{}' > "$DIST_DIR/release.json"

for pkg_dir in packages/*/; do
    pkg_dir="${pkg_dir%/}"
    pkg="${pkg_dir##*/}"

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;33mNotice:\e[0m No .distignore found for $pkg, skipping"
        continue
    fi

    pkg_version=$(cat "$pkg_dir/package.json" | jq -r '.version')
    pkg_type=$(cat "$pkg_dir/composer.json" | jq -r '.type' | sed 's/wordpress-//')

    composer -d "$pkg_dir" install -q --no-dev

    rm -f "$DIST_DIR/$pkg"*.zip

    cp LICENSE-GPL "$pkg_dir/license.txt"

    _wp i18n make-pot "$pkg_dir" "$pkg_dir/languages/$pkg.pot"

    _wp dist-archive "$pkg_dir" "$DIST_DIR" --force --create-target-dir --filename-format="{name}"

    pkg_archive="$pkg.$pkg_version.zip"
    mv "$DIST_DIR/$pkg.zip" "$DIST_DIR/$pkg_archive"

    # 2. Update the manifest entry
    download_url="https://github.com/$REPO/release/download/$TAG/$pkg_archive"
    info_url="https://github.com/$REPO/blob/main/packages/$pkg/CHANGELOG.md"

    jq ".[\"$pkg\"] = {
        \"version\": \"$pkg_version\",
        \"type\": \"$pkg_type\",
        \"package\": \"$download_url\",
        \"url\": \"$info_url\"
    }" "$DIST_DIR/release.json" > "$DIST_DIR/release.tmp" && mv "$DIST_DIR/release.tmp" "$DIST_DIR/release.json"

    rm "$pkg_dir"/{license.txt,composer.lock}
done
