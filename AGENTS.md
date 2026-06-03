# Agent Guidelines & Project Context

This file serves as a persistent context for AI agents working in this project. All agents MUST read and adhere to these guidelines to ensure consistency and prevent environment breakage.

## 🏗 Project Identity

A Dockerized WordPress local development environment using Apache, MySQL 8.0, and automated initialization via WP-CLI.

## 🛠 Operational Mandates

1.  **Environment Variables**: The project is strictly dependent on a `.env` file. NEVER hardcode values in `compose.yml`. Verify required variables (see `README.md`) before proposing changes.
2.  **Service Lifecycle**: Always use `docker compose` for starting/stopping services.
3.  **WP-CLI Management**: Use the dedicated `cli` service for all WordPress commands.
    *   Command pattern: `docker compose run --rm cli wp <command>`
4.  **Volumes & Persistence**: Database data is in `docker/volumes/mysql`, and site files are in `docker/volumes/wordpress`.
5.  **Metadata Management**: ALL AI-generated metadata (plans, specs, and design documents) MUST be stored exclusively in the `.agents/` directory (e.g., `.agents/plans/`, `.agents/specs/`). Do not use any other directory for persistent or temporary agent artifacts.

## 📁 Development Guidelines

1.  **Themes/Plugins**: Prefer creating custom themes/plugins in `packages/` and mounting them into `docker/volumes/wordpress/wp-content/` if intended for portability.
2.  **Initialization**: Changes to site titles, admin users, or pre-installed plugins should be implemented in `scripts/init-wp.sh`. Additional plugins and themes can also be listed in `assets/init-plugins.txt` and `assets/init-themes.txt` for bulk installation during setup.
3.  **Package Mounting**: When adding new themes or plugins, update the `x-packages` YAML anchor in `compose.yml` instead of adding manual volume mounts to individual services.

## ⚙️ Essential Commands

### Environment Lifecycle
- **Start**: `docker compose up -d`
- **Stop**: `docker compose down`
- **Reset**: `docker compose down -v` (Wipes all data)
- **Logs**: `docker compose logs -f cli` (Monitor installation)
Once the services are up, it will be reachable at `SITE_URL` defined in `.env`.

### WP-CLI Usage
All WordPress commands must go through the `cli` service:
```bash
docker compose run --rm cli wp <command>
```
Examples:
- `docker compose run --rm cli wp plugin list`
- `docker compose run --rm cli wp post create --post_type=page --post_title='Test' --post_status=publish`
- `docker compose run --rm cli wp option get siteurl`

Alternatively a local `wp-cli` is available via `vendor/bin/wp`.

### Testing
All tests should be run locally:
- **Run all tests**: `composer test`
- **Run unit tests only**: `composer test:unit` to run whole unit test suite, or use `composer test:unit -- --filter <TestName>` to run individual unit test file, method or group
- **Run integration tests only**: `composer test:integration` to run whole integration test suite, or use `composer test:integration -- --filter <TestName>` to run individual integration test file, method or group
- **Generate coverage report**: Tests automatically generate coverage in `tests/reports/`

Tho the integration tests requires the `db` service to be up and runing before tests. It will be connected via `FORWARD_DB_PORT` defined in `.env.testing`.

### Code Quality
- **Lint PHP (packages)**: `composer lint:packages`
- **Lint PHP (tests)**: `composer lint:tests`
- **Format PHP (packages)**: `composer format:packages`
- **Format PHP (tests)**: `composer format:tests`
- **Run all linting**: `composer lint`

### Dependency Management
- **Install PHP dependencies**: `composer install`
- **Install JS dependencies**: `bun install`
- **Update PHP dependencies**: `composer update`

## 📦 Working with Packages

### Adding a New Local Plugin
1. Create directory: `packages/my-plugin`
2. Add plugin files (main file should be `packages/my-plugin/my-plugin.php`)
3. Update `x-packages` anchor in `compose.yml`:
   ```yaml
   x-packages: &packages
     - ./packages/my-plugin:/var/www/html/wp-content/plugins/my-plugin
     - ./packages/blank-option:/var/www/html/wp-content/plugins/blank-option
     - ./packages/custom-theme:/var/www/html/wp-content/themes/custom-theme
   ```
4. Run `docker compose up -d` to apply changes
5. Activate via WP-CLI: `docker compose run --rm cli wp plugin activate my-plugin`

### Adding a New Local Theme
1. Create directory: `packages/my-theme`
2. Add theme files (must include `style.css` and `index.php` or `functions.php`)
3. Update `x-packages` anchor in `compose.yml` (same as above)
4. Run `docker compose up -d` to apply changes
5. Activate via WP-CLI: `docker compose run --rm cli wp theme activate my-theme`

### Installing Official Plugins/Themes
Add slugs to `.env`:
```bash
SITE_PLUGINS=woocommerce,contact-form-7,akismet
SITE_THEMES=twentytwentythree
```
Or use bulk installation files:
- `assets/init-plugins.txt` (one slug per line)
- `assets/init-themes.txt` (one slug per line)

### Commit Conventions
- Commit messages must follow [Conventional Commits](https://www.conventionalcommits.org/): `feat:`, `fix:`, `chore:`, `ci:`, `docs:`, `refactor:`, `perf:`, `test:`, `build:`, `revert:`.
- Commitlint runs on every commit (via simple-git-hooks + lint-staged).
- Lint-staged auto-formats staged JS/TS/CSS/JSON with Biome and PHP with phpcbf.

## 🧪 Testing Structure

- **Unit Tests**: `tests/units/` - Tests individual functions/classes in isolation
- **Integration Tests**: `tests/integrations/` - Tests requiring WordPress bootstrapped
- **Fixtures**: `tests/fixtures/` - Helper functions and test data
- **Bootstrap**: `tests/bootstrap.php` - Sets up WordPress testing environment
- **Configuration**: `tests/integrations/wp-tests-config.php` - Test database settings
- **Environment File**: `.env.testing` - Environment variables used during testing

Tests follow PHPUnit conventions and use Brain Monkey for mocking WordPress functions.

## 🔧 Important Scripts

- `scripts/init-wp.sh` - Main WordPress installation and configuration script
- `scripts/make-pot.sh` - Generates translation files (.pot) for packages
- `scripts/make-dist.sh` - Creates production-ready ZIP archives (requires `.distignore` in package)
- `scripts/_util.sh` - Utility functions used by other scripts

## 📝 Persistent Memory (Context)

- **Date**: 2026-06-04
- **Status**: Environment infrastructure uses service inheritance and YAML anchors. Documentation reflects the `web` service and improved package mounting workflow.
- **Next Steps**: Continue maintaining strict PSR-12 and WordPress coding standards across all packages and tests.
