# Release 1.2.0

Released October 3, 2026. Version 1.2.0 adds UI Component forms and five catalog presets, with corrections to stock selection, configurable Local Inventory, store-scoped frontend settings, and existing-feed currency.

> Documentation baseline: release 1.2.0 (`v1.2.0`); historical 1.1 behavior is identified separately. Last reviewed: 2026-10-03.

Read the [release notes](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.0/docs/releases/1.2.0.md), [changelog](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.2.0/CHANGELOG.md), and [GitHub release](https://github.com/mage-os-lab/module-shopping-feed/releases/tag/v1.2.0). Source links are pinned to the release tag.

Magento Open Source 2.4.7-p10 passes its separate unit/integration, eight-preset browser/output, and six-role compatibility checks. Its upstream Flysystem advisory remains visible. Review [Status and compatibility](Status-and-Compatibility) before installing on that version; the tested optional backport is not applied automatically.

## Upgrade impact

* [Admin UI Component forms](Admin-UI-Component-Forms) replace legacy PHP tabs. Site-specific form observers, tab plugins, and parameter renderers need migration. The module's own integrations are already converted.
* Downstream constructor overrides for the feed-action column and composite/configurable adapters need the added dependencies. There is no new schema change or Composer platform requirement from 1.1.
* Review website/store overrides for microdata and Google Ads settings, which now take effect. Existing blank feed currencies retain their effective generation currency through an unchanged save.
* Nebula remains optional. The shared form contract reduces editor-specific work, while the Nebula grid and standard-theme editor fallback remain. Native UI Bridge rendering is unverified.
* New [Meta](Meta-Catalog), [Microsoft](Microsoft-Merchant-Center), [TikTok](TikTok-Catalog), [Pinterest](Pinterest-Catalog), and [OpenAI Google-compatible beta](OpenAI-ChatGPT) presets need recipient validation. Google Promotions remains a Google Shopping companion.

Follow [Installation and upgrade](Installation-and-Upgrade#upgrading-from-11-to-120). There is no editor toggle, and restoring code alone does not undo configuration saved by another version. Keep a matching backup.

## Recorded verification

Runtime `133af71` passed 809 unit tests on Magento Open Source 2.4.8, 2.4.9, and Mage-OS 3.5.0, with 21 integration tests and production compilation on each Magento Docker profile. Browser checks cover eight presets and six role profiles. Extended checks cover product modes, native MSI, pricing, 5,000-product generations, worker recovery, private FTP/SFTP transfer, two actual hourly cron cycles, rollback, and fixture cleanup.

See the [local acceptance record](https://github.com/mage-os-lab/module-shopping-feed/blob/089ba57e6124c3b61b062bfdd6df4d27f5025d04/docs/reviews/2026-10-03-local-acceptance.md) for the exact scope. External ingestion, native Nebula bridge, consent-managed tag delivery, Varnish, and production capacity remain separate checks. The [release PR](https://github.com/mage-os-lab/module-shopping-feed/pull/12) retains exact-commit CI and publication evidence.

[Release 1.1.0](Release-1-1-0) and [Release 1.0.0](Release-1-0-0) retain historical release evidence.
