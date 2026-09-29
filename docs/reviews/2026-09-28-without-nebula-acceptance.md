# Installation without Nebula, 2026-09-28

The candidate was installed into a separate, fresh Mage-OS 3.5.0 application with no Nebula packages or module code present. Composer installation, schema installation and upgrade, dependency-injection compilation, production mode, static deployment, and the tested standard Admin workflows passed.

This extends the [September 25 compatibility acceptance](2026-09-25-admin-compatibility.md), which tested the standard Admin with Nebula disabled but still installed. No existing store database or application configuration was changed during this follow-up.

## Environment and installation

* Official `mage-os/project-community-edition:3.5.0`, created through Composer using `https://repo.mage-os.org/`.
* PHP 8.4.24, isolated MySQL 8.4 and OpenSearch 3.8.0 containers, loopback-only services.
* Shopping Feed installed through a Composer path repository with mirroring enabled and symlinks disabled. The package's declared dependencies were resolved normally.
* Core installation initially excluded `MageOS_ShoppingFeed`. Enabling it and running `setup:upgrade` then exercised installation into an existing clean store.
* The Composer lock contained 610 packages including development dependencies, with zero `qoliber/*` or Nebula packages. Runtime component registration and `class_exists` checks independently confirmed Nebula's absence.
* `setup:di:compile`, `setup:static-content:deploy`, and `deploy:mode:set production --skip-compilation` passed. The optional Nebula editor override remained inactive.
* Browser checks covered both the default `MageOS/m137-admin-theme` grid and Magento's classic `Magento/backend` grid. Detailed editor and action checks used the classic theme.

Private evidence is retained in `/private/tmp/shopping-feed-no-nebula-20260928/evidence`. The application files are in the sibling `app` directory. Final source/install parity covered 559 files. Both test containers and their disposable volumes were removed, the browser and HTTP server were closed, and synthetic credentials were removed. Existing store data and configuration were untouched. These are local acceptance artifacts, not a deployment or published release.

## Defects found and corrected

1. Standard-grid keyword search returned every feed. Magento's `FulltextFilter` skips filtering when the collection table has no full-text index. Added a declarative full-text index on feed names and its schema whitelist entry. The schema regression failed before the change; the same browser query returned exactly its matching record after `setup:upgrade`. Its filtered CSV contained that one record.
2. Category mapping could initialize before Magento's form widget and throw `$(...).form is not a function`. Declared `mage/backend/form` as an explicit RequireJS dependency. A regression reproduced the missing dependency and verifies category data serialization before submission. Browser edits then survived save and reload.
3. The promotion counter used the global Prototype `$` before it loaded. The control now uses native DOM events and class operations. A regression reproduced `$ is not defined` and verifies that repeated clicks increment the counter only once per editor load.

Existing generated JavaScript assets had to be cleared before redeployment so browser checks loaded the changed dependency list. The final deployed asset was checked against the source. Historical failed probes and browser errors remain in the private logs; final checks identify the corrected results.

## Verification

| Check | Result |
| --- | --- |
| PHP unit suite against the no-Nebula dependencies | 391 tests, 903 assertions passed |
| Application integration suite | 10 tests, 26 assertions passed |
| JavaScript suite | 14 tests passed, including both new load-order regressions |
| Magento XML | 24 files passed |
| PHP/PHTML syntax | 463 files passed |
| Module, wiki, Composer, and whitespace validation | Passed |
| Configured Magento coding standard | Zero errors; 3,194 warnings under the repository's warning-tolerant exit policy |
| Standard grid | Listing, keyword filtering, sorting, pagination without overlapping records, filtered CSV export |
| Standard bulk actions | Clone, disable, enable, and delete succeeded on synthetic records |
| Google feed editor | Creation, all 12 tabs, repeated saves, category mapping, schedule-hour edits, and SKU grouping persistence |
| Promotion control | Two clicks incremented the counter once; saved successfully |
| Product preview | One synthetic product returned 29 mapped fields with the expected title and price |
| Generation | Generic: 3 rows/17 columns; Google Shopping: 3 rows/29 columns; Local Inventory: 3 rows/7 columns; consistent row widths and unique keys |
| Run Now and queue processing | Standard-grid POST created one queue entry; explicit CLI processing generated three rows and removed the entry |
| HTTP responses | Storefront, all three product pages, the product image, and generated feed returned HTTP 200 |
| Browser errors | All 12 tabs opened with no new JavaScript errors after the final asset rebuild |

The integration tests ran against the real application object manager inside a rollback transaction, rather than Magento's separate integration installer. The fresh catalog contained only three synthetic simple products and one synthetic category. This follow-up does not repeat the earlier full-catalog variant, transport, or MSI matrices and does not establish an unattended cron soak.

Reproduction commands, with `MAGEOS_ROOT` pointing to the clean application and PHP 8.4 on `PATH`:

```sh
composer create-project --repository-url=https://repo.mage-os.org/ mage-os/project-community-edition:3.5.0 clean-store --no-install
# Add the candidate as a mirrored Composer path repository, then install dependencies.
composer install --no-interaction --prefer-dist
# Install core with isolated service credentials and --disable-modules=MageOS_ShoppingFeed.
bin/magento module:enable MageOS_ShoppingFeed
bin/magento setup:upgrade
bin/magento setup:di:compile
bin/magento setup:static-content:deploy -f en_US
bin/magento deploy:mode:set production --skip-compilation
bin/magento setup:db:status
# From the module repository:
MAGENTO_ROOT="$MAGEOS_ROOT" php "$MAGEOS_ROOT/vendor/bin/phpunit" -c phpunit.xml.dist
node --test dev/tests/frontend/*.test.cjs
php dev/tests/validate.php
php dev/tests/validate-wiki.php
php dev/tests/validate-magento-xml.php "$MAGEOS_ROOT"
composer validate --strict --no-check-publish
```

Upgrades require `setup:upgrade` for the password-column change from the preceding review fixes and the new full-text index. Nebula remains optional. Composer emitted upstream abandoned-package notices; this report does not claim a warning-free third-party dependency tree.
