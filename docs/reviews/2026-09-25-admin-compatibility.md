# Admin compatibility acceptance, 2026-09-25

Follow-up: a [fresh installation without any Nebula packages](2026-09-28-without-nebula-acceptance.md) was verified on September 28. That follow-up also corrected standard-grid keyword search and two editor JavaScript initialization errors. This report preserves the September 25 acceptance state.

The installed candidate supports the native Nebula feed grid and Magento's standard grid. With Nebula enabled, feed editing, previews, and logs open in the standard Admin layout, preserving the existing configuration controls. Returning to the list restores Nebula. This integration does not introduce a replacement editor built with Nebula forms.

The earlier Google grouping inconsistency and three stuck local indexers are resolved. Testing also exposed an unescaped feed name in the standard grid's HTML cell; the name is now escaped while preserving the module's microdata indicator.

## Candidate and installation

* Source: `/Users/matt/code/module-shopping-feed`, based on `6c69b2887178408342a894846b02c34cf0434e79`, including the preceding uncommitted review fixes.
* Installed module: `/Users/matt/code/mageos-latest/app/code/MageOS/ShoppingFeed`.
* Runtime: Mage-OS 3.5.0, PHP 8.4.24, MySQL 8.4, Nebula Admin 0.9.0.
* Evidence and rollback: `/Users/matt/code/mageos-latest/var/shopping-feed-nebula-20260925`.

The optional integration uses Nebula's grid definition, collection registry, and grid block. It adds no required Nebula package, inherits no Nebula PHP classes, and changes no vendor files. The standard grid remains the existing Magento UI component. Compatibility work adds no schema changes. A full database backup, module archive, and original application configuration were retained before this work. No commit, push, PR, release, or external deployment was performed.

## Changes

* Native grid columns, store/type/status filters, sorting, pagination, file links, export, create links, per-row actions, and bulk actions.
* Explicit Nebula selections are accepted only by this module's mass-action routes. Empty, malformed, oversized, and non-POST selections fail closed. Standard Magento selections continue through Magento's existing filter.
* Bulk and generation actions retain backend ACL and form-key enforcement. Native grid write controls are hidden from read-only roles. Generation uses a POST form.
* Grid responses contain an explicit allowlist of public feed metadata. Upload credentials and feed configuration are excluded. Action markup and form keys are excluded from CSV/XML export columns.
* Feed export filters use query parameters, and exports include all matched records instead of the collection provider's default first page. Grid pagination parameters also use query parameters so native controls can change them.
* Feed detail routes use Magento's editor theme, assets, dependency controls, and a separate menu cache. Other Admin routes retain Nebula.
* New Google feeds explicitly default to parent SKU grouping. Legacy empty/zero grouping parameters normalize to `sku` when opening or saving the form; explicit `entity_id` mappings remain unchanged.
* Standard-grid feed names are escaped. A regression test failed on an injected image tag before the fix; browser checks confirmed both grids display the literal text without creating or executing that element.
* After confirming no indexer workers were active, `catalogrule_rule`, `catalog_product_price`, and `catalogsearch_fulltext` were reset and rebuilt. All 11 indexers finished Ready with zero backlog.

## Automated verification

| Check | Result |
| --- | --- |
| Full unit suite | 390 tests, 901 assertions passed |
| Module integration suite | 10 tests, 26 assertions passed |
| Isolated database suite | 4 tests, 18 assertions passed |
| JavaScript suite | 12 tests passed |
| PHP/PHTML syntax | All module files passed |
| Magento XML validation | 24 files passed |
| Module and wiki validation | Passed, including 30 wiki pages |
| Configured Magento coding standard | Zero errors; 3,194 warnings under the repository's warning-tolerant exit policy |
| Composer metadata and whitespace | Strict validation and `git diff --check` passed |
| DI compilation | Passed with Nebula enabled, all 16 Nebula modules disabled, and the exact original configuration restored |
| Database status | All modules up to date |

Commands used the PHP 8.4 binary and the installed application's PHPUnit. The integration methods ran unchanged against the application object manager inside a rollback transaction, rather than through Magento's integration installer. Database tests ran against a disposable, loopback-only MariaDB 11.4 container with synthetic credentials and session-local temporary tables; that container was removed.

Primary reproduction commands from the module repository:

```sh
MAGENTO_ROOT=/Users/matt/code/mageos-latest /opt/homebrew/opt/php@8.4/bin/php /Users/matt/code/mageos-latest/vendor/bin/phpunit -c phpunit.xml.dist
node --test dev/tests/frontend/*.test.cjs
php dev/tests/validate.php
php dev/tests/validate-wiki.php
php dev/tests/validate-magento-xml.php /Users/matt/code/mageos-latest
composer validate --strict --no-check-publish
git diff --check
```

## Browser and runtime acceptance

| Surface | Verified behavior |
| --- | --- |
| Native Nebula grid | Records and labels, name filtering, ascending/descending sort, two pages without overlap, create navigation, file links, and returning from the editor |
| Native bulk actions | Clone, enable, disable, and delete against synthetic records, through the confirmation UI |
| Export | A name-filtered CSV contained exactly the matching feed and no form key; a rollback-isolated 25-record export returned all 25 while the ordinary grid provider returned 20 |
| Native create and editor | A Generic feed was created from Nebula, edited in the standard layout, saved, and returned to the native grid |
| Google editor | All 12 tabs opened; repeated saves preserved SKU grouping; schedule and upload settings persisted; password reloaded masked and decrypted to the original synthetic secret while remaining encrypted in the database |
| Product preview | `atlas-pouf` preview succeeded with 29 mapped fields and no error message |
| Read-only role | Native grid visible; create/configure/run/selection controls absent; save, generate, enable, disable, clone, and delete POST requests all returned HTTP 403 |
| Standard Admin | Existing grid and records rendered with Nebula modules disabled; standard mass clone succeeded; a new Google feed was created and saved with `sku` grouping |
| Feed-name escaping | HTML-like names remained literal text in both grids; no image element or event execution occurred |
| Generation | Generic: 160 rows/17 columns; Google: 160 rows/29 columns; Local Inventory: 165 rows/7 columns. Row widths and ID/store-code keys were valid |
| Queued generation | Native Run Now queued the synthetic feed. An explicit CLI run completed 160 rows and removed its queue entry |

One synthetic queued run left a partial temporary file during the configuration-switching period. Its worker had exited. A subsequent explicit CLI run recovered and completed the feed. This verifies recovery and explicit processing; it does not establish an unattended cron soak. A pre-existing cron log contained a September 9 Widgetkit error, which was not treated as a current failure.

Screenshots, structured browser checks, output files, test logs, and installed-source hashes are retained in the private evidence directory. Early failed probes and diagnostics remain as history; the final assertions and final-state files identify successful acceptance. A test fixture's read-only role initially lacked its two-factor permission and was corrected before the ACL checks.

## Cleanup and limits

The exact original application configuration was restored, including all 16 Nebula module states. The original two-factor provider setting was restored. Synthetic feeds 107, 108, 109, 146, 147, and 148, their children, both temporary Admin accounts, and the temporary role were removed; clone 114 had already been removed by the native delete test. The generated public test file was retained privately and removed from the public directory. Browser sessions and saved credentials were removed.

A database comparison against the pre-test backup confirmed the original feed 63 and all 65 of its configuration rows were unchanged. It still has no upload, schedule, or queue rows. The final instance contains only that original feed. All indexers are Ready and database setup is current. Code installation parity is recorded in `final-candidate-manifest.json`.

This verifies Mage-OS 3.5.0 with Nebula 0.9.0 and the standard Admin with Nebula modules disabled. It does not certify every Nebula version, custom Admin child theme, separate Magento/PHP matrix, every optional-widget value combination, or a long-running scheduler. The broader storefront, MSI, credential, batch, and FTP/SFTP tests from the [preceding acceptance report](2026-09-25-mageos-3.5-acceptance.md) remain relevant; those entire matrices were not repeated for these Admin changes. No Merchant Center ingestion or external destination was exercised in this follow-up.

For rollback, retain the current candidate, restore `module-before.tar.gz`, regenerate DI, and clean caches. The database backup is retained as evidence; restoring it is unnecessary for this code rollback and would overwrite subsequent data. Keep the widened upload-password column from the preceding security fixes.
