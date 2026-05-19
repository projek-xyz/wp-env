![CodeRabbit](https://img.shields.io/coderabbit/prs/github/projek-xyz/wp-env?style=flat-square)
![Codecov](https://img.shields.io/codecov/c/github/projek-xyz/wp-env?style=flat-square)

# 📦 WordPress Evaluation Environment

**Zero-config, Docker-based local environment for rapid theme and plugin evaluation.**

Spin up a fully installed WordPress site in seconds, bypassing the setup wizard entirely. This environment is pre-tuned for high-speed testing of themes, plugins, and complex setups like WooCommerce or Multisite.

### ⚡ Key Features
- 🚀 **Zero-Config**: Fully installed WordPress site via `docker compose up`.
- 🛍️ **WooCommerce Ready**: Automatic store setup and configuration.
- 🌐 **Multisite Support**: One-click conversion to a subfolder network.
- 📧 **Mailpit Integrated**: Instant email capture and testing dashboard.
- 🛠️ **Monorepo Structure**: Manage multiple themes and plugins in one project.

## 🚀 Quick Start

1. **Configure**: `cp .env.example .env`
2. **Start**: `docker compose up -d`
3. **Evaluate**: Visit [http://localhost:8080](http://localhost:8080)

> **Default Credentials:**  
> **User:** `admin` | **Password:** `password`

## 🔋 Built-in Evaluation Tools

### ⚡ Automated Installation
The environment uses a custom `init-wp.sh` engine to handle everything:
- Database creation and site installation.
- Admin user creation and plugin/theme activation.
- WooCommerce and Multisite configuration.
- **Plugin Cleanup**: Use `TRIM_PLUGINS` in `.env` to surgically remove default or unwanted plugins during setup.
- **Must-Use Plugins**: Support for `mu-plugins` via the `docker/mu-plugins` directory.
- **Bulk Installation**: Support for installing multiple plugins and themes via `scripts/init-plugins.txt` and `scripts/init-themes.txt`.

### 🛍️ WooCommerce Integration
If `woocommerce` is in `SITE_PLUGINS`, the store is automatically configured:
- Sets currency, units, and address via `.env`.
- Skips the onboarding wizard for a "Ready-to-Evaluate" experience.

### 🌐 Multisite-on-Demand
Convert your site to a **Multisite Network** by setting `MULTISITE_ENABLED=1`. The `cli` service handles the migration and `.htaccess` updates automatically.

### 📧 Email Testing (Mailpit)
All outgoing emails are captured by **Mailpit**.
- **Dashboard:** [http://localhost:8025](http://localhost:8025)
- All local packages are automatically routed to the internal mail service.

## 🔌 Evaluating Your Assets

### 📁 Local Packages
Place your theme or plugin folder in the [`packages/`](packages/) directory. They are automatically discovered and can be managed via the root toolset.

#### 🏗️ Adding a New Package
1. **Create Directory**: `mkdir packages/my-new-plugin`
2. **Mount Volume**: Update the `x-packages` anchor at the top of [`compose.yml`](compose.yml). This automatically syncs the volume across both `web` and `cli` services.
3. **Register PHP Dependencies**: Ensure the package has a `composer.json`. Run `composer install` at the project root to merge its dependencies.
4. **Register Assets**: Ensure the package has a `package.json`. Run `bun install` at the root to register its workspace.

### 🌐 Official Repository
Add slugs to `SITE_PLUGINS` or `SITE_THEMES` in your `.env`:
```bash
SITE_PLUGINS=akismet,woocommerce,contact-form-7
```

> **Note:** For bulk installation, you can also list slugs (one per line) in `scripts/init-plugins.txt` or `scripts/init-themes.txt`.

## 🏗️ Project Architecture

This project is organized as a **monorepo** and uses a modular Docker configuration to simplify the development of multiple WordPress assets simultaneously.

### 🐳 Docker Configuration
The environment uses a base configuration in `docker/compose.base.yml` which is extended by the root `compose.yml`. This separation allows for a clean, reusable base image and service definitions while keeping local development overrides isolated.

### 📂 Directory Structure
- [`assets/`](assets/): Static assets, favicon, and server configurations.
- [`docker/`](docker/): Local MySQL data and service configurations.
- [`packages/`](packages/): Local themes and plugins.
- [`scripts/`](scripts/): Development utilities (Installation, POT generation, Distribution).

## 🛠️ Development Tools

### 📦 Dependency Management
- **Bun**: Manages root tools and JS workspaces.
- **Composer**: Manages PHP dependencies with automated package merging.

### 🎨 Linting & Formatting
- **Biome**: For JS, TS, JSON, and CSS (`bun lint`).
- **PHPCS**: All WordPress packages located in [`packages/`](packages/) directory should follow [WordPress](https://developer.wordpress.org/coding-standards/wordpress-coding-standards/php/) Coding Standards (`composer lint:packages`), while the [`tests/`](tests/) should use [`PSR12`](https://www.php-fig.org/psr/psr-12/) Coding Standards (`composer lint:tests`).

### 📦 Scripts
- [`scripts/init-wp.sh`](scripts/init-wp.sh): Dynamically download, configure, and install a fresh WordPress core.
- [`scripts/make-pot.sh`](scripts/make-pot.sh): Generates translation files for all packages.
- [`scripts/make-dist.sh`](scripts/make-dist.sh): Creates production-ready ZIP archives. Requires a `.distignore` file in the package directory to filter contents.

### 🐞 Debugging & Inspections
- **Xdebug**: Pre-installed and configured for PHP 8.1+. Connect your IDE to the `9003` port on your local machine.
- **WP-CLI**: Access the containerized CLI via the `cli` service.
  ```bash
  docker compose run --rm cli wp <command>
  ```
- **Database Access**: Connect to the local MySQL server at `localhost:3306` using the credentials defined in `.env`.

## 🧪 Testing & Quality Assurance

This project includes a robust testing infrastructure for both unit and integration tests.

### 🛠️ Testing Stack
- **PHPUnit 10.5**: The core testing framework.
- **Brain Monkey**: For advanced WordPress hook and function mocking.

### 🚀 Running Tests Locally
Ensure you have installed development dependencies via `composer install`.
```bash
composer test
```
This will execute all test suites defined in [`phpunit.xml`](phpunit.xml) and output a text-based coverage report. Tests are designed to run on the host machine using the root `vendor/bin/phpunit` binary.

### 🔄 Continuous Integration (CI)
Automated testing is integrated into the development lifecycle via **GitHub Actions** ([`.github/workflows/main.yml`](.github/workflows/main.yml)):
- **Matrix Testing**: Every Pull Request and Push to `main` is automatically tested across multiple **PHP** and **WordPress** versions.

## ⚙️ Lifecycle & Configuration

### Lifecycle Commands
- **Start**: `docker compose up -d`
- **Stop**: `docker compose down`
- **Reset**: `docker compose down -v` (Wipes all data)
- **Logs**: `docker compose logs -f cli` (Monitor installation)

### Environment Variables
See [.env.example](.env.example) for a full list of available settings including site titles, admin credentials, and WooCommerce/Multisite options.

## ⚖️ Licensing

This project uses a **hybrid licensing model**:
- **Environment & Tools**: [MIT License](LICENSE-MIT).
- **WordPress Packages**: [GPLv3 or later](LICENSE-GPL).

This ensures the platform is free to use while ensuring all distributable assets remain compliant with the WordPress ecosystem.
