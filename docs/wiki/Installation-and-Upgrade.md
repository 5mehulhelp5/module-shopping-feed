# Installation and upgrade

Install the module on staging first. Feed generation writes files and records queue, schedule, upload, and status data. A saved upload can also transfer completed files to an external system.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

## Before installation

1. Confirm the target Mage-OS or Magento Open Source and PHP versions are supported.
2. Record the exact module commit or package version.
3. Back up the database and relevant media and configuration files.
4. Confirm Magento cron is healthy.
5. If a Rocket Web feed package is installed, read [Migration and coexistence](Migration-and-Coexistence).
6. Plan to keep new schedules and uploads disabled until a generated file has been reviewed.

## Composer installation

The package is listed on [Packagist](https://packagist.org/packages/mage-os/module-shopping-feed). Install the stable 1.1 line:

```bash
composer require 'mage-os/module-shopping-feed:^1.1'
bin/magento module:enable MageOS_ShoppingFeed
bin/magento setup:upgrade
bin/magento cache:clean
```

In production mode, complete the normal deployment steps for the project, including dependency-injection compilation and static-content deployment where required.

## Source installation

Place or symlink the source at:

```text
app/code/MageOS/ShoppingFeed
```

Use the `v1.1.0` tag for a reproducible 1.1 installation, and record its resolved commit. The `v1.0.0` tag remains available for historical installations.

Then run:

```bash
bin/magento module:enable MageOS_ShoppingFeed
bin/magento setup:upgrade
bin/magento cache:clean
```

## Verify the installation

```bash
bin/magento module:status MageOS_ShoppingFeed
bin/magento list mage-os:shopping-feed
```

Then verify:

* **Catalog > Mage-OS Shopping Feed > Feeds Management** opens for an authorized administrator.
* **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed** is available.
* The `mageos_shopping_feed_*` tables exist.
* The Admin and storefront load without new PHP or JavaScript errors.
* `bin/magento setup:di:compile` succeeds in the intended production configuration.

## Upgrade

Upgrade through the same installation path used for the module:

1. Record the current and target versions or commits.
2. Back up the database and generated feed configuration.
3. Update the Composer package or source checkout.
4. Run `bin/magento setup:upgrade`.
5. Clean the required caches and complete the project's deployment steps.
6. Regenerate a non-production feed and compare it with the previous accepted output.
7. Re-enable schedules and uploads only after the new output is accepted.

## Upgrading from 1.0 to 1.1

Version 1.1.0 requires `setup:upgrade`: encrypted upload passwords now use a `text` column, and standard-grid keyword search uses a full-text index on feed names. Complete DI compilation and static-content deployment where your deployment process requires them, then clear caches so the corrected Admin JavaScript loads.

For a Composer installation:

```bash
composer require 'mage-os/module-shopping-feed:^1.1' --no-update
composer update mage-os/module-shopping-feed --with-dependencies
bin/magento setup:upgrade
bin/magento cache:clean
```

An existing development-branch installation can move to the same stable constraint. Record the old lock file, module code, database, and configuration before upgrading. A rollback should restore that matching set; reverting PHP alone does not reverse schema and saved-configuration changes.

Review upload destinations by saving and reopening them with the password mask unchanged. If an old credential cannot be decrypted, replace it. The module does not recover secrets from truncated ciphertext. Verify schema status with `bin/magento setup:db:status` and test standard-grid keyword search.

Test scheduled recovery, selected promotion rules, UTF-8 limits, and storefront microdata/deep links where enabled. Nebula remains optional: test the grid and editor under the Admin theme actually used by the store. Historical JSON-looking text configuration keeps its explicit string marker; older development decoders require a compatible backup when rolling back.

## Google and custom-CSV changes in 1.1

Review saved feeds before resuming uploads:

1. **Google identifiers:** existing mappings are preserved. Replace SKU-to-MPN mappings unless SKU is the actual manufacturer MPN. Map real GTIN/MPN attributes. The Identifier Exists directive no longer writes FALSE merely because required fields are empty; confirmed identifier absence is now an explicit choice.
2. **Google backorders/preorders:** add `availability_date` to existing feeds and map a real expected shipping date. Rows requiring a date are skipped with a log warning until the date is valid. New feeds contain an empty placeholder, not an invented date.
3. **Local Inventory:** zero-stock backorders export as `out_of_stock`. Disabled source items also export as out of stock. Compare local quantities and statuses with the stores they represent.
4. **Generic CSV:** comma-delimited feeds now preserve commas and use CSV quotation rules for headers and values. Test the recipient's CSV parser; line splitting is not sufficient. Tab and other custom delimiters retain their existing behavior.

The Google and CSV changes do not overwrite saved column maps or need their own data migration. The 1.1 release still requires the schema upgrade described above. See [Google Shopping](Google-Shopping), [Local Inventory](Google-Local-Inventory-and-MSI), and [Generic feeds](Generic-Feeds) for setup and examples.

## Do not copy old installation instructions

Instructions for `rocketweb/module-google-shopping`, Magento Marketplace access keys, `rocketshoppingfeed:*` commands, or a `pub/media/feeds` output directory belong to the older Rocket Web packages. They do not install or operate `MageOS_ShoppingFeed`.
