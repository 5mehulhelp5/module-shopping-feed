# Mage-OS Shopping Feed

`MageOS_ShoppingFeed` generates product feeds for Mage-OS and Magento Open Source.

This repository consolidates four related Rocket Web modules into one independently named Mage-OS module:

- Generic product feeds
- Google Shopping feeds
- Google Local Inventory feeds, including optional Multi-Source Inventory support
- Google Promotions feeds

Current integrations include configurable-product deep links, schema.org offer data for Automatic Item Updates, Google Ads `view_item` events, gzip transfer over FTP or SFTP, reservation-aware MSI quantities, and explicit MSI source-to-Google-store mapping.

The package has its own Composer name, PHP namespace, Magento module name, database tables, configuration paths, routes, cron group, event names, JavaScript aliases, and default output names. It does not replace or mutate an installed Rocket Web package.

## Status

This is a new module identity prepared for Mage-OS Lab. Existing Rocket Web installations are not migrated automatically. Read [MIGRATION.md](MIGRATION.md) before evaluating it on a store that already uses a Rocket Web shopping feed module.

Run [ACCEPTANCE-TEST-PLAN.md](ACCEPTANCE-TEST-PLAN.md) against the exact release candidate before enabling production schedules or uploads.

## Requirements

- A currently supported Mage-OS or Magento Open Source release with `magento/framework` 103.0.6-p15 or later in the 103.x series
- A PHP version supported by the selected platform release, within PHP 8.1 through PHP 8.5
- Magento cron when scheduled feed generation is enabled
- Magento Multi-Source Inventory APIs for source-level Local Inventory feeds

Mage-OS 3.4.0, based on Magento Open Source 2.4.9, is an explicit CI compatibility target. Its production checks install the package into a Mage-OS 3.4.0 project, then run the unit and integration suites, Magento coding standard, and dependency-injection compilation.

## Installation

Once the package is available through a configured Composer repository:

```bash
composer require mage-os/module-shopping-feed
bin/magento module:enable MageOS_ShoppingFeed
bin/magento setup:upgrade
bin/magento cache:clean
```

For a source checkout, place or symlink the repository at `app/code/MageOS/ShoppingFeed`, then run the Magento commands above without `composer require`.

## Use

Manage feeds in the Admin under **Catalog > Mage-OS Shopping Feed > Feeds Management**.

Global settings are under **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed**.

The module registers two CLI commands:

```bash
# Build queued feeds, a specific feed, or one test SKU
bin/magento mage-os:shopping-feed:generate [feed_id] [test_sku]

# Create feed-generation queue entries from configured schedules
bin/magento mage-os:shopping-feed:schedule
```

The dedicated `mageos_shopping_feed` cron group schedules feeds hourly and processes its queue every minute by default.

Feed output is restricted to `pub/media/mageos-shopping-feed` and its safe subdirectories. Per-feed logs are restricted to `var/log` and use `mageos_shopping_feed_*.log` by default.

## Development validation

Run the dependency-free consolidation checks and PHP syntax checks from the repository root:

```bash
composer validate --strict --no-check-publish
php dev/tests/validate.php
find . -path './.git' -prune -o -type f \( -name '*.php' -o -name '*.phtml' \) -print0 | xargs -0 -n1 php -l
```

Validate every Magento XML file against the schemas from an existing Magento or Mage-OS checkout:

```bash
php dev/tests/validate-magento-xml.php /path/to/magento
```

Run the imported and modernized unit suite with the PHPUnit installation from that checkout:

```bash
MAGENTO_ROOT=/path/to/magento /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
```

The unit-test bootstrap loads Magento's test framework and the module directly, so the module does not need to be installed in the validation checkout. CI also installs the package into currently supported Magento Open Source releases and explicitly into Mage-OS 3.4.0, runs the unit and integration suites, checks the Magento coding standard, and compiles dependency injection.

## Provenance and license

The consolidated source and exact import revisions are documented in [PROVENANCE.md](PROVENANCE.md). Original Rocket Web copyright and author notices are retained in source files.

The package uses the [Open Software License 3.0](LICENSE.txt). Composer metadata, source notices, and the bundled license are aligned on OSL-3.0. See [LICENSING.md](LICENSING.md).
