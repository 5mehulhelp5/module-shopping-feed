# Review fixes, 2026-09-24

Baseline: `6c69b2887178408342a894846b02c34cf0434e79`. Changes are local and uncommitted. No store installation, schema upgrade, commit, push, or deployment was performed.

## Findings and disposition

| Finding | Result |
| --- | --- |
| 1. Masked upload password becomes plaintext | **Not reproduced in the raw database on the tested framework.** Magento's `prepareDataForUpdate()` omits values equal to the model's stored data, so the original host-only masked save preserves ciphertext. The model hook alone returned plaintext, which does not prove it was written. Hardened the lifecycle to retain ciphertext explicitly, restore the usable in-memory credential after saving, and require replacement of unreadable credentials. A separate repeated-save bug was reproduced: saving the same upload object twice double-encrypted the password. That is fixed. |
| 2. Public, predictable feed URLs | **Intentional delivery behavior with a confidentiality limit.** Kept existing URLs and recipient fetches working. The Admin path field, README, and wiki now state that files are public, filenames are predictable, and SFTP does not remove the local copy. There is no private-output mode. |
| 3. Ciphertext exceeds `varchar(255)` | **Fixed.** Changed the password column to `text`. The baseline database test truncated a long encrypted credential to 255 bytes; the changed schema preserves it and decrypts correctly. |
| 4. Multiple included promotion rules disappear | **Fixed.** The `in` filter receives an array of rule IDs. |
| 5. Interrupted queue waits until tomorrow | **Fixed.** Running rows remain eligible and reserve the feed against duplicate scheduling. After acquiring the existing file lock, the worker reloads the row and skips one another worker already completed. An interrupted run restarts at offset zero, replacing partial output. Completed batch checkpoints retain their offset and are saved only after closing output. Destruction no longer saves partial progress. |
| 6. Column limits split UTF-8 | **Fixed.** Limits count UTF-8 characters with explicit encoding. ASCII, accented text, CJK, and emoji are covered. |
| 7. Microdata chooses an unselected feed | **Fixed.** Without an explicitly selected feed in the current store, no feed-derived metadata is emitted and the native price metadata is preserved. |
| 8. Child row IDs are not scoped to the feed | **Fixed.** Upload and schedule updates/deletes reject rows belonging to another feed and missing nonempty IDs. New rows remain supported. |
| 9. URL values become jQuery selectors | **Fixed.** Controls use exact ID lookup and literal value comparisons, with numeric option validation. Invalid URI encoding is ignored in both simple and configurable selection. |
| 10. Preview translates product cells and exposes traces | **Fixed.** Cell values and mapped column names are escaped without translation. Generation exceptions/errors are logged through the application logger, with a generic message in the Admin preview. |

## Verification

PHP 8.5.9 with the local Mage-OS framework and PHPUnit 12.5.33:

* Unit suite: **376 tests, 786 assertions passed**.
* Resource/database tests: **4 tests, 18 assertions passed** against disposable MariaDB 11.4.12, session-local temporary tables, and a synthetic encryption key. These exercise actual resource loading/saving and `Feed::saveUploads()`, including raw-column inspection.
* Frontend suite: **9 tests passed**.
* Additional DOM acceptance using Magento's actual jQuery and `jquery.parsequery` in jsdom: **8 cases passed** for valid, selector-like, and malformed fragments across simple and configurable products.
* Magento XML schema validation: **23 files passed**.
* Module validation, wiki validation, Composer metadata validation, PHP syntax checks, and `git diff --check` passed.

New regression tests were also run against an isolated archive of the baseline. The database suite reproduced double encryption, 255-byte truncation, and the missing same-day queue item. The ordinary masked-save raw-column check passed on both versions. Unit and filesystem regressions failed on the original behavior, including partial output being appended on restart.

Reproduce from the module root:

```bash
MAGENTO_ROOT=/path/to/magento php /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
SHOPPING_FEED_TEST_DB_PORT=<disposable-localhost-port> MAGENTO_ROOT=/path/to/magento \
  php /path/to/magento/vendor/bin/phpunit --bootstrap Test/Unit/bootstrap.php Test/Database
node --test dev/tests/frontend/*.test.cjs
php dev/tests/validate-magento-xml.php /path/to/magento
php dev/tests/validate.php
php dev/tests/validate-wiki.php
composer validate --strict --no-check-publish
git diff --check
```

See the development wiki source for the disposable database requirements. The database tests do not bootstrap an installed store or read its credentials.

## Installation limits

The password column change requires the normal `bin/magento setup:upgrade` step when this code is installed. A full installed-store upgrade, generated DI compilation, authenticated Admin/browser acceptance, external FTP/SFTP transfers, and recipient ingestion were not performed. The native-metadata plugin has new injectable dependencies; include normal DI generation and storefront acceptance in deployment checks.

Retry exclusion still relies on the existing shared filesystem lock. Independent hosts without a shared lock filesystem are outside this concurrency model. Repeated generation failures retry on subsequent worker runs; no retry cap or backoff policy was added. Public feed access remains intentional and must be considered before mapping confidential attributes.
