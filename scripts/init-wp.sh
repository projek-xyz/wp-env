#!/usr/bin/env bash

set -euo pipefail

. "$(dirname "$0")/_util.sh"

if [[ -f "$PWD/.env" ]]; then
    . "$PWD/.env"
fi

# ==============================================================================
# Configurations
# ==============================================================================

SETUP_DIR=${SETUP_DIR:-"$PWD"}
ASSET_DIR=${ASSET_DIR:-"$SETUP_DIR/assets"}
SCRIPTS_DIR=${SCRIPTS_DIR:-"$SETUP_DIR/scripts"}
INSTALL_DIR=${INSTALL_DIR:-"$PWD/docker/volumes/wordpress"}

MEDIA_DIR=${MEDIA_DIR:-$ASSET_DIR}
MEDIA_EXTS=${MEDIA_EXTS:-gif,jpg,jpeg,png}

if [[ ! -d "${ASSET_DIR}" ]]; then
    echo -e "\e[1;31mError:\e[0m Unable to continue installation."
    echo -e "       Asset directory '\e[33m${ASSET_DIR}\e[0m' is missing."
    exit 1
fi

SITE_URL=${SITE_URL:-"http://localhost"}
SITE_ADMIN_USER=${SITE_ADMIN_USER:-admin}
SITE_DEFAULT_THEME=${SITE_DEFAULT_THEME:-}
SITE_ICON_FILENAME=${SITE_ICON_FILENAME:-"WordPress-Logo.png"}

SKIP_MEDIA=${SKIP_MEDIA:-0}
SKIP_OPTIONS=${SKIP_OPTIONS:-0}

if [[ "$SKIP_MEDIA" -eq 1 && -n "$SITE_ICON_FILENAME" ]]; then
    echo -e "\e[1;36mNotice:\e[0m '\e[1;33m\$SKIP_MEDIA\e[0m' is set to \e[1;33m1\e[0m, '\e[1;33m\$SITE_ICON_FILENAME\e[0m' will not be imported."
fi

# ==============================================================================

declare -A plugins_map

# ----------------- Blocksy Plugin  Contact         Woo
# ----------------- Comp.   Check   Form7   JetPack Comm.
plugins_map['5.9']='2.0.86  0.2.0   5.7.7   11.2.2  7.5.2'
plugins_map['6.0']='2.0.86  0.2.3   5.7.7   12.0.1  7.7.3'
plugins_map['6.1']='2.0.86  0.2.3   5.7.7   12.5.1  7.9.2'
plugins_map['6.2']='2.0.86  0.2.3   5.8.7   12.7.1  8.2.5'
plugins_map['6.3']='2.0.86  1.9.0   5.9.8   13.2.1  8.7.3'
plugins_map['6.4']='2.0.86  latest  5.9.8   13.6.1  9.0.4'
plugins_map['6.5']='2.1.42  latest  5.9.8   13.9.1  9.4.5'
plugins_map['6.6']='latest  latest  6.0.6   14.4.1  9.8.7'
plugins_map['6.7']='latest  latest  6.1.6   15.1.1  10.3.8'
plugins_map['6.8']='latest  latest  latest  15.7.1  10.7.0'
plugins_map['6.9']='latest  latest  latest  latest  latest'
plugins_map['7.0']='latest  latest  latest  latest  latest'

# ==============================================================================

declare -A themes_map

# ---------------- Blocksy
themes_map['5.9']='2.0.86'
themes_map['6.0']='2.0.86'
themes_map['6.1']='2.0.86'
themes_map['6.2']='2.0.86'
themes_map['6.3']='2.0.86'
themes_map['6.4']='2.0.86'
themes_map['6.5']='2.1.42'
themes_map['6.6']='latest'
themes_map['6.7']='latest'
themes_map['6.8']='latest'
themes_map['6.9']='latest'
themes_map['7.0']='latest'

# ==============================================================================

declare -A options

options['permalink_structure']='/%postname%/'
options['timezone_string']="${SITE_TIMEZONE:-Asia/Jakarta}"
options['thumbnail_size_w']='300'
options['thumbnail_size_h']='300'
options['medium_size_w']='500'
options['medium_size_h']='500'
options['large_size_w']='1080'
options['large_size_h']='1080'
options['blog_upload_space']='50'

# ==============================================================================

declare -A woo_options

woo_options['store_address']=${WC_STORE_ADDRESS:-Jl. Example No. 123}
woo_options['store_city']=${WC_STORE_CITY:-Batang}
woo_options['default_country']=${WC_DEFAULT_COUNTRY:-ID:JT}
woo_options['currency']=${WC_CURRENCY:-IDR}
woo_options['currency_pos']=${WC_CURRENCY_POS:-left_space}
woo_options['store_postcode']=${WC_STORE_POSTCODE:-12345}

woo_options['allowed_countries']=${WC_ALLOWED_COUNTRIES:-specific}
woo_options['all_except_countries']=${WC_ALL_EXCEPT_COUNTRIES:-'[]'}
woo_options['specific_allowed_countries']=${WC_SPECIFIC_ALLOWED_COUNTRIES:-'["ID"]'}
woo_options['ship_to_countries']=${WC_SHIP_TO_COUNTRIES:-specific}
woo_options['specific_ship_to_countries']=${WC_SPECIFIC_SHIP_TO_COUNTRIES:-'["ID"]'}

woo_options['weight_unit']=${WC_WEIGHT_UNIT:-kg}
woo_options['dimension_unit']=${WC_DIMENSION_UNIT:-cm}
woo_options['price_thousand_sep']=${WC_PRICE_THOUSAND_SEP:-.}
woo_options['price_decimal_sep']=${WC_PRICE_DECIMAL_SEP:-,}
woo_options['price_num_decimals']=${WC_PRICE_DECIMAL_NUM:-0}

# ==============================================================================

WP_VERSION=${WP_VERSION:-"5.9"}
# Reduce to major.minor for map lookup
wp_version_key=$(echo "${WP_VERSION}" | awk -F. '{printf "%s.%s", $1, $2}')

wp_plugins=(${plugins_map[${wp_version_key}]:-})
wp_themes=(${themes_map[${wp_version_key}]:-})

if ((${#wp_themes[@]} == 0 )); then
    echo -e "\e[1;31mError:\e[0m Unsupported WordPress version ${WP_VERSION}."
    exit 1
fi

declare -A plugin_supports

plugin_supports['blocksy-companion']="${wp_plugins[0]:-2.0.86}"
plugin_supports['plugin-check']="${wp_plugins[1]:-0.2.0}"
plugin_supports['contact-form-7']="${wp_plugins[2]:-5.7.7}"
plugin_supports['jetpack']="${wp_plugins[3]:-11.2.2}"
plugin_supports['woocommerce']="${wp_plugins[4]:-7.5.2}"

declare -A theme_supports

theme_supports['blocksy']="${wp_themes[0]:-2.0.86}"

# ==============================================================================
# Executions
# ==============================================================================

if [[ ${WP_RESET:-0} -eq 1 ]]; then
    e_start "Reset WordPress Core"
    rm -rf "$INSTALL_DIR"
    echo -e "\e[1;32mSuccess:\e[0m WordPress has been reset."
    e_end
fi

if [[ ! -d "${INSTALL_DIR}" ]]; then
    e_start 'Download WordPress Core'
    result=$(_wp core download --version=${WP_VERSION} | tail -n 1)
    echo -e "$result"
    e_end
fi

if [[ ! -f "${INSTALL_DIR}/wp-config.php" ]]; then
    e_start 'Configure WordPress Core'
    _wp config create \
        --dbhost=${DB_HOST:-127.0.0.1:3306} --dbname=${DB_NAME:-wordpress} \
        --dbuser=${DB_USER:-sampleuser} --dbpass=${DB_PASS:-samplepass}
    e_end
fi

if _wp core is-installed --url="${SITE_URL}" --allow-root; then
  echo -e "\e[1;36mInfo:\e[0m WordPress is already installed."
else
    e_start 'Install WordPress Core'
    _wp core install \
        --url="${SITE_URL}" --title="${SITE_TITLE:-"WordPress Local"}" \
        --admin_user="${SITE_ADMIN_USER}" \
        --admin_password="${SITE_ADMIN_PASS:-secret}" \
        --admin_email="${SITE_ADMIN_EMAIL:-"admin@example.com"}" \
        --skip-email --allow-root
    e_end

    if [[ ! -f "$INSTALL_DIR/favicon.ico" ]]; then
        cp "$ASSET_DIR/favicon.ico" "$INSTALL_DIR/favicon.ico"
    fi
fi

# ==============================================================================

plugins_to_activate=()

if [[ -n "${SITE_PLUGINS:-}" ]]; then
    e_start 'Set up plugins'

    SITE_PLUGINS=${SITE_PLUGINS:-''}
    plugins=()

    for plugin in ${SITE_PLUGINS//,/ }; do
        if _wp plugin is-installed "$plugin"; then
            echo -e "\e[1;36mNotice:\e[0m '$plugin' is already installed."

            continue
        fi

        plugin_version="${plugin_supports[$plugin]:-}"
        if [[ "$plugin_version" == "none" ]]; then
            echo -e "\e[1;36mNotice:\e[0m Skipping '$plugin' - incompatible with WordPress ${WP_VERSION}"

            continue
        fi

        plugins_to_activate+=("$plugin")

        if [[ -n "$plugin_version" && $plugin_version != 'latest' ]]; then
            result=$(_wp plugin install "$plugin" --version="$plugin_version" | head -n 1 | sed 's/^Installing\s\(.*\)$/\1/')
            echo -e "\e[1;32mSuccess:\e[0m Installed $result"

            continue
        fi

        plugins+=("$plugin")
    done

    unset plugin result

    if [[ -f "$ASSET_DIR/init-plugins.txt" ]]; then
        while read -r plugin; do
            if [[ -n $plugin ]] && ! _wp plugin is-installed "$plugin"; then
                plugins+=("$plugin")
            fi
        done < "$ASSET_DIR/init-plugins.txt"

        unset plugin result
    fi

    if ((${#plugins[@]} != 0 )); then
        for plugin in "${plugins[@]}"; do
            result=$(_wp plugin install "$plugin" | head -n 1 | sed 's/^Installing\s\(.*\)$/\1/')
            echo -e "\e[1;32mSuccess:\e[0m Installed $result"
        done

        unset plugin result
    fi

    if [[ ${MULTISITE_ENABLED:-0} -eq 0 ]] && ((${#plugins_to_activate[@]} != 0 )); then
        for plugin in "${plugins_to_activate[@]}"; do
            result=$(_wp plugin activate "$plugin" | head -n 1)
            echo -e "\e[1;32mSuccess:\e[0m $result"
        done
    fi

    e_end
fi

# ==============================================================================

if [[ ${MULTISITE_ENABLED:-0} -eq 1 ]]; then
    e_start "Set up multisite"

    if _wp core is-installed --network; then
        echo -e "\e[1;36mNotice:\e[0m Multisite is already installed."
    else
        _wp core multisite-convert

        # https://developer.wordpress.org/advanced-administration/server/web-server/httpd/#multisite
        cat "$ASSET_DIR/.htaccess.multisite" > "$INSTALL_DIR/.htaccess"
        echo -e "\e[1;32mSuccess:\e[0m Updated '.htaccess'."
    fi

    if ((${#plugins_to_activate[@]} != 0 )); then
        for plugin in "${plugins_to_activate[@]}"; do
            result=$(_wp plugin activate "$plugin" --network | head -n 1)
            echo -e "\e[1;32mSuccess:\e[0m $result"
        done
    fi

    if [[ -n "$SITE_DEFAULT_THEME" ]] && _wp theme is-installed "$SITE_DEFAULT_THEME"; then
        _wp theme enable $SITE_DEFAULT_THEME --network
    fi

    e_end
fi

# ==============================================================================

if _wp core is-installed --network; then
    site_urls=$(_wp site list --field=url)
else
    site_urls="$SITE_URL"
fi

# ==============================================================================

e_start 'Set up themes'

if [[ -n "${SITE_THEMES:-}" ]]; then
    themes=()

    for theme in ${SITE_THEMES//,/ }; do
        if _wp theme is-installed "$theme"; then
            echo " - $theme is already installed."

            continue
        fi

        theme_version="${theme_supports[$theme]:-}"
        if [[ "$theme_version" == "none" ]]; then
            echo -e "\e[1;36mNotice:\e[0m Skipping '$theme' - incompatible with WordPress ${WP_VERSION}"

            continue
        fi

        if [[ -n "$theme_version" && $theme_version != 'latest' ]]; then
            result=$(_wp theme install "$theme" --version="$theme_version" | head -n 1 | sed 's/^Installing\s\(.*\)$/\1/')
            echo -e "\e[1;32mSuccess:\e[0m Installed $result"

            continue
        fi

        themes+=("$theme")
    done

    unset theme result

    if [[ -f "$ASSET_DIR/init-themes.txt" ]]; then
        while read -r theme; do
            if [[ -n $theme ]] && ! _wp theme is-installed "$theme"; then
                themes+=("$theme")
            fi
        done < "$ASSET_DIR/init-themes.txt"

        unset theme result
    fi

    if ((${#themes[@]} != 0 )); then
        for theme in "${themes[@]}"; do
            result=$(_wp theme install "$theme" | head -n 1 | sed 's/^Installing\s\(.*\)$/\1/')
            echo -e "\e[1;32mSuccess:\e[0m Installed $result"
        done

        unset theme result
    fi
fi

if [[ -n "$SITE_DEFAULT_THEME" ]] && _wp theme is-installed "$SITE_DEFAULT_THEME"; then
    for site_url in $site_urls; do
        site_title=$(_wp --url="$site_url" option get blogname)

        result=$(_wp --url="$site_url" theme activate $SITE_DEFAULT_THEME | head -n 1)
        echo -e "$result ($site_title)"
    done
fi

e_end

# ==============================================================================

for site_url in $site_urls; do
    site_title=$(_wp --url="$site_url" option get blogname)

    if [[ "$SKIP_MEDIA" -eq 0 ]]; then
        e_start "Set up media:\e[1;0m $site_title"

        assets=()

        for ext in ${MEDIA_EXTS//,/ }; do
            for asset in "$MEDIA_DIR"/*.$ext; do
                if [[ -f "$asset" ]]; then
                    assets+=("$asset")
                fi
            done
        done

        if [[ -f "$ASSET_DIR/init-assets.txt" ]]; then
            while read -r asset; do
                if [[ -n $asset ]]; then
                    # $asset: can be url from or file path relative to root project
                    # As it supported by `wp media import`
                    assets+=("$asset")
                fi
            done < "$ASSET_DIR/init-assets.txt"
        fi

        unset asset

        for media in "${assets[@]}"; do
            filename=$(basename "$media")

            media_id=$(_wp --url="$site_url" media import "$media" --porcelain)

            if [[ "$filename" == "$SITE_ICON_FILENAME" ]]; then
                options['site_icon']=$media_id
            fi

            echo -e "\e[1;32mSuccess:\e[0m '\e[0;33m$filename\e[0m' imported (ID: \e[1;33m$media_id\e[0m)"
        done

        e_end
    fi

    if [[ "$SKIP_OPTIONS" -eq 0 ]]; then
        e_start "Set up options:\e[1;0m $site_title"

        for key in "${!options[@]}"; do
            _wp --url="$site_url" option update "$key" "${options[$key]}"
        done

        if _wp --url="$site_url" plugin is-active woocommerce || _wp --url="$site_url" plugin is-active woocommerce --network; then
            for key in "${!woo_options[@]}"; do
                format=$([[ "${woo_options[$key]}" == '['*']' ]] && echo 'json' || echo 'plaintext')

                _wp --url="$site_url" option update "woocommerce_$key" "${woo_options[$key]}" --format="$format"
            done

            # Skip the onboarding profile
            timestamp="{\"completed_at\":\"$(date -u +'%Y-%m-%dT%H:%M:%SZ')\"}"
            _wp --url="$site_url" option update woocommerce_onboarding_profile '{"skipped":true}' --format=json
            _wp --url="$site_url" option update woocommerce_onboarding_profile_progress \
                "{\"core_profiler_completed_steps\":{\"intro-opt-in\":${timestamp},\"skip-guided-setup\":${timestamp}}}" --format=json
            unset timestamp

            # Install default woocommerce pages
            _wp --url="$site_url" wc --user="$SITE_ADMIN_USER" tool run install_pages
        fi

        unset key

        e_end
    fi
done

unset options woo_options site_url site_urls

# ==============================================================================

if [[ -n "${TRIM_PLUGINS:-}" ]]; then
    e_start 'Cleanup'

    TRIM_PLUGINS=${TRIM_PLUGINS:-''}
    to_removes=()

    for to_remove in ${TRIM_PLUGINS//,/ }; do
        if _wp plugin is-installed "$to_remove"; then
            to_removes+=("$to_remove")
        fi
    done

    if ((${#to_removes[@]} != 0 )); then
        _wp plugin uninstall ${to_removes[@]}
    fi

    e_end
fi

# ==============================================================================

e_start 'Verify installation'
_wp core version --extra
echo "Site URL: ${SITE_URL}"
e_end
