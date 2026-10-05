# Release acceptance

Release acceptance proves the extension on representative Magento runtimes and proves each generated artifact at the boundary where it is consumed.

> Documentation baseline: release 1.2.2 (`v1.2.2`); earlier acceptance is identified by version. Last reviewed: 2026-10-05.

Magento Open Source 2.4.7-p10 has a separate compatibility profile and upstream Flysystem advisory. Review [Status and compatibility](Status-and-Compatibility) before installing on that version; the tested optional backport is not applied automatically.

## 1.2.2 verification

The local core suite passes 863 tests on Mage-OS 3.5/PHPUnit 12, Magento 2.4.8/PHPUnit 10, and Magento 2.4.7-p10/PHPUnit 9; 47 JavaScript checks pass. Seventeen new description cases include the exact #14 report. Nine fail against the earlier runtime and pass with the correction. The merged runtime passed all 25 [post-merge CI jobs](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37332361658). See [Release 1.2.2](Release-1-2-2) and the repository's `docs/reviews/2026-10-05-release-1.2.2-preparation.md` for exact baselines and publication boundaries.

Users migrating from Rocket Shopping Feeds can install the optional [Rocket Web migration companion](Rocket-Web-Migration). Existing-store migration, recipient acceptance, and explicit activation remain separate checks.

## 1.2.1 verification

Version 1.2.1 passes 846 local unit tests on both native frameworks and 47 JavaScript checks. The 32 new regression cases reproduce 22 assertion failures in v1.2.0 and pass with the fixes. The companion passes 63 unit/database tests per framework and 24 opt-in legacy runtime tests. Full store/browser/generated-output acceptance was not all repeated against 1.2.1; earlier results below retain their 1.2.0 scope. See [Release 1.2.1](Release-1-2-1).

## 1.2.0 acceptance record

The release runtime `133af71` is deployed on `mageos-latest` and installed on the Magento Open Source 2.4.8/2.4.9 Docker profiles. The original category-generation and promotion-date failures are repaired. Form persistence/output, permissions, previews, stock selection, native configurable MSI, pricing, frontend scope, 5,000-product generation, private receiver delivery, and rollback have recorded local evidence. See [Admin UI Component forms](Admin-UI-Component-Forms) and the repository's `docs/reviews/2026-10-03-local-acceptance.md` for the hourly soak, cleanup state, exact scope, and linked historical results.

The [1.2.0 release summary](Release-1-2-0) links the release notes and upgrade checklist. Release publication does not establish external provider ingestion, a customer's transfer destination, native Nebula UI Bridge behavior, consent-managed tag delivery, Varnish behavior, or production-catalog capacity. Test the exact release on each intended target and preserve those boundaries in the release record.

The repository's [`ACCEPTANCE-TEST-PLAN.md`](https://github.com/mage-os-lab/module-shopping-feed/blob/main/ACCEPTANCE-TEST-PLAN.md) is the detailed test source. This page describes the evidence expected from a release candidate.

For historical 1.1 results and limits, see [Release 1.1.0](Release-1-1-0). The earlier [1.0.0 record](Release-1-0-0) remains historical evidence. The general profiles below remain the acceptance guide for each target deployment; the release record does not claim every external integration was exercised.

## Required profiles

Test the release candidate against the supported Mage-OS and Magento Open Source profiles declared by Composer and CI. Include a store with cron, writable media and log directories, storefront product pages, and representative simple and complex products.

Features that need additional fixtures should be tested with them:

* Standard Admin with Nebula absent, plus Nebula Admin when supported
* Upgrade from 1.0 with existing uploads, schedules, and column maps
* Google identifier/date edge cases and CSV parser round trips
* MSI sources, website stocks, and reservations for Local Inventory
* Active cart price rules for Promotions
* Configurable products with swatch and select attributes for deep links
* An existing consent-managed Google tag integration for direct `view_item` delivery
* A quarantine FTP or SFTP target for transfer tests

## Acceptance layers

| Layer | Evidence |
| --- | --- |
| Installation | Module enables, setup completes, Admin route and ACL work, no legacy package is required |
| Configuration | Every feed section survives save, reload, and an unchanged save; generated output confirms the saved values |
| Product data | Expected headers, stable row shape, sampled values, price behavior, and complex-product output |
| Operations | Manual run, queue, cron, batch continuation, locking, atomic final file, logging, and retries |
| Integrations | Local Inventory, Promotions, schema.org, deep links, `view_item`, and uploads where enabled |
| Compatibility | CI and runtime evidence for the supported platform matrix |
| Upgrade | Clean install and upgrade from the previous Mage-OS module release |

## Safety rules

* Use test products, stores, rules, credentials, and destinations.
* Do not point an unaccepted feed at a serving endpoint.
* Do not enable live ad event delivery without the site's consent and tag owners.
* Do not infer Google acceptance from a locally valid-looking file. Capture the current account diagnostic or API result.
* Do not infer production readiness from unit tests or CI alone.

## Release record

Record the exact commit and package artifact, environment versions, validation output, generated file checksums, sampled expected-versus-actual data, external diagnostics, unresolved limitations, and cleanup result.

Track prepared, locally verified, committed, pushed, CI green, released, installed, externally accepted, and live-verified as separate states. Approval for one state does not imply the next.
