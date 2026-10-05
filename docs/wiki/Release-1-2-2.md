# Release 1.2.2

> Documentation baseline: release 1.2.2 (`v1.2.2`). Last reviewed: 2026-10-05.

Version 1.2.2 fixes [#14](https://github.com/mage-os-lab/module-shopping-feed/issues/14): malformed attribute quotes no longer leave tags in descriptions or swallow following headings. Literal comparisons, quoted comparison attributes, escaped entities, and limits applied after cleaning retain coverage. The release also includes the pricing, backorder, and Page Builder fixes from [1.2.1](Release-1-2-1).

## Migrating from Rocket Shopping Feeds

Install the separate [Rocket Web migration companion](https://github.com/rocketweb/module-shopping-feed-migration-rocketweb), published as `rocketweb/module-shopping-feed-migration-rocketweb` 1.0.0 on [Packagist](https://packagist.org/packages/rocketweb/module-shopping-feed-migration-rocketweb). Follow the [installation and migration guide](Rocket-Web-Migration).

The companion previews and explicitly imports reviewed configuration into disabled Mage-OS feeds. Schedules and uploads remain held until separately reviewed activation. Keep legacy modules enabled through schema upgrades, compare actual output, and review custom PHP, shared settings, and recipient URLs. Installing or upgrading the main module does not run a migration.

## Upgrade and validation

There is no schema change or new required dependency relative to 1.2.1. See the [upgrade checklist](Installation-and-Upgrade#upgrading-from-121-to-122).

The local unit suite passes 863 tests on Mage-OS 3.5, Magento 2.4.8, and Magento 2.4.7-p10, covering PHPUnit 12, 10, and 9. Seventeen new cases include nine observed failures against the earlier runtime. All 47 JavaScript checks pass, and the merged runtime passed all 25 [post-merge CI jobs](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37332361658). Existing-store, browser/operational, and recipient checks retain their exact earlier scopes; see [Release acceptance](Release-Acceptance).
