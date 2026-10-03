# Magento Open Source 2.4.7-p10 compatibility acceptance

Date: October 3, 2026. Release candidate: `feat/ui-component-editor`. Compatibility changes are in `bd71071969293f283ef064d4b56a1007ecec7242`; runtime files remain those accepted at `133af711a72edee42ee2d8b56b72761062b64116`. No application dependency constraint or schema changed.

## Environment and dependency disposition

A separate Docker Compose project, `shopping-feed-magento-247p10`, runs Magento Open Source 2.4.7-p10 with PHP 8.3, Composer 2.9.8, MySQL 8.0, OpenSearch 2.19.6, and Magento/backend. Its Admin is `http://127.0.0.1:8127/admin/`. Nebula is absent. The other local Magento profiles and `mageos-latest` are unchanged.

The initial Composer resolution failed on `league/flysystem ^2.4` and `PKSA-w9tt-7782-78jx`, reproducing the [reported CI failure](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37135210050/job/111238306149). A root-project exception for this advisory's install blocking permits the disposable install, but keeps it in `composer audit`. The audit gate rejects additional or hidden findings. The module's distributed Composer configuration contains no exception.

The installed Flysystem remains the original 2.5.0 during module acceptance. A separately patched copy passes the regression probe. See [dependency and backport guidance](../compatibility/magento-2.4.7-p10.md). These results do not classify the unpatched platform as free of security advisories.

## Automated checks

| Check | Result |
| --- | --- |
| Original PHPUnit 9 run | 21 errors: missing data-provider arguments because PHPUnit 9 ignores provider attributes |
| Same suite after adding annotations alongside attributes | 809 tests, 1,879 assertions passed |
| Magento native integration suite | 21 tests, 53 assertions passed |
| Actual Magento UI date/null-value behavior | 5 checks passed |
| Frontend suite and CI advisory policy | 29 frontend and 13 policy checks passed |
| Magento XML schemas | 26 files passed |
| Production DI compilation and Magento/backend static deployment | Passed |
| All eight presets: complete generation and parsed output | Two data rows each, consistent column widths |
| All eight presets: real CLI preview | One expected product each |
| Composer metadata and module validation | Passed |
| Workflow validation | YAML parse and actionlint 1.7.7 passed |
| Flysystem 2.5.0 backport | Three malformed-path cases fail before patch; all 12 checks pass afterward |

The 21 test-method annotations are the only compatibility correction outside the CI/dev tooling. PHPUnit attributes remain for newer frameworks. The native integration runner reports its existing custom-suite-loader deprecation. The local image uses a MySQL client for the MySQL server; the initial MariaDB-client TLS failure was a test-image issue, not a module failure.

## Browser acceptance pending

Chrome opened the new Admin login, but reports that another extension's popup blocks automation. Dismissing that popup is required before save/reopen, category/promotion editing, DynamicRows, and six-role browser acceptance can finish on 2.4.7-p10. No browser pass is claimed for this profile yet.

The separate test store retains eight disabled synthetic feeds, two products, one promotion rule, and six synthetic Admin role profiles for that continuation. Its pre-browser database backup is retained privately. No scheduled worker or external upload has been enabled. The shared Mage-OS installation and earlier Magento stores have not been changed.

## Remote CI

The [push run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37137598227) and [PR run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37137600514) test `bd71071`; its dedicated job completed installation, audit verification, units, optional backport, DI compilation, and native integration tests successfully. The dedicated 2.4.7-p10 job replaces its three blocked reusable matrix jobs with installation, visible audit verification, unit/integration tests, and DI compilation. Magento 2.4.8-p5, 2.4.9, and Mage-OS 3.4.0 retain their existing profiles. Check the exact final PR head's status; a local pass does not establish remote CI success.

## Evidence and limits

Local logs, database-backed comparisons, and browser evidence are retained under `/private/tmp/shopping-feed-magento-247p10-20261003/evidence`. Credentials are held separately and are excluded from the repository and reports.

The earlier [2.4.8/2.4.9 operational acceptance](2026-10-03-local-acceptance.md), including 5,000-product runs and real hourly cron cycles, remains evidence for those versions. It is not represented as a repeated 2.4.7-p10 result. External provider ingestion, production upload destinations, native Nebula bridge behavior, and production capacity remain unverified on this profile. This record does not grant final release approval.
