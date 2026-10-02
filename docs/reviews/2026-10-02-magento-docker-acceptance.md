# Magento Open Source Docker acceptance

> Follow-up: the [Mage-OS deployment acceptance report](2026-10-02-mageos-latest-deployment-acceptance.md) records the subsequent `mageos-latest` deployment and two additional preview fixes. Deployment-status statements below describe this earlier run.

**Follow-up:** The form repairs were committed as `c8092ca`. The later [grid permission repair](2026-10-02-grid-permission-acceptance.md) resolves the read-only control finding on both Docker installations. The original results and candidate state below are retained as the record of this run.

Tested October 2, 2026. Candidate: the uncommitted `feat/ui-component-editor` working tree based on `9f07e46`, including the [review repairs](2026-10-02-ui-component-editor-fixes.md) and the null-parameter repair found during this run. No commit, push, release, or deployment to `mageos-latest` was performed.

## Installations

Both applications run completely in Docker: PHP/Apache, MySQL, and OpenSearch. Each has its own application copy, database volume, search volume, and browser session. The exact retained Composer locks identify `magento/product-community-edition` 2.4.8 and 2.4.9; installed metadata reports Community edition. Neither installation contains Nebula.

| Platform | Admin | PHP | MySQL | OpenSearch | Mode |
| --- | --- | --- | --- | --- | --- |
| Magento Open Source 2.4.8 | `http://127.0.0.1:8128/admin/` | 8.4.26 | 8.4.11 | 2.19.6 | Production |
| Magento Open Source 2.4.9 | `http://127.0.0.1:8129/admin/` | 8.4.26 | 8.4.11 | 3.8.0 | Production |

The installations use synthetic products, categories, cart rules, and feeds. Admin two-factor authentication and secret URL keys are disabled only in these isolated stores. POST form-key validation remains active and was tested. Only the web ports are published, on loopback. No live store database, credentials, or feed destinations were copied.

## Automated results

| Check | 2.4.8 | 2.4.9 |
| --- | --- | --- |
| PHP unit suite | 788 tests, 1,811 assertions | 788 tests, 2,067 assertions |
| Official Magento integration harness, separate database | 14 tests, 30 assertions | 14 tests, 30 assertions |
| Password/queue database regressions, temporary tables | 4 tests, 14 assertions | 4 tests, 18 assertions |
| Actual framework JavaScript | 5 cases | 5 cases |
| Magento XML validation | 26 files | 26 files |
| UI naming contracts | 3 components | 3 components |
| Production DI compilation | Passed | Passed |
| Admin and Luma static deployment | Passed | Passed |

The common frontend suite passes **29 tests**, nine of which cover the active Admin form. The framework suite separately exercises all four promotion dates through two saves in US, British, and German formats, plus null parameter initialization and the actual Magento value-link implementation. PHPUnit 10 and 12 compatibility mocks account for assertion-count differences.

## Browser and output results

The following passed on both installations:

* Create, save, reopen, and unchanged-save all eight presets. New Google Shopping feeds use microdata by default; all seven other types default to zero.
* Save all eight existing presets and regenerate their two-product outputs. Generated content remained identical, allowing only the documented OpenAI expiration clock normalization. Unknown configuration, structured parameters, duplicate priorities, legacy static-rule migration, schedules, and stored upload credential hashes were preserved.
* Add mapping and find/replace rows, preserve literal quotes, backslashes, HTML-like text and newlines, and delete the final schedule, upload, and find/replace rows. Invalid-path recovery retained non-secret edits and omitted newly typed passwords.
* Create a category mapping with priority zero and inherited child values, save twice, preview a product, and generate the full feed. Both rows contain `Furniture` taxonomy and `Home > Furniture` product type.
* Save all four promotion dates, reopen, save unchanged, and reopen again. The generated companion contains effective dates `2026-10-01/2027-04-02` and display dates `2026-10-02/2027-04-01`. The promotion counter increments once per editor load.
* Reject five malformed header/category requests, including legacy JSON rows, with recoverable errors. Reject four malformed preview requests. Reject an invalid POST form key. The stored configuration SHA-256 remains unchanged across rejected requests.
* Search by a unique keyword, clone an exact selected feed, cancel individual deletion, confirm individual deletion, and delete an exact disposable clone through the bulk action. Original feeds remain present. Bulk deletion retains its existing immediate behavior.
* Bulk-disable all fixtures, enable one exact fixture, and use Run Now with its confirmation. A manually invoked queue worker completes the queued Google feed and its product/promotion output is checked again. This does not establish recurring cron execution.
* Read-only and no-module-access accounts receive HTTP 403 on all eight mutation routes: edit, save, generate, delete, mass clone, mass enable, mass disable, and mass delete. The no-access account is also denied the grid.

## Additional defect repaired

An unchanged Generic feed lost its default `kg` shipping-weight unit after a form save on both Magento versions. The saved directive parameter began as null. Magento's value-link initialization skipped that null import, then exported the base input's empty-string default; the base initial-value calculation also skipped null. Those two steps changed generator behavior.

`view/adminhtml/web/js/form/parameter.js` now starts with a null value and preserves it when calculating the initial value. Explicit empty strings, zero, false, arrays, and deliberate directive changes keep their distinct meanings. The real-framework regression checks were observed failing before the repair. The final browser save and generated-output comparisons pass on both versions.

## Remaining finding and limits

**The standard grid still displays unavailable mutation controls to read-only users.** Create New Feed, Configure, Run Now, and mass-action choices are visible even though the server denies those operations. The relevant grid/block files match `9f07e46`; this is a pre-existing presentation defect, not an authorization bypass. This run records it without changing the grid. Permission-filtered control visibility remains an acceptance item.

These results establish the listed standard-Admin workflows on both requested versions. They do not establish native Nebula UI Bridge rendering, third-party editor customizations, a representative complex-product/MSI catalog, external FTP/SFTP delivery, recurring cron, recipient ingestion, or large-catalog performance. The separate `mageos-latest` installation has not received these repairs. Previously erased promotion dates still require recovery from a suitable backup.

## Local access and evidence

All 407 runtime/package paths in each installed module match the final working tree. Fresh browser sessions recorded no JavaScript errors across the grid, editor, preview, and log pages.

Both requested installations remain available. The Docker project and scripts are under `/private/tmp/shopping-feed-magento-docker-20261002/`, with per-version evidence in `248/evidence/` and `249/evidence/`. Evidence includes test logs, before/after output files, comparison JSON, rejected-request hashes, browser records, screenshots, and runtime/source manifests. Test username: `feed_tests`. Generated passwords are stored only in the protected local `admin-access.json` file.

The two applications finish with 16 disabled feeds each, no schedules, and no upload destinations. No background cron or queue worker is installed. The separate temporary database-regression container was removed. Browser automation used DOM readiness, DOM click events, and keyboard activation where coordinate clicks raced with popup or modal transitions; harness failures are retained alongside the passing evidence.

Operate only this project:

```sh
cd /private/tmp/shopping-feed-magento-docker-20261002
docker compose -f compose.json ps
docker compose -f compose.json stop
docker compose -f compose.json start
```

Stopping retains the applications and database/search volumes. The files live under `/private/tmp`; preserve the directory if these instances need to outlive temporary-file cleanup. Published documentation and release CI were not changed by this local run.
