# Release 1.2.4

> Documentation baseline: release 1.2.4 (`v1.2.4`). Last reviewed: 2026-10-20.

Version 1.2.4 verifies SFTP server keys before authentication, keeps upload credentials encrypted, preserves logs if archiving fails, and tightens Test Feed permissions. It fixes inventory, configurable shipping, promotion timezone, table-prefix, and tax-helper edge cases, and completes the module standards work. Read the [full release notes](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.4/docs/releases/1.2.4.md).

## Upgrade

**Existing SFTP destinations need a trusted SHA256 host-key fingerprint.** Run `setup:upgrade` to add its column, obtain each fingerprint through a trusted channel, enter it in the feed's Uploads section, and verify save/reopen and delivery on staging. Missing or mismatched fingerprints prevent authentication. FTP remains available as explicitly unencrypted; unknown transfer modes are rejected.

The package adds an explicit `phpseclib/phpseclib:^3.0` dependency and declares `magento/framework:^103.0.6-p15`. Follow the [upgrade checklist](Installation-and-Upgrade#upgrading-from-123-to-124), preserve a matching database/code/lock/configuration backup, and review [Uploads](Uploads) before resuming schedules or transfers. The Rocket Web migration companion remains optional and separately installed.

## Verification

The local Mage-OS 3.6.0 run passes 939 unit tests with 2565 assertions, 30 integration tests with 88 assertions, four database persistence tests, and 84 JavaScript checks. Standard Admin save/reopen, DI compilation, real local FTP/SFTP delivery, gzip cleanup, failed host-key rejection before authentication, and complete log archival are recorded in the [verification report](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.4/docs/reviews/2026-10-20-standards-security-mageos36.md).

Check the [CI workflow](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/check-extension.yaml) for the exact tagged commit and supported platform matrix. Native Nebula acceptance, external recipient ingestion, and production volume were not repeated in this local run. The known preset output-limit key mismatch remains deferred; configure explicit **Limit column output** rows where needed.
