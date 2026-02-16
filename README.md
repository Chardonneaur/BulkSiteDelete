# Bulk Site Delete

## Description

Adds a dedicated admin page where Super Users can select multiple Matomo websites and delete them in bulk with a single action. Includes a confirmation dialog to prevent accidental deletion.

## Features

- Browse all websites in a searchable, sortable table
- Select multiple sites via checkboxes (with select all / deselect all)
- Filter sites by name, URL, or ID
- Confirmation dialog listing all selected sites before deletion
- Success/error notifications after the operation

## Requirements

- Matomo 5.0 or later
- PHP 8.1 or later
- Super User access

## Installation

### Via Marketplace (recommended)
1. Go to Administration > Marketplace
2. Search for "Bulk Site Delete"
3. Click Install

### Manual installation
1. Download the latest release
2. Extract to your Matomo `plugins/` directory
3. Activate: `./console plugin:activate BulkSiteDelete`

## Usage

1. Log in as a Super User
2. Go to Administration > System > Bulk Site Delete
3. Select the websites you want to delete
4. Click "Delete selected sites"
5. Review the confirmation dialog and confirm

## License

GPL v3 or later
