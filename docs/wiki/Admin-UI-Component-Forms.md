# Admin UI Component forms

Version 1.2.0 replaces the default New/Edit Feed and Test Feed forms with Magento UI Components. Version 1.1.0 retains the previous editor. This guide describes the 1.2.0 controls and upgrade implications.

> Documentation baseline: release 1.2.1 (`v1.2.1`); earlier acceptance is identified by version. Last reviewed: 2026-10-04.

Existing feeds with a blank saved currency display the currency currently used by generation. Saving makes that selection explicit without changing prices to a different store default. New feeds still start with the store default; explicit saved selections remain unchanged.

## Current acceptance status

Version 1.2.0 repairs the defects found in `9f07e46`. Disposable Mage-OS browser tests now pass new-category save-to-generation and all four promotion dates through two saves plus companion generation. Header/category validation, preview input handling, and hidden microdata defaults are also repaired. The repository's `docs/reviews/2026-10-02-ui-component-editor-fixes.md` records the evidence and limits.

The repaired PHP suite passes on the Mage-OS, Magento Open Source 2.4.8, and Magento Open Source 2.4.9 frameworks. Actual framework date conversion passed US, British, and German locale tests on all three. Production-mode Docker installations of both Magento versions now pass create/save/reopen for all eight presets, category and promotion output, dynamic rows, failed-save recovery, and malformed-request checks. These tests also found and repaired loss of null directive defaults. The repository's `docs/reviews/2026-10-02-magento-docker-acceptance.md` records the current evidence. The follow-up in `docs/reviews/2026-10-02-grid-permission-acceptance.md` verifies permission-filtered controls and server-side denials with six role profiles on both versions. Earlier automated results did not catch these defects, so they are preserved as historical evidence rather than substituted for the new regression checks.

The release runtime `133af71` is deployed on `mageos-latest`, including the preview, feed stock-selection, configurable Local Inventory, frontend store-scope, and currency corrections found during acceptance. Eight-preset save/reopen, unchanged-save, CLI preview, full generation, category mapping, promotion dates, cloning, and deletion checks passed on Mage-OS 3.5.0. The repository's `docs/reviews/2026-10-03-local-acceptance.md` records the final candidate, preserved original data, extended tests, and remaining checks. Updating code does not reconstruct promotion dates already erased by an earlier candidate.

Column names must be single-line and contain no control characters. Invalid category rows and malformed preview requests now produce recoverable Admin errors. Custom configuration keys remain supported; see [Columns and directives](Columns-and-Directives) and [Development and CI](Development-and-CI).

Magento Open Source 2.4.7-p10 uses the same UI Component forms on PHP 8.3. Eight-preset save/output comparisons, category and promotion editing, DynamicRows, preview recovery, and six-role checks pass without runtime changes. Its separate acceptance and upstream Flysystem dependency guidance are linked from [Status and compatibility](Status-and-Compatibility).

## Using the editor

Open **Catalog > Mage-OS Shopping Feed > Feeds Management**. Use **Create New Feed** to select a preset, or **Configure** to edit a saved feed.

The editor uses collapsible sections:

| Section | Purpose |
| --- | --- |
| General | Name, store, currency, output path and filename, delimiter, stock settings, and type-specific options |
| Columns Map | Output columns, attributes/directives, parameters, and explicit Order values |
| Categories Map | Category selection, priority, taxonomy, and product type |
| Product Filters | Product eligibility, replacement rules, output limits, and applicable type-specific filters |
| Product Options | Custom-option handling |
| Configurable Products, Grouped Products, Bundle Products | Parent/child handling and applicable value inheritance |
| Shipping | Shipping directive configuration |
| Schedule | Generation times and batch settings |
| Uploads | FTP/SFTP destinations and gzip |
| Google Promotions | Included cart rules, titles, dates, and promotion counter for Google Shopping only |

Google Local Inventory hides Product Options and Shipping and changes category/inventory fields. Google Promotions is a section of Google Shopping, not a ninth New Feed preset. Version 1.2.0 contains eight presets; see [Feed types and lifecycle](Feed-Types-and-Lifecycle).

**Save** returns to the feed list. The adjacent dropdown provides **Save and Continue Edit**. After changing **Store View**, save and reload before editing category or attribute choices. **Back** leaves the editor. A saved feed also has **Test Feed** and **Delete Feed**, subject to the Admin role's permissions. Individual deletion asks for confirmation.

Repeated configuration uses **Add** and row deletion controls. Mapping rows use their explicit **Order** value, including duplicate values; other repeated rows can be reordered by dragging. Remove the last schedule or upload row and save to clear that collection. New feeds can include a default schedule, so inspect Schedule before the first save on a shared store.

Saved upload passwords display a mask. Leave it unchanged to retain the existing secret. After a failed save, re-enter any new or changed password; it is deliberately omitted from recovered form data. See [Uploads](Uploads).

## Grid actions and output

**Test Feed** accepts a SKU or product ID and runs through **Test Now**. A preview is not a full-file check or a Promotions companion-file test. **Run Now** queues generation; cron or an explicit CLI generation command must process it. **View Log** opens existing generation history. See [Testing one product](Testing-One-Product) and [Manual and CLI generation](Manual-and-CLI-Generation).

Create New Feed, Configure, Enable, Disable, and Clone require the save permission. Run Now requires generate permission; bulk Delete requires delete permission. Read-only users retain Test Feed, View Log, and Export. The Actions menu is hidden when no permitted bulk action remains.

Bulk **Delete** executes immediately for the selected feeds in the standard grid, without a confirmation dialog. Verify the selected count and names first. Clone creates a disabled feed and copies configuration, schedules, and upload destinations. A literal filename is copied unchanged, so review the output destination before enabling the clone. A filename containing `%s` substitutes the new feed ID. These behaviors predate the form migration.

The local repair defaults new Google Shopping feeds to microdata and other new presets to zero. Existing saved selections and explicit selections are preserved. The old candidate could silently select a non-Google preset without displaying a **Use for microdata** control; no existing store selections are migrated by this repair. Inspect the grid's `[microdata]` marker when upgrading. See [Automatic updates and schema.org](Automatic-Updates-and-Schema-org).

## Existing editor customizations

No schema or bulk feed-data migration is required by this editor change. Existing routes, configuration keys, server-side permissions, and the prepare-save event remain. Custom integrations with the old editor need review:

* PHP `prepare_form_*` observers and plugins on legacy tab blocks no longer customize the default form.
* Use a `ModifierInterface` implementation in `ShoppingFeedFormModifierPool` to add field metadata and data.
* Custom directive parameter controls use declarative definitions or replacement UI components. Arbitrary PHP/PHTML parameter renderer output is not executed.

The module's six former form observers and two tab-visibility plugins have already been replaced by metadata behavior. This does not mean every store has third-party customizations; the upgrade work applies to sites that added them. The detailed contract is in the repository's `docs/ui-component-editor.md`. Retained legacy files do not provide a selectable old-editor mode.

## Nebula and the standard Admin

The form uses one shared metadata and persistence implementation, with no required Nebula dependency. It removes the active editor's dependence on legacy PHP form widgets and custom dependency handling, reducing the work a future theme adapter must reproduce.

The optional Nebula grid integration, `FeedEditorTheme` fallback, and menu-cache handling still exist. Feed editing, previews, and logs continue in Magento/backend on Nebula installations. Native rendering through a Nebula UI Bridge has not been accepted, and the inspected Nebula 0.9.0 package does not include that bridge. Most of the approximately 575 lines counted in the original Nebula integration concern the separate grid; this migration does not remove them.

Switching Admin themes does not switch Shopping Feed editors. The inspected Nebula 0.9.0 package's `ForceAdminTheme` plugin forces its theme while enabled; its Nebula settings contain layout/skin options, not a master enable/disable field. Use the installed version's deployment procedure for an Admin-theme rollback. The local acceptance target had all 16 Nebula modules disabled and used Magento/backend throughout this run.

## Verification and rollback

Test the exact repaired revision with a disposable feed through save, reopen, unchanged save, preview, full generation, and file parsing. Category tests must create a new mapping and then generate a product that uses it. Promotion tests must compare all four dates after each save and in the companion output. Repeat on Magento Open Source 2.4.8, 2.4.9, and the intended Mage-OS environment.

Also test site-specific modifiers, restricted Admin roles, failed saves, password retention, deletion of final rows, cloning, and the Admin theme actually used by the store. Private receiver tests and a 5,000-product synthetic envelope supplement the form checks. Use the current record for hourly cron and cleanup results; external recipient acceptance and a representative production-catalog test remain separate requirements.

Restoring the previous package and rebuilding DI/static assets restores the old editor. There is no per-feed configuration switch. A code rollback does not recover erased promotion dates or repair malformed category data; compare with the pre-upgrade backup and restore affected configuration through a reviewed recovery. See [Installation and upgrade](Installation-and-Upgrade) and [Release acceptance](Release-Acceptance).
