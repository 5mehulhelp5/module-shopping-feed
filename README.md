# Mage-OS Shopping Feed

[![CI on main](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml?query=branch%3Amain)

Release **1.0.0 passed all 27 CI checks** on commit `f14a763`. [View the passing release run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/34402149180).

`MageOS_ShoppingFeed` generates product feeds for Mage-OS and Magento Open Source.

This repository consolidates four related Rocket Web modules into one independently named Mage-OS module:

- Generic product feeds
- Google Shopping feeds
- Google Local Inventory feeds, including optional Multi-Source Inventory support
- Google Promotions feeds

Current integrations include configurable-product deep links, schema.org offer data for Automatic Item Updates, Google Ads `view_item` events, gzip transfer over FTP or SFTP, reservation-aware MSI quantities, and explicit MSI source-to-Google-store mapping.

The package has its own Composer name, PHP namespace, Magento module name, database tables, configuration paths, routes, cron group, event names, JavaScript aliases, and default output names. It does not replace or mutate an installed Rocket Web package.

## Status

Version [1.0.0](https://github.com/mage-os-lab/module-shopping-feed/releases/tag/v1.0.0) is the first stable release of this independently named module. Existing Rocket Web installations are not migrated automatically. Read [MIGRATION.md](MIGRATION.md) before evaluating it on a store that already uses a Rocket Web shopping feed module.

This release includes Hyva custom-option and configurable-variant selection, safer microdata selection, and corrections to queue scheduling, output encoding, uploads, and stock inheritance. See the [release notes](docs/releases/1.0.0.md) and [full-feed validation record](docs/reviews/2026-09-09-full-feed-validation.md).

Run [ACCEPTANCE-TEST-PLAN.md](ACCEPTANCE-TEST-PLAN.md) against the exact release candidate before enabling production schedules or uploads.

## Requirements

- A currently supported Mage-OS or Magento Open Source release with `magento/framework` 103.0.6-p15 or later in the 103.x series
- A PHP version supported by the selected platform release, within PHP 8.1 through PHP 8.5
- Magento cron when scheduled feed generation is enabled
- Magento Multi-Source Inventory APIs for source-level Local Inventory feeds

Mage-OS 3.4.0, based on Magento Open Source 2.4.9, is an explicit CI compatibility target. Its production checks install the package into a Mage-OS 3.4.0 project, then run the unit and integration suites, Magento coding standard, and dependency-injection compilation.

Mage-OS 3.5.0 on PHP 8.4.24 was also verified locally on Magebox with Hyva: a complete 160-row storefront feed, all exported prices and stock values, and all 38 available configurable deep links passed the recorded checks. This local evidence is separate from CI and Merchant Center acceptance.

## Installation

Install the 1.x release from [Packagist](https://packagist.org/packages/mage-os/module-shopping-feed):

```bash
composer require 'mage-os/module-shopping-feed:^1.0'
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

## Documentation

For setup and troubleshooting, use the [Shopping Feed support bot on Rocket Web](https://rocketweb.com/rocket-shopping-feeds). Select **Open support chat** on the product page.

The reviewable GitHub Wiki source is under [`docs/wiki`](docs/wiki), starting with [`Home.md`](docs/wiki/Home.md). Documentation contributors should update that source and follow [`docs/WIKI-MAINTENANCE.md`](docs/WIKI-MAINTENANCE.md) rather than editing the public wiki independently.

## Development validation

Run the dependency-free consolidation checks and PHP syntax checks from the repository root:

```bash
composer validate --strict --no-check-publish
php dev/tests/validate.php
php dev/tests/validate-wiki.php
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
