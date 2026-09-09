# ShoppingFeed review and local acceptance, 2026-09-09

Historical review baseline (the later [full-feed validation](2026-09-09-full-feed-validation.md) supersedes the configurable-selection limitation and temporary empty-table state below).

Reviewed commit: `5be26f1bc20bb01025722aeb21506e9f53e5bff2`, branch `fix/hyva-simple-autoselect`.

The Hyvä simple-product fix is committed, installed, and locally verified. The six findings below have now been resolved in the fixes recorded with this report and installed on local Magebox. They are in code that predates this commit. The reported frontend failure is a Hyvä compatibility issue; there is no evidence establishing a Mage-OS 3.5 regression.

## Resolution verification

All six findings are fixed locally. The changes restrict metadata selection to eligible configurable children, isolate queue lookup state, sanitize delimiters after decoding, stop SFTP writes when directory selection fails, preserve ports in simple and grouped product links, and explicitly encode scalar settings so their type survives persistence. New JSON-looking scalar settings use an internal string prefix; ordinary text retains its storage format, and legacy plain text and JSON arrays remain readable without a schema migration.

- **346 PHP tests, 712 assertions passed**, including 20 additional unit cases. Restoring the original production implementations made the new regression selection fail again (15 failures and 2 errors); the fixes were restored afterward.
- Database probes confirmed repeated queue lookups, an initially empty queue followed by inserts, and unfiltered lookups. Two due feeds with the first already queued now create the missing second job.
- Repeated database saves preserve bracket-prefixed text, JSON-looking string values, quoted text, and structured arrays. A real legacy malformed JSON record throws `InvalidArgumentException` with the original model and loads unchanged with the corrected model.
- Seven unauthenticated HTTP cases passed: valid child, disabled child, other-website child, unrelated product, malformed ID, array ID, and absent ID. Valid child metadata is retained; rejected selections use the public parent's metadata.
- The final bounded feed retains port 8080 and has 29 columns in both header and product row. SFTP validation and upload both reject a failed directory change without attempting a write. A real remote SFTP transfer was not performed.
- The updated installation contains the seven changed runtime files and their regression tests. All 518 tracked source files and the new test file match the working checkout. The five pre-existing modified files remain unchanged. Exact hashes are recorded in `fixes-baseline/installed-fixes-sha256.json` beside the pre-fix file copies. No schema or Composer changes were needed.

The five frontend tests also pass. Final browser acceptance on Atlas Pouf returned no JavaScript errors. All four security fixtures were removed, the catalog returned to 43,186 products, all seven module tables are empty, and microdata settings were restored.

The full Magento integration harness was not run against the shared local database. Two database regression methods were added for that harness; equivalent transactional probes were executed locally. The original browser acceptance and the new HTTP security acceptance provide separate storefront evidence.

## Original review findings

### 1. P1: Public microdata can disclose a disabled product

[Block/Product/View/Microdata.php:97](https://github.com/mage-os-lab/module-shopping-feed/blob/5be26f1/Block/Product/View/Microdata.php#L97) loads the request's `aid` as a product ID without checking its relationship to the page product, enabled status, or website assignment. The resulting model supplies the public microdata template with that product's name, SKU, and price.

Confirmed through unauthenticated HTTP on the local store, with microdata temporarily enabled and a temporary Google feed selected. The disabled fixture's own URL returned **404**. A visible fixture's URL with `?aid=43188` returned **200** and embedded the disabled fixture's name, SKU, and price in ShoppingFeed's metadata. The fixtures were unrelated simple products. Both products and the temporary feed were removed, and the original microdata configuration was restored.

Impact: stores enabling this feature can disclose unpublished catalog information through enumerable product IDs. The setting was not enabled in the final local configuration. Fix by resolving `aid` only among eligible children of the current product in the current store, with explicit handling of invalid or inaccessible IDs. Add HTTP coverage for unrelated, disabled, other-website, and valid child products.

### 2. P2: A queued feed prevents other due feeds from being scheduled

[Model/ResourceModel/Generator/Queue/Collection.php:64](https://github.com/mage-os-lab/module-shopping-feed/blob/5be26f1/Model/ResourceModel/Generator/Queue/Collection.php#L64) mutates and loads the same collection on every `getQueue()` call. `clean()` deletes orphan rows; it does not clear loaded items or previous filters. `Cron/Schedule.php` reuses this collection inside its schedule loop.

Confirmed with two due schedules inside a rolled-back database transaction: feed A had a queue entry and feed B did not. The scheduler created **zero** jobs, leaving feed B unqueued. Independently, consecutive lookups for two feed IDs returned the first feed's queue both times.

Use a fresh collection/query for each lookup. Cover an already queued first feed followed by an unqueued due feed, as well as repeated empty lookups.

### 3. P2: Entity decoding can inject a feed delimiter after sanitization

[Model/Product/Filter.php:95](https://github.com/mage-os-lab/module-shopping-feed/blob/5be26f1/Model/Product/Filter.php#L95) decodes HTML entities after replacing the active output delimiter. A single decoded tab survives the whitespace normalization. The default TSV writer joins cells without enclosure.

Confirmed: `cleanField('before&#09;after')` returns a value containing an actual tab, which splits into **two TSV cells**. Product content containing such an entity can shift subsequent fields or cause feed rejection. This is a feed-integrity issue, not a demonstrated code-execution vulnerability.

Decode entities before the final delimiter/control-character sanitation, and test the serialized row with representative decimal and hexadecimal entities and configured delimiters.

### 4. P2: SFTP reports success after failing to enter the destination directory

[Model/Uploader/UploaderAbstract.php:117](https://github.com/mage-os-lab/module-shopping-feed/blob/5be26f1/Model/Uploader/UploaderAbstract.php#L117) ignores the result of `cd()`. The installed Magento SFTP implementation returns the underlying `chdir()` boolean. Both connection validation and upload continue after a false result.

Confirmed with an in-process fake SFTP connection: `cd()` returned false, `checkConnection()` returned true, and `upload()` still called `write()` and returned true. No remote server was contacted. With a writable login directory, this can place the feed in the wrong directory while reporting success.

Require a successful directory change before validation succeeds or writing starts. Test a missing or inaccessible directory and confirm no write occurs.

### 5. P2: Product links lose nonstandard ports

[Model/Product/Mapper/Generic/Simple/Url.php:47](https://github.com/mage-os-lab/module-shopping-feed/blob/5be26f1/Model/Product/Mapper/Generic/Simple/Url.php#L47) reconstructs URLs using scheme, host, and path, omitting the parsed port.

Confirmed in actual generic and Google Shopping sample output: the working product URL is `http://mageos-latest.localhost:8080/atlas-pouf.html`, while the generated link uses `http://mageos-latest.localhost/atlas-pouf.html`. Image URLs retain port 8080. This breaks product links for stores using nonstandard HTTP or HTTPS ports.

Preserve the URL authority, including its port, while retaining the intended store URL and tracking parameters. Cover both default and explicit ports.

### 6. P2: Text settings beginning with a bracket are treated as JSON

The original [Model/Feed/Config.php](https://github.com/mage-os-lab/module-shopping-feed/blob/5be26f1/Model/Feed/Config.php) attempted JSON decoding for every value beginning with `[` or `{`, and accepted every result except boolean false. Invalid JSON can throw during feed loading; a decoder returning null can erase the loaded string.

Correction to the initial evidence: the first feed probe changed only a nested config object, without marking the loaded feed changed. Its empty reload did not prove persisted text was erased. The corrected probe saved an actual legacy config row containing `[plain text default]`. The original model threw `InvalidArgumentException` on this Mage-OS version; the fixed model preserved the text. A separate regression covers decoders that return null. Repeated saves also preserve valid JSON-looking strings and arrays as their respective types.

The implemented fix distinguishes new scalar strings from structured values and retains malformed legacy text. Legacy quoted text remains unchanged.

## Initial verification and limits

The target is `<MAGENTO_ROOT>`, running Mage-OS 3.5.0 on PHP 8.4.24, with the storefront at [the local Magebox URL](http://mageos-latest.localhost:8080/). The module was installed from a Git archive into `app/code/MageOS/ShoppingFeed`. All **518 installed source files** match the commit byte for byte. Composer files and all five pre-existing modified files retain their original hashes.

- PHP unit suite: **326 tests, 622 assertions passed**, using `MAGENTO_ROOT=<MAGENTO_ROOT> php vendor/bin/phpunit -c <MODULE_ROOT>/phpunit.xml.dist` from the Mage-OS checkout.
- Frontend regression suite: **5 passed**, using `node --test dev/tests/frontend/*.test.cjs`. The original templates failed these checks before the fix.
- Real Hyvä page acceptance: existing Atlas Pouf page loads with `require` undefined and no JavaScript errors. A temporary simple product selected dropdown, multiselect, radio, and checkbox values from its URL fragment. The price changed from **$141.50 to $192.44**. The cart retained all four selected options and the **$192.44** unit price. Invalid, empty, and unknown hash values preserved the unselected state and base price without errors.
- Generic, Google Shopping, and local inventory generation each exported one existing product without skipping it. A complete bounded Google feed produced two lines, with **29 columns** in both header and product row. This verifies generation, not Merchant Center acceptance.
- Seven DI service construction checks passed. Path traversal and executable output filename probes were rejected. New upload credentials remained encrypted after a masked-password save with a host edit; the suspected plaintext-password issue was not reproduced.
- `composer audit --locked --format=json --no-interaction` reported no security advisories for the installed local dependency tree. It exited 1 because seven dependencies are marked abandoned: `laminas/laminas-config`, `laminas/laminas-json`, `laminas/laminas-loader`, `laminas/laminas-text`, `mage-os/module-catalog-inventory`, `mage-os/module-inventory-in-store-pickup-webapi-extension`, and `doctrine/annotations`. These are existing platform dependencies; this installation changed no package versions.
- Installation dry run contained seven new module tables and no ALTER or DROP statements. All database validators report up to date, the module is enabled, and storefront/admin entry URLs return HTTP 200.
- Temporary records were rolled back or removed. The catalog returned to **43,186 products**, all seven module tables contain zero rows, the test cart is empty, and the browser session is closed. The only module-enablement change in `app/etc/config.php` is `MageOS_ShoppingFeed => 1`.

The review traced admin action authorization and mutation methods, frontend request/rendering paths, feed persistence, queue scheduling, generation and serialization, path containment, uploader behavior, and representative product mappers. It is not an exhaustive security guarantee. Authenticated admin form submission, actual FTP/SFTP transfer, a full 43,186-product run, and external merchant ingestion were not tested. Existing unit tests and the targeted runtime probes are complementary; the full Magento integration test harness was not run against the shared local database.

The simple-product fix does not add Hyvä configurable selection or Google Ads integration. The tested local configurable page produced no error because the module's legacy configurable block was not rendered there. Separate compatibility work is still needed for those features. PHP 8.4 also emitted deprecation notices when feed defaults passed null to `strtr()` at `Model/Feed.php:319`; generation completed, but this warrants cleanup.

Release preflight subsequently resolved the null-default deprecation by explicitly normalizing column values before sanitization. The regression test failed with the original implementation and passed with the correction. All three default feed types saved and reloaded without deprecation notices in transactional Magebox probes. The later full-feed record covers configurable selection and the regenerated release output; Google Ads on Hyva remains outside this compatibility work.

## Local evidence and recovery

Database backup, original configuration files, schema dry run, installation logs, runtime probe results, HTTP reproduction HTML, cart evidence, and a generated feed sample are retained in `<MAGENTO_ROOT>/var/shopping-feed-review-20260909`.

The compressed pre-install database backup is `database-before.sql.gz`, SHA-256 `891ad10743010b0a901358957a4ab98abfa27f1ce5ff26b0a557881dbe2a25dd`. The recovery instructions in that directory describe how to disable and quarantine this module while retaining its tables. Restoring the full database is a separate operation that would overwrite later local work and has not been performed.

This report accompanies the resolution commit. The fixes are installed only on local Magebox; installation provenance is recorded beside the local verification evidence. At the time this local review was recorded, these fixes had not been pushed or deployed to a remote store. Release publication is tracked separately.
