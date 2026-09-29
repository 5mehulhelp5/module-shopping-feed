# Google specification and custom-feed fixes, 2026-09-28

The four defects identified in the final specification review are corrected in the working tree following commit `4242649`. Regression tests reproduced the failures before the implementation changed. The updated candidate passed generation and browser checks in a fresh, disposable Mage-OS 3.5.0 database with no Nebula packages or module code present.

## Changes

* **Identifiers:** missing catalog data no longer implies `identifier_exists=FALSE`. The directive requires explicit confirmation of identifier absence, and a supplied brand, GTIN, or MPN overrides that choice. New feeds leave MPN and GTIN attribute mappings empty instead of using SKU as MPN. Existing saved maps remain intact.
* **Availability dates:** Google Shopping backorder and preorder rows require a valid future date within one year. Invalid rows are skipped with an actionable warning and skipped count. ISO dates and Magento UTC datetime values are normalized; invalid optional dates are omitted without dropping an in-stock or out-of-stock product.
* **Local availability:** online backorder flags no longer override local quantities. Simple source rows respect the source item's status. Configurable and grouped rows retain their associated-product quantity calculation without the online backorder override.
* **Custom CSV:** Generic comma output quotes every header and cell, doubles embedded quotes, and preserves commas. Custom names, ordering, values, Unicode, and trailing empty fields survive parsing. Google-specific date validation and sale-price suppression do not apply to Generic feeds.

The implementation follows Google's current [identifier rules](https://support.google.com/merchants/answer/6324478?hl=en), [MPN rules](https://support.google.com/merchants/answer/6324482?hl=en), [availability-date format](https://support.google.com/merchants/answer/6324470?hl=en), and [Local Inventory vocabulary](https://support.google.com/merchants/answer/14819809?hl=en). CSV quotation follows the escaping rules described in [RFC 4180](https://www.rfc-editor.org/rfc/rfc4180.html).

## Verification

| Check | Result |
| --- | --- |
| Unit suite | 413 tests, 955 assertions passed |
| Application integration suite | 10 tests, 26 assertions passed; rollback preserved the feed count |
| JavaScript suite | 14 tests passed |
| Generated-feed acceptance | 33 checks passed across Google Shopping, Local Inventory, Generic CSV, tab, pipe, and Other/comma configurations |
| PHP/PHTML syntax | 466 files passed; final Generator edit rechecked |
| Magento XML and module contracts | 24 XML files and all three feed types passed |
| Wiki validation | 30 pages and 30 sidebar targets passed |
| Magento coding standard | Zero errors; 3,207 warnings under the configured warning-tolerant exit policy |
| Fresh application setup | Core installation, module enable/upgrade, indexing, DI compilation, and cache flush passed |
| Google Admin form | New empty MPN/GTIN controls, explicit identifier confirmation, date mapping, save/reload, and backorder preview passed |
| Custom Admin form | Created, renamed/reordered columns, added a quoted Unicode literal, saved/reloaded, and previewed successfully |
| Browser-created CSV | HTTP 200; Python strict CSV parser read 3 rows and 18 columns with exact quoted/comma/Unicode values |
| Browser errors | No JavaScript errors reported in the isolated browser session |
| Legacy saved map | A saved 29-column map survived reload unchanged, including its explicit SKU-to-MPN mapping; legacy identifier parameters no longer infer absence |

The initial focused regression run had 16 failures before the fixes. A separate regression then reproduced the optional-date formatting problem before its correction. Local source availability tests include positive and zero quantity, disabled source items, and configurable/grouped online-backorder overrides.

The application reused the official Mage-OS 3.5.0 dependency tree from the preceding clean-install review, with a fresh database and isolated MySQL/OpenSearch containers. It used PHP 8.4.24 and the standard Admin under `MageOS/m137-admin-theme`. Installed module files were mirrored from this checkout and compared by SHA-256. No existing store database or application configuration was changed.

Private evidence, generated files, scripts, browser screenshots, and the candidate manifest are retained under `/private/tmp/shopping-feed-spec-fixes-20260928/evidence`. Test services and synthetic credentials were removed after verification.

Core checks can be repeated from the module checkout with PHP 8.4 and `MAGEOS_ROOT` pointing to a Mage-OS application:

```sh
MAGENTO_ROOT="$MAGEOS_ROOT" php "$MAGEOS_ROOT/vendor/bin/phpunit" -c phpunit.xml.dist
node --test dev/tests/frontend/*.test.cjs
php dev/tests/validate.php
php dev/tests/validate-magento-xml.php "$MAGEOS_ROOT"
php dev/tests/validate-wiki.php
php "$MAGEOS_ROOT/vendor/bin/phpcs" --standard=phpcs.xml.dist
git diff --check
```

The integration suite used the installed application object manager inside a rollback transaction, not Magento's separate integration installer. Runtime fixtures were three synthetic simple products. Composite behavior was covered by unit tests; this pass did not repeat the previous full variant, MSI, transport, or Nebula browser matrices. Localhost URLs and placeholder images were used, so these results do not establish Merchant Center acceptance. Nothing was uploaded to Google.

## Upgrade implications

Existing Google feeds need their identifier mappings reviewed and an `availability_date` column added if they include backorders or preorders. No date is invented and no saved mapping is rewritten. Existing comma-feed recipients must parse quoted CSV and should be checked before uploads resume. These fixes require no additional database migration.

The changelog and wiki pages for Google Shopping, Local Inventory, Generic feeds, directives, transformations, and upgrades have been updated. Follow the [upgrade checklist](../wiki/Installation-and-Upgrade.md#google-and-custom-csv-changes-in-11) before enabling uploads. At the time of this acceptance run, the changes were local and uncommitted; no push, deployment, tag, or release had been performed.
