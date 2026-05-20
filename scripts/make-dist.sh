#!/usr/bin/env bash

set -euo pipefail
shopt -s nullglob

. "$(dirname "$0")/_util.sh"

ASSET_DIR=${ASSET_DIR:-"$PWD/assets"}
DIST_DIR=${DIST_DIR:-"$ASSET_DIR/dist"}

mkdir -p "$DIST_DIR"

# Detect GitHub context for URLs
REPO=${GITHUB_REPOSITORY:-"your-user/your-repo"}

PHP_VERSION=${PHP_VERSION:-"8.1"}
WP_VERSION=${WP_VERSION:-"6.9"}

FOR_RELEASE=${FOR_RELEASE:-"0"}
COMMIT_MESSAGE=${COMMIT_MESSAGE:-""}

RELEASE_URL=${RELEASE_URL:-""}
tag_name=${GITHUB_REF_NAME:-"v0.0.0"}
new_releases=0

if [[ "$FOR_RELEASE" == "1" && "$COMMIT_MESSAGE" != "" ]]; then
    tag_name=$(echo "$COMMIT_MESSAGE" | head -n 1 | sed 's/chore(release): //; s/^v//')
fi

make_dist() {
    local pkg_dir="${1%/}"
    local is_check="${2:-0}"

    local pkg="${pkg_dir##*/}"
    local pkg_type
    local pkg_version
    local manifest_version="none"

    pkg_type=$(jq -r '.type' "$pkg_dir/composer.json" | sed 's/wordpress-//')

    e_start "Creating distribution for\e[0m '\e[1;33m$pkg\e[0m' (\e[1;33m$pkg_type\e[0m)..."

    if [ ! -f "$pkg_dir/.distignore" ]; then
        echo -e "\e[1;35mNotice:\e[0m No .distignore found for '\e[1;33m$pkg\e[0m', skipping"

        e_end
        return 0
    fi

    pkg_version=$(jq -r '.version' "$pkg_dir/package.json")

    if [[ -f "$DIST_DIR/release.json" ]]; then
        manifest_version=$(jq -r ".[\"$pkg\"].version // \"none\"" "$DIST_DIR/release.json")
    fi

    if [[ -n "${CI:-}" && "$pkg_version" == "$manifest_version" ]]; then
        echo -e "\e[1;35mNotice:\e[0m '\e[1;33m$pkg\e[0m' is already at version \e[1;33m$pkg_version\e[0m, skipping"

        e_end
        return 0
    fi

    # On check mode: if runs locally it should always be a new release.
    if [[ $is_check -eq 1 ]]; then
        echo -e "\e[1;34mInfo:\e[0m '\e[1;33m$pkg\e[0m' new version \e[1;33m$pkg_version\e[0m available"
        new_releases=$((new_releases+1))

        e_end
        return 0
    fi

    echo -e "\e[1;36mInfo:\e[0m '\e[1;33m$pkg\e[0m' (\e[1;33mv$pkg_version\e[0m)..."

    composer -d "$pkg_dir" install -q --no-dev

    rm -f "$DIST_DIR/$pkg"*.zip

    cp LICENSE-GPL "$pkg_dir/license.txt"

    "$(dirname "$0")/make-pot.sh" "$pkg_dir"

    _wp dist-archive "$pkg_dir" "$DIST_DIR" --force --create-target-dir --filename-format="{name}"

    local pkg_archive="$pkg.$pkg_version.zip"
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

        echo -e "\e[1;32mSuccess:\e[0m '\e[1;33m$pkg\e[0m' manifest updated"
    else
        echo -e "\e[1;36mInfo:\e[0m '\e[1;33m$pkg\e[0m' no manifest update"
    fi

    rm "$pkg_dir"/{license.txt,composer.lock}

    e_end
}

e_start "Fetching previous manifest..."
if [[ ! -f $DIST_DIR/release.json ]]; then
    if [[ -n "$RELEASE_URL" ]] && curl -s -f "$RELEASE_URL" -o "$DIST_DIR/release.json"; then
        echo -e "\e[1;36mInfo:\e[0m Fetched existing manifest from \e[1;33m$RELEASE_URL\e[0m."
    else
        echo -e "\e[1;35mNotice:\e[0m No existing manifest found. Starting fresh."
        echo '{}' > "$DIST_DIR/release.json"
    fi
else
    echo -e "\e[1;35mNotice:\e[0m Manifest already exists, use existing."
fi
e_end

declare -A pkgs

is_check=0
pkgs=()

for arg in "$@"; do
    if [[ "$arg" == "--check" ]]; then
        is_check=1
        pkgs=() # Clear out existing pkgs

        continue
    fi

    # Capture the path up to and including the first directory after 'packages/'
    if [[ "$arg" =~ (.*packages/[^/]+) ]]; then
        pkgs["${BASH_REMATCH[1]}"]=1
    fi
done

if [ ${#pkgs[@]} -gt 0 ]; then
    # Get unique keys and sort them for consistency
    IFS=$'\n' sorted_pkgs=($(sort <<<"${!pkgs[*]}"))
    unset IFS
else
    sorted_pkgs=(packages/*/)
fi

for pkg_dir in ${sorted_pkgs[@]}; do
    make_dist "$pkg_dir" "$is_check"
done

if [[ -n "${CI:-}" && -n "${GITHUB_OUTPUT}" ]]; then
    echo "release-version=$tag_name" >> $GITHUB_OUTPUT
    echo "new-release=$new_releases" >> $GITHUB_OUTPUT
fi

if [[ "$FOR_RELEASE" == '1' ]]; then
  echo -e "\e[1;32mSuccess:\e[0m Prepare for '\e[1;33m$tag_name\e[0m'"
else
  echo -e "\e[1;35mNotice:\e[0m Prepare for '\e[1;33m$tag_name\e[0m'"
fi
