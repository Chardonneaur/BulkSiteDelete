# Bulk Site Delete

Adds a dedicated admin page to select and delete multiple Matomo websites at once.

> **Warning**
>
> This plugin is experimental and was coded using [Claude Code](https://claude.ai).
> It is provided without any warranty regarding quality, stability, or performance.
> This is a community project and is not officially supported by Matomo.

## Description

Manage large Matomo installations more efficiently. This plugin adds an admin page where Super Users can browse all websites in a searchable table, select multiple sites via checkboxes, and delete them in a single bulk action with a confirmation dialog.

Also includes a CLI command (`./console site:delete-bulk`) for scripted or large-scale deletions.

### Features

- Browse all websites in a searchable, sortable table
- Select multiple sites via checkboxes (with select all / deselect all)
- Filter sites by name, URL, or ID
- Confirmation dialog listing all selected sites before deletion
- Success/error notifications after the operation
- CLI command with support for ranges (`10-20`), comma-separated IDs, and `--force` flag

## FAQ

**Who can use this plugin?**

Only Super Users have access to the Bulk Site Delete page and API.

**Is deletion reversible?**

No. Deleting a site permanently removes it and all associated tracking data. Always double-check before confirming.

**Does this plugin bypass Matomo's deletion logic?**

No. It delegates to Matomo's core `SitesManager.deleteSite` API for each site, so all standard checks and cleanup apply.

## Requirements

- Matomo >= 5.0
- PHP >= 8.1
- Super User access

## Installation

### From Matomo Marketplace
1. Go to Administration > Marketplace
2. Search for "BulkSiteDelete"
3. Click Install

### Manual Installation
1. Download the latest release from GitHub
2. Extract to your `matomo/plugins/` directory
3. Activate: `./console plugin:activate BulkSiteDelete`

## Usage

### Web interface
1. Log in as a Super User
2. Go to Administration > Measurables > Bulk Site Delete
3. Select the websites you want to delete
4. Click "Delete selected sites"
5. Review the confirmation dialog and confirm

### CLI
```bash
./console site:delete-bulk 5 12 23
./console site:delete-bulk 10-20
./console site:delete-bulk 5,12,23 --force
```

## Changelog

See [CHANGELOG.md](CHANGELOG.md).

## License

GPL-3.0+. See [LICENSE](LICENSE) for details.
