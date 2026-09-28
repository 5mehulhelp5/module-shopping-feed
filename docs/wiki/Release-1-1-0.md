# Release 1.1.0

Version 1.1.0 adds optional Nebula Admin support and corrects upload persistence, queue recovery, Google feed output, and custom CSV serialization. Standard Admin installations require no Nebula package.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

Read the [release notes](https://github.com/mage-os-lab/module-shopping-feed/releases/tag/v1.1.0) and [complete changelog](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/CHANGELOG.md). [Release 1.0.0](Release-1-0-0) remains the historical record for the first stable version.

## Changes that affect configuration

| Area | Behavior in 1.1 |
| --- | --- |
| Admin | Optional native Nebula grid; existing standard editor, previews, and logs; corrected standard-grid keyword search |
| Upload credentials | Masked and repeated saves preserve usable encryption; longer ciphertext fits the upgraded column |
| Queue recovery | Interrupted rows can restart on the next worker invocation without appending duplicate partial output |
| Google identifiers | Explicit absence confirmation; no default SKU-to-MPN assumption; empty MPN/GTIN mappings for new feeds |
| Google availability | Backorders and preorders need a real future date; missing or invalid dates produce logged skips |
| Local Inventory | Online backorders do not override local quantity and source availability |
| Generic CSV | Quoted fields preserve commas and escape quotes; custom names, order, and values remain configurable |
| Other corrections | Multiple promotion rules, UTF-8 limits, explicit microdata selection, literal URL option handling, and safe previews |

## Required upgrade steps

Install with `composer require 'mage-os/module-shopping-feed:^1.1'` and run `bin/magento setup:upgrade`. The release changes the password column and adds a full-text feed-name index. Complete the store's normal compilation, static deployment, and cache steps.

Existing column maps are preserved. Review identifier mappings, add `availability_date` where needed, and verify quoted CSV with the recipient. Keep schedules and uploads disabled until the output is accepted. Follow [Installation and upgrade](Installation-and-Upgrade#upgrading-from-10-to-11), [Google Shopping](Google-Shopping), and [Generic feeds](Generic-Feeds).

## Recorded verification

The final Google/custom-feed acceptance on Mage-OS 3.5.0 without Nebula passed:

* 413 unit tests with 955 assertions
* 10 application integration tests with 26 assertions
* 14 JavaScript tests and 33 generation checks
* Browser creation, save/reload, product preview, and HTTP CSV download with strict parsing

The [Google/custom-feed report](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-28-google-custom-feed-fixes.md) includes methods and limits. Separate earlier reports cover [Nebula compatibility](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-25-admin-compatibility.md), [installation without Nebula](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-28-without-nebula-acceptance.md), and [storefront acceptance](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-25-mageos-3.5-acceptance.md).

Check the [CI history](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml) for the exact release commit. Mage-OS 3.4.0 and supported Magento Open Source versions are CI targets; Mage-OS 3.5.0 is a local acceptance profile. Local checks are not Merchant Center approval or proof of a live FTP/SFTP transfer.

For setup and troubleshooting, use the [Shopping Feed support bot on Rocket Web](https://rocketweb.com/rocket-shopping-feeds) and select **Open support chat**.
