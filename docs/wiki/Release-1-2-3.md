# Release 1.2.3

> Documentation baseline: release 1.2.3 (`v1.2.3`). Last reviewed: 2026-10-13.

Version 1.2.3 corrects website-stock reservation totals and physical-source Local Inventory output, promotion persistence/cache writes, manual queue checkpointing, Nebula filter navigation, and Admin package-version reporting. It also preserves native microdata without a replacement feed and repairs configurable URL-selection timing. Read the [full release notes](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.3/docs/releases/1.2.3.md).

## Upgrade

There is no schema change or new required Composer dependency relative to 1.2.2. Follow the [upgrade checklist](Installation-and-Upgrade#upgrading-from-122-to-123). Compare aggregate and Local Inventory quantities with the selected website stock, linked sources, and reservations. Verify promotion configuration and TSV rows, manual batching, and storefront output before resuming schedules or uploads.

The [Rocket Web migration companion](Rocket-Web-Migration) remains optional and separately installed; an upgrade does not import or activate legacy feeds.

## Recorded verification

The combined release candidate passes 922 local unit tests and 2481 assertions using the Mage-OS 3.5 framework. The earlier [hardening CI run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37792760180) passed all 25 jobs across Mage-OS 3.5.0 and three Magento Open Source profiles, including 27 integration tests per platform. The combined candidate and merged release are checked separately in [CI](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml?query=branch%3Amain) against their exact SHAs.

The [hardening report](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.3/docs/reviews/2026-10-08-hardening-and-mageos-3.5-verification.md) covers native inventory, all eight presets, promotions, and queue recovery. The [native theme follow-up](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.3/docs/reviews/2026-10-08-remote-ci-and-native-theme-acceptance.md) covers Hyvä 1.5.2 and the Nebula 0.9.0 grid, actions, and supported Magento backend editor fallback. Native UI Bridge rendering and external recipient ingestion remain unverified. Earlier transport, recurring-cron, volume, and rollback records retain their original versions; see [Release acceptance](Release-Acceptance).
