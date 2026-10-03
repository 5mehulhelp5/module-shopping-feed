# Release 1.2.0 candidate

Version 1.2.0 is being prepared. Version 1.1.0 remains the latest published release. The candidate adds UI Component forms and five catalog presets, with corrections to stock selection, configurable Local Inventory, store-scoped frontend settings, and existing-feed currency.

> Documentation baseline: 1.2.0 release candidate, runtime `133af71` (unreleased); released 1.1 behavior is identified separately. Last reviewed: 2026-10-03.

Read the [draft release notes](https://github.com/mage-os-lab/module-shopping-feed/blob/feat/ui-component-editor/docs/releases/1.2.0.md), [changelog](https://github.com/mage-os-lab/module-shopping-feed/blob/feat/ui-component-editor/CHANGELOG.md), and [release preparation record](https://github.com/mage-os-lab/module-shopping-feed/blob/feat/ui-component-editor/docs/reviews/2026-10-03-release-1.2.0-preparation.md). These branch links are for candidate review; they are not a published release tag.

Magento Open Source 2.4.7-p10 passes its separate unit/integration, eight-preset browser/output, and six-role compatibility checks. Its upstream Flysystem advisory remains visible. Review [Status and compatibility](Status-and-Compatibility) before installing on that version; the tested optional backport is not applied automatically.

## Upgrade impact

* [Admin UI Component forms](Admin-UI-Component-Forms) replace legacy PHP tabs. Site-specific form observers, tab plugins, and parameter renderers need migration. The module's own integrations are already converted.
* Downstream constructor overrides for the feed-action column and composite/configurable adapters need the added dependencies. There is no new schema change or Composer platform requirement from 1.1.
* Review website/store overrides for microdata and Google Ads settings, which now take effect. Existing blank feed currencies retain their effective generation currency through an unchanged save.
* Nebula remains optional. The shared form contract reduces editor-specific work, while the Nebula grid and standard-theme editor fallback remain. Native UI Bridge rendering is unverified.
* New [Meta](Meta-Catalog), [Microsoft](Microsoft-Merchant-Center), [TikTok](TikTok-Catalog), [Pinterest](Pinterest-Catalog), and [OpenAI Google-compatible beta](OpenAI-ChatGPT) presets need recipient validation. Google Promotions remains a Google Shopping companion.

Follow [Installation and upgrade](Installation-and-Upgrade#upgrading-from-11-to-the-120-candidate). There is no editor toggle, and restoring code alone does not undo configuration saved by another version. Keep a matching backup.

## Recorded verification

Runtime `133af71` passed 809 unit tests on Magento Open Source 2.4.8, 2.4.9, and Mage-OS 3.5.0, with 21 integration tests and production compilation on each Magento Docker profile. Browser checks cover eight presets and six role profiles. Extended checks cover product modes, native MSI, pricing, 5,000-product generations, worker recovery, private FTP/SFTP transfer, two actual hourly cron cycles, rollback, and fixture cleanup.

See the [local acceptance record](https://github.com/mage-os-lab/module-shopping-feed/blob/089ba57e6124c3b61b062bfdd6df4d27f5025d04/docs/reviews/2026-10-03-local-acceptance.md) for the exact scope. External ingestion, native Nebula bridge, consent-managed tag delivery, Varnish, and production capacity remain separate checks. Remote CI, final release approval, publication, Packagist indexing, and public wiki synchronization are separate states.

[Release 1.1.0](Release-1-1-0) and [Release 1.0.0](Release-1-0-0) retain historical release evidence.
