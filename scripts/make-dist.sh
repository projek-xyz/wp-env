#!/usr/bin/env bash

set -euo pipefail
shopt -s nullglob

. "$(dirname "$0")/_util.sh"

ASSET_DIR=${ASSET_DIR:-"$PWD/assets"}
DIST_DIR="$ASSET_DIR/dist"

mkdir -p "$DIST_DIR"

# Detect GitHub context for URLs
REPO=${GITHUB_REPOSITORY:-"your-user/your-repo"}

PHP_VERSION=${PHP_VERSION:-"8.1"}
WP_VERSION=${WP_VERSION:-"6.9"}

FOR_RELEASE=${FOR_RELEASE:-"0"}
COMMIT_MESSAGE=${COMMIT_MESSAGE:-""}

tag_name=${GITHUB_REF_NAME:-"v0.0.0"}
release_url="https://projek-xyz.github.io/wp-env/release.json"

if [[ "$FOR_RELEASE" == "1" && "$COMMIT_MESSAGE" != "" ]]; then
    tag_name=$(echo "$COMMIT_MESSAGE" | head -n 1 | sed 's/chore(release): //; s/^v//')
fi

e_start "Fetching previous manifest..."
if curl -s -f "$release_url" -o "$DIST_DIR/release.json"; then
    echo -e "\e[1;34mInfo:\e[0m Existing release manifest loaded."
else
    echo -e "\e[1;35mNotice:\e[0m No existing manifest found at $release_url. Starting fresh."
    echo '{}' > "$DIST_DIR/release.json"
fi
e_end

for pkg_dir in packages/*/; do
    pkg_dir="${pkg_dir%/}"
    pkg="${pkg_dir##*/}"
    pkg_type=$(cat "$pkg_dir/composer.json" | jq -r '.type' | sed 's/wordpress-//')

    e_start "Creating distribution for $pkg ($pkg_type)..."

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;35mNotice:\e[0m No .distignore found for $pkg, skipping"

        e_end
        continue
    fi

    pkg_version=$(cat "$pkg_dir/package.json" | jq -r '.version')
    manifest_version=$(jq -r ".[\"$pkg\"].version // \"none\"" "$DIST_DIR/release.json")

    if [[ "$pkg_version" == "$manifest_version" ]]; then
        echo -e "\e[1;35mNotice:\e[0m $pkg is already at version $pkg_version"

        e_end
        continue
    fi

    echo -e "\e[1;34mInfo:\e[0m $pkg (v$pkg_version)..."

    composer -d "$pkg_dir" install -q --no-dev

    rm -f "$DIST_DIR/$pkg"*.zip

    cp LICENSE-GPL "$pkg_dir/license.txt"

    _wp i18n make-pot "$pkg_dir" "$pkg_dir/languages/$pkg.pot"

    _wp dist-archive "$pkg_dir" "$DIST_DIR" --force --create-target-dir --filename-format="{name}"

    pkg_archive="$pkg.$pkg_version.zip"
    mv "$DIST_DIR/$pkg.zip" "$DIST_DIR/$pkg_archive"

    if [[ "$FOR_RELEASE" == "1" ]]; then
        download_url="https://github.com/$REPO/releases/download/$tag_name/$pkg_archive"
        info_url="https://github.com/$REPO/blob/main/packages/$pkg/CHANGELOG.md"

        jq ".[\"$pkg\"] = {
            \"type\": \"$pkg_type\",
            \"version\": \"$pkg_version\",
            \"tag_name\": \"$tag_name\",
            \"download_url\": \"$download_url\",
            \"info_url\": \"$info_url\",
            \"php_version\": \"$PHP_VERSION\",
            \"wp_version\": \"$WP_VERSION\"
        }" "$DIST_DIR/release.json" > "$DIST_DIR/release.tmp" && mv "$DIST_DIR/release.tmp" "$DIST_DIR/release.json"
    fi

    rm "$pkg_dir"/{license.txt,composer.lock}

    e_end
done

export RELEASE_VERSION=$tag_name
