# Release 1.0.0

**All 27 CI checks passed** for release commit `f14a763`, including unit tests, integration tests, coding standards, and dependency-injection compilation across the configured Magento Open Source and Mage-OS platforms. [View the passing release run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/34402149180).

Version 1.0.0 is the first stable release of Mage-OS Shopping Feed. It combines the Generic, Google Shopping, Google Local Inventory, and Google Promotions functionality under the independent `MageOS_ShoppingFeed` module identity.

> Documentation baseline: release `v1.0.0`. Last reviewed: 2026-09-09.

Read the [release notes](https://github.com/mage-os-lab/module-shopping-feed/releases/tag/v1.0.0) and [changelog](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.0.0/CHANGELOG.md) for the full change list.

## Main corrections

- [Hyva custom options](Product-Options) initialize without RequireJS.
- [Configurable deep links](Configurable-Product-Deep-Links) select the advertised child on Hyva, and [stock inheritance](Complex-Products) no longer requires positive parent quantity.
- [Microdata](Automatic-Updates-and-Schema-org) accepts only an eligible child of the current configurable product.
- [Queue lookups](Scheduling-and-Queues) remain independent across due feeds.
- [Output filtering](Filters-and-Transformations) removes delimiters introduced by HTML entity decoding, and text settings survive JSON-like content.
- Product links retain nonstandard ports, and [uploads](Uploads) stop if the configured remote directory cannot be entered.
- Empty column defaults are normalized before sanitization so saving feeds does not emit PHP deprecation notices.

## Recorded local acceptance

The local profile used Mage-OS 3.5.0, PHP 8.4.24, and Hyva on Magebox. Storefront acceptance used `a3dd9d0`; the subsequent release preflight corrected nullable column defaults and repeated feed-save and full-generation checks.

| Check | Result |
| --- | --- |
| PHP unit suite | 354 tests, 721 assertions passed |
| Default feed persistence | Generic, Google Shopping, and Local Inventory saved and reloaded without deprecation notices |
| Frontend regression suite | Five tests passed |
| Complete default-store generation | 148 visible products processed, 160 rows exported |
| File shape | 29 columns in every row; unique IDs and required values present |
| Price and stock comparison | All 160 exported rows matched the local storefront data |
| Product and image URLs | All 663 unique URLs returned HTTP 200 |
| Available configurable links | All 38 selected the expected child and displayed the expected price |
| Skipped products | Five image-less gift-card entries, reported in the log |

The [full validation record](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.0.0/docs/reviews/2026-09-09-full-feed-validation.md) includes the output checksum and method. GitHub Actions separately tests the configured Magento Open Source matrix and Mage-OS 3.4.0; consult the [CI run history](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml) for its current state.

This is local acceptance, not Merchant Center approval, a live FTP/SFTP transfer, or a production-scale catalog test. Google Ads event delivery on Hyva remains a separate integration. The Hyva failures were not established as Mage-OS 3.5 regressions.

## Install or upgrade

Follow [Installation and upgrade](Installation-and-Upgrade), using `composer require 'mage-os/module-shopping-feed:^1.0'` for the stable line. Existing Rocket Web packages are not migrated automatically. Back up a development installation before upgrading, then regenerate and compare its feeds before enabling schedules or uploads.

For setup and troubleshooting, use the [Shopping Feed support bot on Rocket Web](https://rocketweb.com/rocket-shopping-feeds). Select **Open support chat** on the product page.
