# Mage-OS deployment acceptance

Tested October 2, 2026, on `http://mageos-latest.localhost:8080/admin/`. The deployment uses Mage-OS 3.5.0, native PHP 8.4.24, developer mode, and Magento/backend. All 16 installed Nebula modules remain disabled.

The deployed candidate is commit `c07de812e8cb740073c213734bc54f029b8a31c1`. It combines base `f3edab5` with the two fixes found during this acceptance run. It includes the earlier form repairs and permission-filtered grid. No push, merge, release, or wiki publication was performed.

## Defects found and repaired

1. A preserved null product-URL parameter reached `substr()` in the Generic URL mapper. PHP 8.4 emitted a deprecation that Magento's CLI error handler converted into failed preview generation. The mapper now treats null as an empty query string. A regression test explicitly promotes deprecations to exceptions, so the unit bootstrap's normal deprecation suppression cannot hide this failure.
2. Test Now supplied the UI-component button adapter but omitted `on_click`. Magento's default UI button renderer therefore added a navigation handler pointing at the module's nonexistent index controller. Clicking the button produced a GET and 404 before any preview POST reached PHP. An explicit empty `on_click` suppresses that extra handler. The regression uses the actual Magento button renderer and failed before the fix. Browser submission now returns product output.

Both fixes are committed as `c07de81` and deployed. They change no constructor, schema, dependency, or Nebula integration.

## Verification

| Check | Result |
| --- | --- |
| Mage-OS unit suite | 801 tests, 2,126 assertions, no PHPUnit notices |
| Magento Open Source 2.4.8 unit suite, including both follow-ups | 801 tests, 1,866 assertions |
| Magento Open Source 2.4.9 unit suite, including both follow-ups | 801 tests, 2,126 assertions |
| Deployment DI compilation | Passed |
| Magento/backend static deployment, `en_US` | Passed |
| Magento XML validation | 26 files passed |
| Frontend tests | 29 passed |
| Real Mage-OS UI framework JavaScript cases | Five passed, including three date locales and null parameter initialization/value links |
| Form preparation and projected data | Six original feeds and eight temporary presets passed |
| CLI single-product preview | All eight presets passed with `atlas-pouf` |
| Browser persistence | All eight presets saved, reopened, and saved again unchanged; final stored configuration matched exactly |
| Full CLI generation | All eight presets passed before and after unchanged saves |
| Browser preview | Generic SKU preview; Google SKU and Product ID previews; invalid Product ID produced a recoverable error and the valid retry passed |
| Category regression | New category 4 mapping retained its ID, zero priority, taxonomy, and product type; preview and generated product row contained the expected values |
| Promotion regression | Effective dates `2026/10/01` and `2027/04/02`, display dates `2026/10/02` and `2027/04/01` survived save/reopen and an unchanged second save; companion output contained both ranges |
| Invalid category priority | Client validation rejected `-1`; the stored valid mapping and promotion dates remained unchanged |
| Clone and individual delete | Clone copied configuration into a new disabled feed; Cancel preserved it, then confirmation deleted it |
| Final surfaces | Authenticated grid displayed the six original feeds; storefront returned HTTP 200 |

The existing catalog produced 160 Generic rows, 160 Google Shopping rows, 152 Meta rows, 146 Pinterest rows, 152 TikTok rows, 152 Microsoft rows, 151 OpenAI rows, and 165 Local Inventory rows. Required test brand/store-code mappings were supplied on temporary feeds without changing catalog products or categories.

Generated output comparison normalizes only two existing clock-dependent values: sale-price fallback times for product 96 (`chair-vila-velvet`, whose special-price dates are absent), and OpenAI expiration times. Dates, time zones, every other cell, and row order remain compared. All eight normalized outputs match, and the promotion companion file is byte-identical. Raw files and hashes are retained. This is not a byte-identical claim for the eight product files.

The first CLI-created fixtures had an empty currency. Their first Admin save correctly selected the store's EUR default, changing prices from the CLI fallback currency. The unchanged-save comparison was repeated after that initial setup. Null directive parameters, unexposed nested custom configuration, and microdata selections persisted.

Chrome automation intermittently detached while sending input. Text replacement also failed to emit the change events used by Magento's fields. Acceptance therefore used explicit DOM change events and, where needed, the rendered button's click handlers through the Chrome debugging interface. No registry values were assigned and no form save methods were invoked directly. Stored values, server requests, visible success/error responses, and generated files verified the results. Native keyboard automation is not established by this run. Chrome also logged a view-transition abort during navigation; the final grid rendered correctly. This is not a zero-console-error claim.

The six-role permission matrix and official integration/database tests remain the results of the [Magento Docker run](2026-10-02-magento-docker-acceptance.md) and [grid follow-up](2026-10-02-grid-permission-acceptance.md). They were not repeated with new Admin accounts on this shared Mage-OS installation. The [committed preview follow-up](2026-10-02-preview-followup-acceptance.md) records the subsequent unit, integration, CLI preview, source-parity, and focused browser checks on the retained Docker runtimes. The full eight-preset and six-role browser matrices were not repeated for these two additional fixes.

## Preservation and cleanup

Original feed IDs `63`, `153`, `154`, `155`, `156`, and `157` were not edited. The final snapshot exactly matches the pre-deployment snapshot across all seven module tables, all six original output hashes, the module configuration file hash, cron configuration, and Nebula module states.

Final counts are six feeds, 370 configuration rows, 290 process rows, and zero schedules, upload destinations, queue rows, or shipping-cache rows. Module cron remains at its original enabled setting. Test feeds had no schedules or upload destinations; generation used explicit temporary IDs. No external transfers occurred.

Temporary feeds `178` through `185` and clone `186` were removed. Their database state was retained before cleanup, and 25 generated output/log/lock files were quarantined. All 254 recorded unrelated target file hashes, including existing dirty work and application configuration, are unchanged. Maintenance is off.

## Deployment and rollback

The target module is `/Users/matt/code/mageos-latest/app/code/MageOS/ShoppingFeed`. The initial deployment changed 14 runtime paths from the original `9f07e46` copy. The two follow-ups bring the scoped rollback set to 16 paths. All 408 deployed runtime/package files match commit `c07de81`; this was rechecked after committing, together with all 254 preserved target file hashes. The deployment manifest records that source commit and retains the original rollback hashes.

The protected backup is `/Users/matt/code/mageos-latest/var/backups/shopping-feed-repairs-20261002T144327Z/`. It contains the original module archive, deployment and preservation manifests, follow-up patches and before/after files, test-data snapshots, generated-file quarantine, and `restore-module.py` with `ROLLBACK.md`.

For code rollback, follow that `ROLLBACK.md`: enable maintenance, run the scoped restore script, rebuild DI and Magento/backend static assets, clean caches, then restore the recorded maintenance state. The restore refuses later edits and changes only the recorded module paths. Do not restore the database merely to roll back this code. The earlier editor-migration backup remains separate; this repair rollback returns to the pre-repair UI Component candidate, not the legacy editor.

Evidence is retained under `/private/tmp/shopping-feed-mageos-deploy-20261002/`, including failing regression logs, final unit logs, browser save records and screenshots, stored regression values, before/after feed output, normalized comparisons, and preservation results.

## Remaining acceptance limits

This verifies the listed standard-Admin deployment workflows. Native Nebula bridge rendering, downstream editor customizations, exhaustive complex-product/MSI combinations, external FTP/SFTP delivery, scheduled cron execution, provider ingestion, and large-catalog performance remain separate release gates. Code deployment cannot recover promotion dates previously erased by an older candidate. No release-readiness or user-acceptance claim is implied.
