# UI Component editor defect fixes

Verification completed October 2, 2026.

Local working-tree changes based on `9f07e46`, following the [code review](2026-10-01-ui-component-editor-review.md) and [deployed Chrome failures](2026-10-01-ui-component-chrome-acceptance.md). These fixes are not committed, deployed to `mageos-latest`, or released. The previously deployed `9f07e46` still contains the reported defects.

## Changes and verification

| Finding | Repair | Evidence |
| --- | --- | --- |
| Missing category IDs | The category component includes the ID. The converter supplies it for valid submitted rows, and the generator recovers missing IDs from existing map keys before sorting. | New mappings with priority zero and inherited values survived two browser saves. Preview and full generation produced the expected taxonomy and merchant product type. Unit coverage includes existing rows without embedded IDs. |
| Promotion-date data loss | A small date component retains localized display while fixing input/output storage to `Y/m/d`. Magento's locale preparation had overwritten the original metadata format. All four fields use the component. | All four dates survived save, reload, unchanged save, and reload. The companion file contained the expected effective and display ranges. Actual framework JavaScript checks passed for US, British, and German formats on Magento 2.4.8, 2.4.9, and Mage-OS. |
| Header control characters | Model save validation rejects control characters in column names with an Admin error. Literal values and structured directive parameters remain unchanged. | Tests cover tab, newline, carriage return, NUL, and DEL. Actual Admin requests with tab/newline headers were rejected without changing stored configuration. |
| Malformed category rows | Shared conversion validates the container, category identity, flags, priority, and text fields before pruning default rows. Invalid legacy JSON becomes a recoverable error. Unknown valid categories and custom row fields remain supported. | Tests exercise array and legacy JSON input. Admin requests with scalar rows, missing fields, and legacy JSON rows returned validation messages instead of uncaught errors. |
| Preview input validation | The controller separates feed identity from lookup mode, validates SKU/ID input before product lookup, and catches invalid feed identities. Provider recovery avoids casting arrays to strings. | Controller tests preserve `0` and `00042` as SKUs and normalize positive IDs. Actual requests with array input, unsupported modes, and a negative ID returned clear errors without product output. |
| Hidden microdata default | Only new Google Shopping feeds default to microdata. Other presets default to zero. Existing saved selections and explicit selections remain supported. | Unit coverage checks all eight presets and preservation of existing zero/one selections. Installed Builder-to-provider checks confirmed all eight defaults. No existing store selections were migrated. |

The review's custom configuration contract remains intact. Live legacy menu/tree dependencies and renderer identifiers remain in place; removing unused editor files is separate maintenance work. Existing clone filename behavior and immediate standard-grid bulk deletion are unchanged.

## Automated checks

The PHP suite passed against the installed Mage-OS framework and the retained Magento Open Source 2.4.8 and 2.4.9 framework checkouts. The final Mage-OS and Magento 2.4.9 runs each passed **788 tests / 2,067 assertions**. Magento 2.4.8 passed **788 tests / 1,811 assertions** with PHPUnit 10; compatibility mocks account for the assertion-count difference.

* Existing frontend suite: **28 tests passed**, including the category serializer regression.
* Framework date suite: **three locale cases passed on each of the three platforms**. Each exercises all four dates through two saves, leap day, and explicit clearing.
* UI naming contracts: three layout-referenced components passed.
* Magento XML schema validation: 26 files passed.
* Coding-standard gate: zero errors; existing and new style warnings remain permitted by the repository gate.
* Changed/new PHP syntax: 14 files passed. Wiki validation covered 37 pages; 185 relative documentation links/anchors and `git diff --check` passed.

The regression checks were observed failing against the original behavior before the repairs. Provider/wiring checks also ran against an isolated copy of the original classes; the working checkout was not reverted for that comparison.

Representative commands from the repository root:

```sh
MAGENTO_ROOT=/path/to/magento php /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
node --test dev/tests/frontend/*.test.cjs
MAGENTO_ROOT=/path/to/magento node --test dev/tests/magento-ui-form.test.cjs
php dev/tests/validate-magento-xml.php /path/to/magento
php dev/tests/validate.php
php dev/tests/validate-wiki.php
```

## Disposable runtime acceptance

The authenticated browser run used a fresh Mage-OS 3.5.0 installation on loopback port 8102 with Magento/backend, PHP 8.4.24, MySQL 8.4, and OpenSearch 3.8.0. It contained only synthetic products, categories, cart rules, and disabled feeds without schedules or upload destinations. The module was copied inside the application for Magento's template path validation. No `mageos-latest` code, configuration, or records were changed.

The Google fixture used a synthetic image URL and the category-mapped product-type directive so both products exercised the relevant output paths. Full generation produced two product rows with `Furniture` taxonomy and `Home > Furniture` product type. The promotion companion contained one row with `2026-10-01/2027-04-02` effective dates and `2026-10-02/2027-04-01` display dates. The rendered single-product preview matched and recorded no browser JavaScript errors. An unexposed custom configuration value remained intact.

Five malformed save requests and four malformed preview requests returned recoverable Admin messages. The feed configuration SHA-256 matched before and after the rejected requests. Fixture setup corrections, including the initial external module symlink and incomplete image mapping, were test-environment issues; final acceptance used the internal module copy and complete fixture.

Evidence and reproducible local scripts are under `/private/tmp/shopping-feed-ui-fixes-20261001/`. The `evidence` directory contains the unit/frontend/framework results, browser JSONL, screenshots, generated-file assertions, configuration hashes, and module manifest.

All nine repaired runtime files matched the browser-tested module copy. The same nine paths in `mageos-latest` were verified to retain their original deployed state. The test browser and PHP workers were stopped, and both disposable containers and their synthetic database volumes were removed. Evidence remains available; `cleanup.json` records the cleanup.

## Subsequent Magento Docker verification

The [Magento 2.4.8/2.4.9 Docker run](2026-10-02-magento-docker-acceptance.md) closes the repaired-workflow browser gap on both versions. It also found and repaired null directive parameters changing to empty strings, which removed the Generic shipping-weight default. The active parameter component now preserves null during both value linking and initial-value calculation. The frontend suite now has 29 tests; the framework suite has five cases per Magento version, including actual framework link behavior.

The Docker run establishes server-side restricted-role denial but identifies a pre-existing standard-grid UI issue: unavailable mutation controls remain visible. It does not establish a completely permission-filtered grid.

## Remaining boundaries

These changes prevent the reproduced failures; they do not reconstruct promotion dates already erased by the old candidate. Compare affected configuration with a pre-change backup before any recovery. There is no automatic store-data migration.

The initial Mage-OS run did not establish Magento browser acceptance; the subsequent Docker report above supplies that evidence for its listed workflows. Deployment verification on `mageos-latest`, fully permission-filtered standard-grid controls, native Nebula bridge rendering, external uploads, live cron, recipient ingestion, and large-catalog performance remain open. No push, merge, remote CI, publication, or release is implied.
