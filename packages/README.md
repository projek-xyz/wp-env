# 📦 Packages

This directory is the dedicated location for custom WordPress themes and plugins.

## 🛠 Contribution Guidelines

To ensure the local development environment remains organized and functional, all additions to this directory MUST follow these rules:

1.  **Flat Directory Structure**: Every theme or plugin must be placed directly under the `packages/` directory.
    - ✅ `packages/my-custom-theme/`
    - ❌ `packages/themes/my-custom-theme/`
2.  **WordPress Conventions**: Follow standard WordPress metadata requirements for themes (`style.css` header) and plugins (primary PHP file header).
3.  **Manual Mounts**: Any new package must be manually added as a volume mount in `compose.yaml` to be visible within the WordPress container.
4.  **Workspaces Only**: Do not commit `node_modules` or `vendor` directories within your package. Use the root `package.json` (workspaces) and `composer.json` (merge-plugin) for dependency management.

## 🤖 Automation Features

- **Favicon Synchronization**: During initialization, the `cli` service automatically copies the `favicon.ico` from the `public/` directory to the WordPress site root. This ensures your custom branding is applied consistently across the local environment.

## 📦 Dependency Management

This project handles dependencies at the root level using a monorepo approach:

- **JS/Assets**: All `package.json` files within `packages/*` are automatically managed by **Bun Workspaces**. Dependencies are hoisted to the root `node_modules` where possible.
- **PHP**: All `composer.json` files within `packages/*` are automatically discovered and merged by the root **Composer** configuration using the `wikimedia/composer-merge-plugin`. This allows for a single `vendor` directory and unified autoloading.

## 📁 Current Packages

### Themes
- **custom-theme**: A starter theme for local development.

### Plugins
- **blank-option**: A starter plugin for local development.
