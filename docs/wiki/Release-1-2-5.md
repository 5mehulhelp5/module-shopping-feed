# Release 1.2.5

> Documentation baseline: release 1.2.5 (`v1.2.5`). Last reviewed: 2026-10-22.

Version 1.2.5 creates the **Skip from Being Submitted** product attribute that earlier releases checked but never created, and reads dropdown and multiselect option labels for the feed's store view. Full notes: [release notes](https://github.com/mage-os-lab/module-shopping-feed/blob/main/docs/releases/1.2.5.md) and [changelog](https://github.com/mage-os-lab/module-shopping-feed/blob/main/CHANGELOG.md).

## Upgrade

**Run `setup:upgrade`.** It creates `mageos_shopping_feed_skip_submit` as a Yes/No field, default No, with store-view scope, under **Shopping Feed** in every existing product attribute set, and leaves a merchant-created attribute with that code unchanged. There are no table changes. Follow the [upgrade checklist](Installation-and-Upgrade#upgrading-from-124-to-125). Upgrading from 1.2.3 or earlier also requires the 1.2.4 SFTP fingerprint step.

## Changes

* **Exclude one product.** On the product form, set **Shopping Feed > Skip from Being Submitted** to Yes. The product, including configurable, grouped, and bundle children, is left out of every feed for that store view, and the feed log names it. A store-view No restores it there. Deleting the user-managed attribute disables the exclusion; a later `setup:upgrade` does not recreate it. See [Filters and transformations](Filters-and-Transformations#exclude-a-single-product).
* **Store-view option labels.** Dropdown and multiselect columns use the feed store view's labels, fall back to the admin label where no override exists, keep multiselect order, and cache labels per store. Option rows from custom source models without a usable value are skipped. See [Columns and directives](Columns-and-Directives).

## Verification

Local checks on the candidate, on Mage-OS 3.6.0 with PHP 8.4.26 and the unit suite also on PHP 8.5.11: 951 unit tests, 38 integration tests on a fresh install, four database persistence tests, 84 JavaScript checks, PHPStan level 8 with a smaller baseline, the Magento coding standard, and the Composer, wiki, and consolidation validators. Functional acceptance on an upgraded store copy covered real `setup:upgrade` and its repeat, the Admin product form with store-view overrides, Admin and CLI previews, and queued generation. See [Release acceptance](Release-Acceptance#125-verification).

Known limits: the "rows skipped" count does not include products skipped by the new field, which the feed log names; sources that ignore the attribute store, such as country of manufacture, keep the process locale's labels; the preset output-limit mismatch from 1.2.4 remains. External recipient ingestion and production volume were not part of this run. Check the [CI workflow](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/check-extension.yaml) for the tagged commit and supported platform matrix.
