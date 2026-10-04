# Mage-OS Shopping Feed

**Version 1.2.1** fixes required-option prices, inherited/configurable backorders, and Page Builder descriptions. Read [Release 1.2.1](Release-1-2-1) and the [optional Rocket Web migration companion guide](Rocket-Web-Migration).

[![CI on main](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml/badge.svg?branch=main)](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml?query=branch%3Amain)

Version 1.2.0 introduced UI Component forms, five catalog presets, stock and Local Inventory corrections, store-scoped frontend settings, and currency preservation. Read [Release 1.2.0](Release-1-2-0) and its [upgrade checklist](Installation-and-Upgrade#upgrading-from-11-to-120). [Version 1.1.0](Release-1-1-0) remains documented for existing installations.

Mage-OS Shopping Feed generates product feeds from Mage-OS and Magento Open Source. It combines generic product feeds, Google Shopping, Google Local Inventory, and Google Promotions in one independently named module.

The [1.1 upgrade checklist](Installation-and-Upgrade#upgrading-from-10-to-11) covers the required schema update and changes to saved Google and comma-delimited feeds. [Release 1.0.0](Release-1-0-0) remains available as a historical record.

> Documentation baseline: release 1.2.1 (`v1.2.1`); earlier acceptance is identified by version. Last reviewed: 2026-10-04.

The [Admin UI Component forms](Admin-UI-Component-Forms) in 1.2.0 change the editor and its customization hooks. The release runtime `133af71` is deployed on `mageos-latest`, with form, grid-permission, preview, stock-selection, Local Inventory, frontend-scope, and currency-preservation corrections. Magento Open Source 2.4.8 and 2.4.9 retain production-mode form/output and six-role acceptance. The repository's `docs/reviews/2026-10-03-local-acceptance.md` is the current evidence record, including extended operational checks and remaining limits. Historical version 1.1 continues to use the previous editor.

Magento Open Source 2.4.7-p10 has a separate compatibility profile and upstream Flysystem advisory. Review [Status and compatibility](Status-and-Compatibility) before installing on that version; the tested optional backport is not applied automatically.

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

## Documentation publication

These pages are maintained in the module repository under `docs/wiki` and copied into this separate GitHub Wiki repository after review. A module commit, merge, or release does not automatically synchronize the wiki.

## Getting help

For setup and troubleshooting, use the [Shopping Feed support bot on Rocket Web](https://rocketweb.com/rocket-shopping-feeds). Select **Open support chat** on the product page.

Use [GitHub Issues](https://github.com/mage-os-lab/module-shopping-feed/issues) for reproducible defects and feature requests. Do not post credentials, private feed files, customer data, or internal paths.

Report security issues through the repository's [private vulnerability reporting process](https://github.com/mage-os-lab/module-shopping-feed/security/policy).
