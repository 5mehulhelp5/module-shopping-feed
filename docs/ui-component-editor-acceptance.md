# UI Component editor acceptance

Pre-deployment acceptance of the implementation on `feat/ui-component-editor`, based on `f8488b1258cf52f8bb38dc53b983cc745ba28599`. This report records the disposable-installation checks below; deployment verification is recorded separately in the target installation's backup directory.

## Tested installations

| Installation | Environment | Results |
| --- | --- | --- |
| Magento Open Source 2.4.8 | PHP 8.4.24, MySQL 8.4, OpenSearch 2.19.6, Magento/backend, no Nebula | All eight types created and saved; output/data round trips passed; advanced editor interactions and preview passed; DI compilation and Admin static deployment passed in production mode |
| Magento Open Source 2.4.9 | PHP 8.4.24, MySQL 8.4, OpenSearch 3.8.0, Magento/backend, no Nebula | Same checks passed; production mapping table and log route visually inspected |
| Mage-OS 3.5.0 | PHP 8.4.24, MySQL 8.4, OpenSearch 3.8.0, default Admin, no Nebula | All eight types created and saved; dynamic rows, child deletion, category inheritance, failed-save recovery, promotions, and preview checked in developer mode |

The Mage-OS Composer product/base packages report 3.5.0. Its `Magento\Framework\App\ProductMetadataInterface` reports the underlying Magento version 2.4.9.

All installations were disposable local sites using synthetic products, categories, sales rules, schedules, and upload destinations. No feed was uploaded externally. The 417 runtime/package files matched the workspace byte-for-byte across all three installed copies at final verification. These acceptance checks did not change the separate module copy in `mageos-latest`; its 16 Nebula modules were disabled.

## Data and interaction checks

- Created and saved Generic, Google Shopping, Google Local Inventory, Meta, Microsoft, TikTok, Pinterest, and OpenAI Google-compatible feeds.
- Compared generated artifacts before and after a browser save for all eight types on both Magento versions. Output matched. The OpenAI comparison excludes the clock portion of its computed expiration timestamp.
- Verified preservation of unknown configuration, structured custom parameters with zero/false/nested arrays, literal `${...}` text, duplicate mapping priorities, schedules, and upload credentials. Converted legacy static fallback values to their equivalent directive parameter representation.
- Added mapping and find/replace rows; changed directives; saved literal quotes, backslashes, HTML-like text, and newlines; deleted the final find/replace rule, schedule, and upload destination.
- Verified category priority zero and taxonomy inheritance into an empty child category; unit checks additionally cover inactive and unknown category mappings.
- Triggered a server-side failed save with an invalid output path. The form recovered other values, cleared a newly typed password, and kept that password out of returned HTML/provider data.
- Verified Cancel and confirmed POST deletion on a synthetic feed through the restored Delete Feed button.
- Edited a promotion title and incremented its counter, then generated a Test Feed preview containing 31 output fields.
- Rehearsed an ordinary feed read/save using the unchanged original model/converter source. Custom configuration and upload credentials remained intact. The older editor still has its documented string/whitespace normalization limits for custom parameter structures.
- Inspected the production mapping-table screenshot and the log route, with no browser JavaScript errors in the final checks.

## Automated checks

- PHP unit suite: **722 tests, 1,908 assertions**, including the existing child-ownership and upload encryption tests.
- JavaScript suite: **28 tests**.
- PHP syntax: 503 PHP/PHTML files checked; subsequent changed PHP paths also loaded by unit/runtime checks.
- Magento XML schema validation: 26 files, checked against both Magento versions.
- UI naming contracts: three layout-referenced components passed.
- Composer strict validation, module consolidation validation, wiki validation, and `git diff --check` passed.
- Production DI compilation and Admin static deployment passed on both Magento versions; final form assets were loaded in the browser after deployment.

Representative commands from the repository root:

```sh
MAGENTO_ROOT=/path/to/magento php /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
node --test dev/tests/frontend/*.test.cjs
php dev/tests/validate.php
php dev/tests/validate-wiki.php
php dev/tests/validate-magento-xml.php /path/to/magento
```

Local evidence is retained under `/private/tmp/shopping-feed-ui-20261001/evidence/`: `roundtrip248-comparison.json`, `roundtrip249-comparison.json`, `advanced248-final.log`, `advanced249-final.log`, `advanced350.log`, `preview350-final.log`, `create-all*-final.log`, `create-all350.log`, `save-all350.log`, `production248.log`, `production249.log`, `compile248-final.log`, `compile249-final.log`, `delete249-final.log`, `unit-final.log`, `frontend-final.log`, `rollback-check.log`, `source-parity.json`, and `ui-component-form249.png`.

## Remaining release boundaries

Native rendering through a Nebula UI Bridge has not been accepted. The existing editor theme fallback and separate Nebula grid integration remain. No Nebula dependency was added, and no unsupported bridge path was enabled.

External PHP form observers, tab plugins, and bespoke parameter renderers require migration to the documented UI metadata extension points. No downstream customer customization inventory, large-catalog performance benchmark, remote CI run, or live-store acceptance is claimed. See [the implementation and customization guide](ui-component-editor.md).

The three acceptance browser sessions and PHP servers were closed, and the three disposable Docker containers and their synthetic database volumes were removed. Evidence and setup scripts remain in the temporary directory; `cleanup.json` records the cleanup verification.
