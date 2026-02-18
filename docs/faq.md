## FAQ

**Who can use this plugin?**

Only Super Users have access to the Bulk Site Delete page and API.

**Is deletion reversible?**

No. Deleting a site permanently removes it and all associated tracking data. Always double-check before confirming.

**Does this plugin bypass Matomo's deletion logic?**

No. It delegates to Matomo's core `SitesManager.deleteSite` API for each site, so all standard checks and cleanup apply.

**Can I use this from the command line?**

Yes. Run `./console site:delete-bulk` with site IDs as arguments. Supports individual IDs, comma-separated lists, and ranges like `10-20`. Add `--force` to skip the confirmation prompt.
