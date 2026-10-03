# Review fixes, 2026-09-30

Reviewed baseline: `55ee46717406c7e2166ed75e76d27724e9a34486`, branch `feat/1.2-release`. This response accompanies [the original review](2026-09-30-security-review.md), which is unchanged. The verification recorded here was completed before committing and pushing the fixes. The follow-up installed the runtime fixes into the local demo store. It did not include wiki publication or external feed submission.

## Findings and disposition

| Finding | Result |
| --- | --- |
| 1. Find/replace script injection | **Fixed and reproduced before the fix.** Saved values now use JavaScript-context escaping. Rendered-template tests with the installed Magento Escaper show that closing-script payloads cannot create additional script elements. Literal HTML, quotes, ampersands, backslashes, and newlines survive encoding. A related JavaScript bug stripped a literal backslash before an apostrophe when reopening a rule; that also has a failing-before, passing-after regression test. |
| 2. Column-map array parameters | **Hardened; the review's stated exploit mechanism was not reproduced on this framework.** Its JSON encoder delegates to the JSON serializer and escapes slashes, so a plain closing-script payload is already neutralized. The template now explicitly uses all four `JSON_HEX_*` flags and throws on encoding failure, avoiding literal HTML parser state transitions as well. |
| 3. Request cast precedence | **Fixed and reproduced.** Feed IDs and store IDs accept nonnegative integers or decimal digit strings within the PHP integer range. Malformed strings, arrays, negative values, and overflow are rejected before model creation/loading. Microdata accepts only zero or one; the type must be a string when supplied. |
| 4. Schedule grid HTML | **Hardened.** Schedule text is escaped while preserving only the intended line breaks. Batch limits are normalized on save and cast when formatted. The reported persisted HTML vector is overstated: `batch_limit` is already an unsigned integer in the database schema. Raw grid rendering was reproduced with a malicious fixture. |
| 5. Promotion condition parsing | **Fixed and reproduced under Magento's warning-to-exception handler.** JSON condition trees are traversed recursively. ALL combines use the strongest minimum; ANY combines use the weakest, and an alternative without a minimum removes the global threshold. Negated/unsupported conditions do not invent a lower bound. Malformed or legacy non-JSON condition data gets an actionable error asking the merchant to resave the cart rule; PHP object deserialization was not introduced. Incomplete date fields and absent/malformed promotion widget data no longer raise undefined-key or array-type errors. Empty promotion configuration produces a header-only file. |
| 6. Log rotation TypeError | **Fixed and reproduced.** Invalid, nonpositive, or nonfinite settings fall back to the existing 512 KB default. The Admin field requires a positive number. Regression tests verify that malformed settings neither abort generation nor prematurely erase the existing log. |
| 7. Missing keys and cleanup typo | **Fixed and reproduced where behavioral.** An omitted upload-delete flag means retain/save the owned upload. Processing rows without valid progress show the status label; progress and status output cannot inject grid HTML. The generator cleanup now references the actual adapter variable. |
| 8. URL and option edge cases | **Fixed malformed URL handling.** Invalid product/base URLs return an empty mapping, and invalid option links cannot become fragment-only links. The empty-search `str_replace` claim is incorrect: PHP leaves the string unchanged. An explicit empty/null early return was still added. |
| 9. File grid HTML and paths | **Fixed and reproduced.** Links use the configured media base URL, preserve nested `pub` directories, escape URLs/text/statistics, and use `rel="noopener"`. Existing files must resolve within media; outside-media promotion paths are omitted. No `DOCUMENT_ROOT` dependency remains, and the missing-file label is translated. |
| 10. Spreadsheet formulas | **Intentional data preservation, documented.** No apostrophe/tab prefix is added to product or promotion values because it changes data sent to recipients. README and configuration documentation explain importing spreadsheet columns as text with formula evaluation disabled. |
| 11. Other hardening | **Addressed.** Taxonomy script data uses explicit HEX encoding. Every direct CI action/reusable-workflow reference is pinned to an upstream-verified full commit hash. Invalid memory-limit strings and missing/malformed shipping-method lists are handled without warnings. Sequential taxonomy IDs remain unchanged because they are UI keys. |

## Verification

PHP 8.4.24, PHPUnit 12.5.33, and the framework from `/Users/matt/code/mageos-latest`:

* Unit suite: **713 tests, 1,877 assertions passed**.
* JavaScript suite: **20 tests passed**.
* PHP syntax: **488 PHP/PHTML files passed**; the subsequently edited integration and provider test files also passed syntax checks.
* Module consolidation: **24 XML files and 8 feed types passed**.
* Magento XML schema validation: **24 files passed**.
* Wiki validation: **36 pages and 36 sidebar targets passed**.
* Composer metadata, CI YAML parsing, all **9** direct action/workflow SHA references, and `git diff --check` passed.

The behavioral regressions were observed failing before their respective fixes. Template tests render the complete affected templates with Magento's real Escaper; filesystem tests cover real files and path containment; JavaScript tests exercise literal field values.

A read-only application bootstrap probe loaded the current source over the installed demo module. Seven changed classes were constructed against real Magento services. The probe verified missing-progress grid rendering, malformed-ID rejection before model loading, `50.00 USD` for an actual SalesRule model with a subtotal condition, and an existing feed file's grid URL with malicious date text escaped. No feed, product, rule, or configuration records were saved.

Reproduce the automated checks from the module root with the indicated PHP version:

```bash
MAGENTO_ROOT=/path/to/magento php /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
node --test dev/tests/frontend/*.test.cjs
php dev/tests/validate.php
php dev/tests/validate-magento-xml.php /path/to/magento
php dev/tests/validate-wiki.php
composer validate --strict --no-check-publish
git diff --check
```

The local runtime probe is `/private/tmp/shopping-feed-security-review-20260930.php`; it contains the source and demo-store paths used above.

## Local installation follow-up

Installed **19 runtime files** into `/Users/matt/code/mageos-latest/app/code/MageOS/ShoppingFeed`. The preflight verified each target matched the baseline before replacement. Unrelated store, test, and documentation changes were preserved. The original module and generated DI files are backed up in `/private/tmp/shopping-feed-security-install-20260930-132752/module-and-di-before.tar.gz`; the same directory contains the before/after SHA-256 manifest and a scoped `restore.py` rollback script.

* `php -d memory_limit=2G bin/magento setup:di:compile` passed, regenerating metadata and interceptors for the new constructor dependencies.
* `bin/magento cache:clean config layout block_html` passed.
* Installed-store integration suite: **14 tests, 30 assertions passed**. This used the installed application with a suite-wide database rollback transaction, rather than Magento's disposable-database integration runner. Row counts and content hashes for all existing feed, configuration, queue, schedule, and upload rows matched after rollback. Evidence: `integration-isolation.json` in the backup directory.
* Integration DI checks now cover all four changed grid classes and force PHP 8.4 lazy objects to initialize, preventing constructor failures from passing unnoticed.
* The installed runtime probe passed through compiled DI with no source autoloader override or manual constructor substitution. It confirmed current installed class paths, missing-progress grid rendering, malformed-ID rejection, a `50.00 USD` promotion threshold, and escaped file-grid output. Probe: `/private/tmp/shopping-feed-security-installed-20260930.php`.
* The complete repository coding-standard gate passed with **zero errors and 3,443 warnings**; the configured gate permits warnings. Its only new error was an implicit `strpos` return-value comparison in shipping-method handling. This was changed to an explicit false check, verified by the focused shipping tests, and synchronized to the installed module. No constructor signature changed after compilation.

The initial read-only probe's stale compiled-metadata limitation is resolved by the successful installation and DI rebuild above. A final hash comparison found no differences across all **372 tracked runtime files** between source and the installed module.

One disabled, unscheduled synthetic feed was prepared for browser testing and then removed while waiting for sign-in. After sign-in, a fresh fixture was created and removed after the checks below. Both cleanups verified that every original feed, configuration, queue, schedule, and upload row retained its pre-test hash. Evidence: `browser-fixture-cleanup.json` in the backup directory.

## Authenticated browser acceptance

Chrome checks passed against the installed local Mage-OS 3.5.0 store, with the Nebula feed list and the standard feed editor:

* The feed list loaded with the six original feeds and their existing file links.
* A disabled fixture with no schedules or uploads carried a closing-script payload in find/replace text and a column-map array parameter. The payload's only action, if executed, would have set a test attribute on the page. That attribute was absent both before and after save/reload.
* The editor displayed the payload as literal field/option text. Quotes, ampersands, and backslashes were preserved.
* **Save and Continue Edit** completed with the success message. Find text, replacement text, and the array parameter retained their exact values after reload.
* Google taxonomy autocomplete returned matching Furniture suggestions after the JSON encoding change. This read-only check used an existing Google feed; the transient field edit was discarded without saving.
* The synthetic fixture was removed. The refreshed list again showed exactly six original feeds, and database row hashes confirmed their data was unchanged.

The standard grid column classes were exercised through installed compiled-runtime and integration checks; this browser session used Nebula's feed list. Browser acceptance does not imply every Admin theme or platform version was retested.

## Remaining verification limits

Additional PHP/platform versions, GitHub Actions, and external recipient ingestion were not part of this local verification. Pinning the repository's direct workflow references does not audit or pin every dependency inside third-party workflows.
