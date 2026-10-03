# Feed types and lifecycle

The feed type provides a starting column map and default behavior. All feeds then move through the same save, test, queue, generation, upload, and review lifecycle.

> Documentation baseline: release `v1.1.0`, with explicitly marked unreleased `133af71` editor notes. Last reviewed: 2026-10-03.

The unreleased [UI Component editor](Admin-UI-Component-Forms) uses collapsible sections instead of the 1.1 tabs. Its category and promotion-date defects are repaired and verified locally. The linked guide records the deployed candidate, currency preservation, and remaining release boundaries.

The 1.2 development branch includes the four Tier 1 additions: [Meta Catalog](Meta-Catalog), [Microsoft Merchant Center](Microsoft-Merchant-Center), [TikTok Catalog](TikTok-Catalog), and [Pinterest Catalog](Pinterest-Catalog). The same branch adds the [OpenAI / ChatGPT Google-compatible beta](OpenAI-ChatGPT). All additions are unreleased and require destination validation before production use.

## Feed types

| Feed | Use it for |
| --- | --- |
| Generic | Custom delimited feeds for systems without a bundled template |
| Google Shopping | Google product data, variants, taxonomy, shipping, and product-linked promotion IDs |
| Google Local Inventory | Store-level availability, quantity, and price, optionally expanded by MSI source |
| Google Promotions | A companion file configured and generated from a Google Shopping feed |
| Meta Catalog (1.2 development) | Facebook and Instagram product catalogs using a scheduled TSV data feed |
| Microsoft Merchant Center (1.2 development) | Microsoft Shopping product catalogs using tab-delimited TXT |
| TikTok Catalog (1.2 development) | TikTok Ads Manager product catalogs using a scheduled CSV feed |
| Pinterest Catalog (1.2 development) | Pinterest retail catalogs and product Pins using a scheduled TSV feed |
| OpenAI / ChatGPT (Google-compatible, beta, 1.2 development) | TSV product discovery feed requiring confirmation during OpenAI onboarding |

See the individual feed guides before relying on their default columns.

These templates generate advertising and catalog feeds. Marketplace listing management and order synchronization for Amazon, Walmart, or eBay require separate integrations.

## Grid actions

The Feeds Management grid exposes these per-feed actions:

* **Configure** opens the feed editor.
* **Run Now** adds the feed to the generation queue.
* **Test Feed** renders one SKU or product ID without replacing the normal production file.
* **View Log** opens the per-feed log.

The grid also supports mass enable, disable, clone, and delete operations when the Admin role has the required ACL permissions. In the standard grid, mass **Delete** executes immediately without a confirmation dialog. Verify the selected names and count before choosing it. Individual **Delete Feed** in the editor has a confirmation dialog.

## Statuses

| Status | Meaning |
| --- | --- |
| Disabled | The feed is not active for normal operation. |
| Scheduled | The feed has saved schedule configuration. |
| Pending | Work is waiting in the generation queue. |
| Processing | A worker is generating the feed. |
| Completed | The last generation completed. Review the file and counts. |
| Error | Processing failed. Open the feed log before retrying. |

## Recommended lifecycle

1. Create the feed from the closest template.
2. Save its general settings and column map.
3. Test representative products.
4. Generate the complete file without an upload destination.
5. Compare product counts and sampled data with Magento.
6. Validate the file with the intended recipient.
7. Add one non-serving upload destination and test it.
8. Add schedules only after the output and transfer are accepted.
9. Record the accepted commit, configuration, file checksum, and external result.

## Clone carefully

Cloning creates a new disabled feed with an `_clone` name and copies its configuration, schedules, and upload destinations. It does not guarantee a distinct output filename: a literal filename is copied unchanged and can overwrite the source feed's file when the clone is generated. A filename containing `%s` substitutes the new feed ID. Review the filename, store view, schedules, and destinations before enabling or generating the clone. This behavior predates the UI Component migration.
