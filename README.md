# Mage-OS Shopping Feed

[![CI on main](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml?query=branch%3Amain)

Version **1.1.0** adds optional Nebula Admin support, safer upload and queue handling, corrected Google feed output, guest tier pricing, consistent variant grouping, and configurable custom output. Read the [1.1.0 release notes](docs/releases/1.1.0.md) and [upgrade checklist](docs/wiki/Installation-and-Upgrade.md#upgrading-from-10-to-11).

`MageOS_ShoppingFeed` generates product feeds for Mage-OS and Magento Open Source.

The **1.2 development branch** adds a [Meta Catalog template](docs/wiki/Meta-Catalog.md) for Facebook and Instagram. It generates UTF-8 TSV with Meta availability values, variant grouping, and row validation. This work is unreleased and still needs Commerce Manager acceptance.

It also adds [Microsoft Merchant Center](docs/wiki/Microsoft-Merchant-Center.md), with tab-delimited TXT output, Microsoft stock values, configurable variants, and destination-specific validation. Microsoft import acceptance remains a release check.

[TikTok Catalog](docs/wiki/TikTok-Catalog.md) adds quoted CSV output for Ads Manager catalogs, including `sku_id`, TikTok availability values, variant grouping, and row validation. TikTok import acceptance remains unverified.

[Pinterest Catalog](docs/wiki/Pinterest-Catalog.md) adds quoted TSV for retail catalogs, with Pinterest field limits, availability values, and configurable grouping checks. Pinterest import acceptance and real catalog image quality remain release checks.

[OpenAI / ChatGPT (Google-compatible, beta)](docs/wiki/OpenAI-ChatGPT.md) adds a TSV discovery preset with conditional identifier and availability-date checks. OpenAI must confirm this profile during onboarding; local generation does not establish account access, ingestion acceptance, or checkout support.

This repository consolidates four related Rocket Web modules into one independently named Mage-OS module:

- Generic product feeds
- Google Shopping feeds
- Google Local Inventory feeds, including optional Multi-Source Inventory support
- Google Promotions feeds

Current integrations include configurable-product deep links, schema.org offer data for Automatic Item Updates, Google Ads `view_item` events, gzip transfer over FTP or SFTP, reservation-aware MSI quantities, and explicit MSI source-to-Google-store mapping.

The package has its own Composer name, PHP namespace, Magento module name, database tables, configuration paths, routes, cron group, event names, JavaScript aliases, and default output names. It does not replace or mutate an installed Rocket Web package.

## Status

Version [1.1.0](https://github.com/mage-os-lab/module-shopping-feed/releases/tag/v1.1.0) is the current stable release. Existing Rocket Web installations are not migrated automatically. Read [MIGRATION.md](MIGRATION.md) before evaluating it on a store that already uses a Rocket Web shopping feed module.

The release retains custom feed mapping and Hyva/Luma deep links while correcting identifier defaults, backorder dates, Local Inventory statuses, and CSV serialization. Existing Google mappings need review, comma-feed recipients must accept quoted CSV, and `setup:upgrade` is required for the upload-password column and feed-search index. See the [changelog](CHANGELOG.md), [Google/custom-feed acceptance report](docs/reviews/2026-09-28-google-custom-feed-fixes.md), and [issue acceptance report](docs/reviews/2026-09-28-github-issues.md). Historical [1.0.0 notes](docs/releases/1.0.0.md) remain available.

Run [ACCEPTANCE-TEST-PLAN.md](ACCEPTANCE-TEST-PLAN.md) against the exact release candidate before enabling production schedules or uploads.

## Requirements

- A currently supported Mage-OS or Magento Open Source release with `magento/framework` 103.0.6-p15 or later in the 103.x series
- A PHP version supported by the selected platform release, within PHP 8.1 through PHP 8.5
- Magento cron when scheduled feed generation is enabled
- Magento Multi-Source Inventory APIs for source-level Local Inventory feeds

Mage-OS 3.4.0, based on Magento Open Source 2.4.9, is an explicit CI compatibility target. Its production checks install the package into a Mage-OS 3.4.0 project, then run the unit and integration suites, Magento coding standard, and dependency-injection compilation.

Mage-OS 3.5.0 on PHP 8.4.24 was also verified locally on Magebox with Hyva: a complete 160-row storefront feed, all exported prices and stock values, and all 38 available configurable deep links passed the recorded checks. This local evidence is separate from CI and Merchant Center acceptance.

The module supports Magento's standard Admin grid and an optional native Nebula Admin grid. With Nebula enabled, feed editing, previews, and logs open in the standard Admin layout so the complete existing editor remains available; returning to the list restores Nebula. No Nebula dependency is required for standard installations. See the [Admin compatibility acceptance report](docs/reviews/2026-09-25-admin-compatibility.md) for the tested versions and limits.

A fresh Mage-OS 3.5.0 installation with no Nebula packages also passed Composer installation, schema upgrades, DI compilation, production-mode generation, and standard Admin browser checks. See the [installation without Nebula report](docs/reviews/2026-09-28-without-nebula-acceptance.md).

## Installation

Install the 1.1 release line from [Packagist](https://packagist.org/packages/mage-os/module-shopping-feed):

```bash
composer require 'mage-os/module-shopping-feed:^1.1'
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

Feed output is restricted to `pub/media/mageos-shopping-feed` and its safe subdirectories. Files are publicly downloadable for recipient fetches; default filenames contain the feed ID and are predictable. FTP or SFTP upload leaves that public local copy in place. Export only data intended for public distribution. Per-feed logs are restricted to `var/log` and use `mageos_shopping_feed_*.log` by default.

## Documentation

For setup and troubleshooting, use the [Shopping Feed support bot on Rocket Web](https://rocketweb.com/rocket-shopping-feeds). Select **Open support chat** on the product page.

The [public GitHub Wiki](https://github.com/mage-os-lab/module-shopping-feed/wiki) is published separately; it does not automatically update when this repository changes. The reviewable GitHub Wiki source is under [`docs/wiki`](docs/wiki), starting with [`Home.md`](docs/wiki/Home.md). Documentation contributors should update that source and follow [`docs/WIKI-MAINTENANCE.md`](docs/WIKI-MAINTENANCE.md) rather than editing the public wiki independently.

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
