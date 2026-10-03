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

## Browser and output acceptance

The user authorized completing the tests in the in-app browser after Chrome automation was blocked by another extension's popup. The following passed against the production-mode 2.4.7-p10 installation:

* Save each of the eight existing presets, reopen it, and save unchanged. Complete generation preserved their output, schedules, upload password hashes, unknown configuration, duplicate mapping priorities, structured parameters, and legacy static-rule migration. Only the documented OpenAI expiration clock was normalized for comparison.
* Create, save, reopen, and save unchanged all eight presets. The new Google Shopping feed uses microdata; the other seven default to zero.
* Save category priority zero, `Furniture` taxonomy, and `Home > Furniture` product type, including inherited child values. All four promotion dates survived two saves and reopens. The generated two-row Google feed contains the expected categories; its one-row promotion companion contains effective dates `2026-10-01/2027-04-02` and display dates `2026-10-02/2027-04-01`. Submit as new promotion disabled after one click on that editor load.
* Add mapping, find/replace, and schedule rows; preserve quotes, backslashes, HTML-like literal text, zero values, and an existing structured custom parameter. An invalid output path produced a recoverable error and retained non-secret edits. Correcting the path saved those values while preserving the existing masked-password hash. Deleting the final schedule, upload, and find/replace rows persisted empty collections.
* Preview a real product, repeat Test Now, recover from an unknown SKU, and view the generation log. The preview displays the saved category values and retains the product input.
* Search by a unique keyword, clone one selected feed, cancel individual deletion, confirm deletion, and bulk-delete a second exact disposable clone. Bulk-disable all 16 fixtures, enable one Google fixture, and confirm Run Now. A manually invoked queue worker completed the selected two-product feed and emptied its queue.
* Check full-access, save-only, generate-only, delete-only, read-only, and no-module-access role profiles. Create, Configure, Run Now, and mass actions follow each role's permissions. Direct editor navigation is denied to the four roles without save access; the no-access role is also denied the grid. The full-access session is restored.

No application change was required. The browser harness needed explicit native input/change events for Knockout fields. Magento displays April dates with a leading zero while storing the correct dates. The editor's separate-window Test Feed launch did not open a window in the in-app browser; preview acceptance used its rendered grid-link destination in the existing tab. Separate-window behavior is not claimed as verified on this profile. No JavaScript errors were captured during the completed browser run.

## Retained fixture state

The separate test store retains 16 disabled synthetic feeds, two products, one promotion rule, and six synthetic Admin role profiles. All schedules and upload destinations are removed, the queue is empty, module cron is disabled, and no scheduled worker is installed. Both disposable clones were deleted. Private database and output backups retain the before-cleanup state, with a database restore helper. The shared Mage-OS installation and earlier Magento stores remain unchanged.

## Remote CI

The [push run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37137907366) and [PR run](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/37137910770) each pass all 25 jobs for `a83da9a4352053b6c841020a8bde117c0ca7fb00`, the checkout used for this browser run. The dedicated 2.4.7-p10 job completed installation, visible audit verification, 809 unit tests, the optional backport probe, DI compilation, and 21 native integration tests. It replaces the three blocked reusable matrix jobs. Magento 2.4.8-p5, 2.4.9, and Mage-OS 3.4.0 retain their existing profiles. Documentation-only follow-ups require their own exact-head CI result in the release PR.

## Evidence and limits

Local logs, database-backed comparisons, browser snapshots, role results, final-state assertions, and the acceptance screenshot are retained under `/private/tmp/shopping-feed-magento-247p10-20261003/evidence`. All 407 runtime/package files match the repository. Credentials are held separately and are excluded from the repository and reports.

The earlier [2.4.8/2.4.9 operational acceptance](2026-10-03-local-acceptance.md), including complex-product/MSI scenarios, 5,000-product runs, real hourly cron cycles, and private transfer tests, remains evidence for those versions. It is not represented as a repeated 2.4.7-p10 result. This browser continuation also did not repeat the earlier forged-request/form-key matrix; its access checks cover rendered controls and direct editor/grid navigation. External provider ingestion, production upload destinations, native Nebula bridge behavior, and production capacity remain unverified on this profile. This record does not grant final release approval.
