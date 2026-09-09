# Mage-OS Shopping Feed

Mage-OS Shopping Feed generates product feeds from Mage-OS and Magento Open Source. It combines generic product feeds, Google Shopping, Google Local Inventory, and Google Promotions in one independently named module.

Version **1.0.0** is the first stable release. Read the [release summary](Release-1-0-0) for the Hyva compatibility fixes, feed correctness changes, and validation results.

> Documentation baseline: release `v1.0.0`. Last reviewed: 2026-09-09.

## What the module does

The module gives a merchant control over which products enter a feed, how Magento data maps into columns, how complex products are represented, and when completed files are transferred.

Current capabilities include:

* Generic delimited product feeds
* A Google Shopping template with current item-group and variant columns
* Google Local Inventory output with optional Multi-Source Inventory support
* Google Promotions generated alongside a Google Shopping feed
* Category filtering and Google taxonomy mapping
* Column mapping, static values, transformations, and output limits
* Configurable, grouped, bundle, and custom-option handling
* Scheduled and manual generation through a dedicated queue
* FTP or SFTP delivery, with optional streaming gzip compression
* Storefront schema.org offer data for automatic product updates
* Configurable-product deep links and Google Ads `view_item` events

## Start here

New installation:

1. Read [Status and compatibility](Status-and-Compatibility).
2. Follow [Installation and upgrade](Installation-and-Upgrade).
3. Build and test one feed with [Quick start](Quick-Start).
4. Configure the feed in detail using the Configuration section in the sidebar.
5. Complete the [release acceptance](Release-Acceptance) checks before enabling production schedules or uploads.

Existing Rocket Web installation:

1. Read [Migration and coexistence](Migration-and-Coexistence) first.
2. Evaluate the Mage-OS module on staging with schedules and uploads disabled.
3. Recreate and compare one feed before planning any production cutover.

## Admin locations

Manage feeds at **Catalog > Mage-OS Shopping Feed > Feeds Management**.

Global settings are at **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed**.

## A necessary boundary

This module has its own Composer package, Magento module, PHP namespace, database tables, configuration paths, Admin route, cron group, events, logs, and output directory. It does not replace or migrate an installed Rocket Web package.

## Getting help

Use [GitHub Issues](https://github.com/mage-os-lab/module-shopping-feed/issues) for reproducible defects and feature requests. Do not post credentials, private feed files, customer data, or internal paths.

Report security issues through the repository's [private vulnerability reporting process](https://github.com/mage-os-lab/module-shopping-feed/security/policy).
