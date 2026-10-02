# Code Review: UI Component Editor Migration

**Date:** 2026-10-01
**Branch:** `feat/ui-component-editor` @ `9f07e46`
**Scope:** All changes since the 2026-09-30 review baseline (`55ee467`): commit `f8488b1` "Fix security review findings and harden feed handling" and commit `9f07e46` "Migrate feed Admin editors to UI Component forms" (88 files, +3306/-255).
**Method:** Original static review of the diff and surrounding code, data-flow tracing, and local checks without a configured Magento runtime. Reconciled on 2026-10-01 with the later Chrome acceptance, current source, isolated PHP 8.4.24 probes, and focused tests against the available Mage-OS framework. The original check results are retained below. This reconciliation changes documentation only; none of the outstanding code findings is fixed.

## Outcome

**Subsequent local fixes:** the [repair report](2026-10-02-ui-component-editor-fixes.md) records working-tree repairs and new verification for Findings 1, 4, 5, 6, 7, and 8. The findings below describe `9f07e46` as reviewed. That deployed revision has not been replaced by the local fixes.

**The candidate is not release-ready.** The [deployed Chrome run](2026-10-01-ui-component-chrome-acceptance.md) found two blocking regressions: new category mappings can break generation, and promotion dates can be erased after reopening and saving. Those failures take precedence over earlier passing fixture checks.

The September 30 findings were addressed in `f8488b1` with fixes, hardening, or documented intentional behavior; they were not all reproduced vulnerabilities that required code changes. See [the fix verification](2026-09-30-review-fixes.md). No additional exploitable security issue was established by this static review. That conclusion is limited to the inspected paths and is not a guarantee that the migration has no vulnerabilities.

| # | Severity | Disposition | Location |
| --- | --- | --- | --- |
| 7 | High | Browser-confirmed regression, unresolved | `view/adminhtml/web/js/form/categories.js`, category mappers |
| 8 | High | Browser-confirmed data loss, unresolved | `Ui/DataProvider/Feed/Form/Metadata.php`, `Form/Promotions.php`, `Model/Promotions/Provider.php` |
| 1 | Low | Confirmed header-integrity regression for unenclosed output | `Model/Feed.php`, `Model/Generator.php::getHeader()` / `writeFeed()` |
| 2 | Low | Maintenance and coverage gap; blanket deletion is unsafe | Legacy editor files, retained live menu/tree, tests and validator references |
| 3 | Informational | Intentional extensibility contract; validate known settings | `Model/Adminhtml/FeedFormData.php::decode()` |
| 4 | Low | Malformed category rows can raise an uncaught `TypeError`; pre-existing normalizer gap | `Model/Feed/Converter.php::_configTaxonomyDeleteDefaults()` |
| 5 | Informational | Preview SKU validation gap; claimed `type[]` path corrected | `Controller/Adminhtml/Feed/Test.php`, `TestDataProvider.php`, Builder |
| 6 | Medium | Pre-existing hidden microdata default, browser-confirmed | `Controller/Adminhtml/Feed/Builder.php`, `Form/Metadata.php`, `Model/Microdata.php` |

## 1. Fix verification for `f8488b1` (previous review findings)

Each finding from `docs/reviews/2026-09-30-security-review.md` was re-checked against the current code, not just the commit message.

| Prior finding | Status | Verification |
|---|---|---|
| Stored XSS, `find-replace.phtml` | Fixed | Template now uses `$escaper->escapeJs()`, which neutralizes `</script>` breakouts. |
| Column-map array-parameter escaping, `columns-map.phtml:246` | Hardened | The prior report's stated exploit was not reproduced on the installed framework. Explicit `json_encode` flags now include `JSON_HEX_TAG\|JSON_HEX_AMP\|JSON_HEX_APOS\|JSON_HEX_QUOT\|JSON_THROW_ON_ERROR`. Same pattern applied to `category-taxonomy.phtml` (prior informational finding 11). |
| Cast/ternary precedence bug, `Builder.php` | Fixed | New `nonNegativeInteger()` (lines 120-131) accepts only int or digit-string, rejects arrays, negatives, floats, booleans, and integers beyond `PHP_INT_MAX` via `filter_var` with `min_range: 0`. `use_microdata` additionally bounded to 0/1. Both throw `LocalizedException`, which `Save` catches and surfaces as a recoverable admin error. |
| Schedule/status HTML handling | Hardened | Escaper injected, batch limits normalized, and progress validated to 0-100. The persisted `batch_limit` HTML exploit was not established because the database field is already unsigned integer; the raw-rendering hardening was verified separately. |
| Broken `mapMinimumPurchaseAmount`, `Promotions/Provider/Map.php` | Fixed | Recursive `minimumSubtotal()` walks all/any aggregator semantics correctly; strict JSON decode with an actionable `LocalizedException` for legacy PHP-serialized conditions; isset guards on date keys. |
| Log-rotate TypeError, `Generator.php:286` | Fixed | `$rotateKb` validated with a 512 fallback; `system.xml` rotate field gained `validate-number validate-greater-than-zero`. The `unset($product, $adapter, $row)` typo is also fixed. |
| Missing isset guards (`Feed.php:558`, `Status.php:107`, `Map.php`) | Fixed | `!empty($upload['delete'])` at `Feed.php:546`; guards confirmed in `Status.php` and `Map.php`. |
| Unescaped grid HTML, `Ui/.../File.php` and `File/Plugin.php` | Fixed | Both rewritten with realpath containment inside the media directory, `URL_TYPE_MEDIA`, escaped URL and link text, `rel="noopener"`, and no `DOCUMENT_ROOT` usage. |
| CSV formula injection | Acknowledged | Informational in the prior report; export format behavior unchanged by design. |
| CI actions pinned by tag | Fixed | All workflow actions pinned to full commit SHAs. |
| Upload password handling | Preserved | `Upload::beforeSave` still rejects `OBSCURED_VALUE` for new rows and retains the encrypted password for existing rows. The new envelope never transports decrypted credentials (see below). |

## 2. Findings and corrected dispositions for `9f07e46`

### Finding 1 (Low): control characters in column names can break unenclosed output

Commit `9f07e46` removed the `Feed::beforeSave()` cleanup that replaced `\n`, `\r`, and `\t` throughout column-map values with spaces. This preserves structured parameters and literal text for the new editor, but also lets those characters remain in column names.

`Generator::getHeader()` collects the column names. `writeFeed()` then applies the configured enclosure/escaping and joins the cells with the delimiter. With no enclosure, a tab inside a TSV column name adds a column and a newline splits the header. The isolated probe exercised these actual methods: a two-column map produced three parsed TSV header columns with an embedded tab, and only one column in the first record with an embedded newline. Quoted Generic CSV preserved the newline-containing header as one logical two-column record, so the original blanket corruption claim was too broad.

This requires authorized feed configuration and is a data-integrity gap, not an established privilege bypass. Reject control characters in column names at the server-side save boundary with a field-specific error. Preserve literal text and structured directive parameters; do not restore blanket coercion of every column-map value. Add save-to-output regression coverage for tab, newline, carriage return, and configured delimiters/enclosures. No fix was applied.

### Finding 2 (Low): retained editor code obscures active coverage; audit before removal

The default routes now use UI Component forms, but much of the previous form implementation and its tests remain. The original recommendation to delete the whole `Block/Adminhtml/Feed/Edit` tree except the category tree missed another live dependency:

- `Block/Adminhtml/Feed/Edit/Menu.php` is still injected by `Plugin/Adminhtml/FeedEditorTheme.php` for the Nebula standard-editor fallback. Its cache-key separation remains required.
- `Block/Adminhtml/Feed/Edit/Tab/Options/Category/Tree.php` is still instantiated by `Controller/Adminhtml/Feed/SuggestCategories.php`.
- Legacy parameter-renderer class names remain active configuration identifiers in `etc/mageos_shopping_feed.xml` and `etc/adminhtml/di.xml`. The new `Form/Parameters` resolves those names to declarative definitions; it does not instantiate their PHP/PHTML renderers. Preserve the identifiers and custom-definition contract during any file cleanup.
- `HideShippingTab` and `HideOptionsTab` registrations are removed. Old tab templates and the `feed-form.js` / `category-taxonomy.js` widgets no longer implement the default editor, but their aliases and source references remain in the validator and tests.
- Four of the seven frontend `.test.cjs` files target retained legacy behavior. At review time, `admin-ui-form.test.cjs` contained eight tests; the remaining two files cover storefront behavior. The aggregate 28-test result is not 28 tests of the new editor.

Keep the transition files until a scoped dependency audit and replacement coverage justify removal. Include validator references, PHP template tests, external customizations, the live menu/tree, and Nebula fallback acceptance in that audit. Port the meaningful behavior/security tests to the active components before retiring old tests. This finding does not authorize or establish safe deletion of the entire directory.

### Finding 3 (Informational): config sub-keys remain an extension contract

`FeedFormData::decode()` allowlists top-level fields and schedule/upload child fields, but accepts arbitrary keys within `config`. The converter applies submitted settings to the loaded feed while preserving unexposed saved settings. This supports the documented metadata-modifier and custom-setting contract, and the old save path also accepted custom configuration paths.

Unknown keys are not necessarily inert: downstream directives or modules can consume them. Known sensitive settings still require their specific validation, including output-path containment. No new access-control bypass was established here; saving requires the existing feed save permission.

Do not add a blanket built-in-only config allowlist as a routine hardening change. It would conflict with custom settings and require an explicit extensibility design. Validate known shapes and constraints, preserve unknown configuration, and ensure modifier authors do not return secrets in provider data. The implementation guide documents that responsibility.

### Finding 4 (Low, pre-existing normalizer gap): malformed taxonomy rows can escape Save recovery

The category-map key is not in `FeedFormData::ROW_CONFIG`, so an envelope containing `categories_provider_taxonomy_by_category: {"x": "str"}` passes `decode()`. `_configTaxonomyDeleteDefaults()` then reads `d` and `p` without validating each row.

The PHP 8.4.24 probe returned `TypeError: Cannot access offset of type string on string`. `TypeError` does not extend `Exception`, and Save catches `LocalizedException`, `RuntimeException`, and `Exception`, not `Throwable`. The original statement that Save catches this case and recovers the form was incorrect. An empty array row instead emitted an undefined-`d` warning, promoted to an exception by the test bootstrap. These are distinct error paths.

The normalizer itself predates the migration and also accepted decoded legacy JSON; the new envelope did not add row validation for this key. The observed failure occurs before `feed->save()`. This probe did not submit a live HTTP request or mutate a store record.

Validate the category container, row shape, and required fields at the shared conversion boundary, returning a `LocalizedException` for malformed input. Reject rather than silently discard bad rows. Cover the JSON-envelope and legacy JSON-string request forms while preserving valid inactive/unknown category mappings. Catching every `Throwable` in Save would only mask the missing validation.

### Finding 5 (Informational): preview input validation needs a narrower description

Test first passes the request parameters to Builder. Builder rejects a non-string `type` with `LocalizedException('Invalid feed type.')` before loading a feed. The isolated probe and existing Builder regression tests confirm this. A `type[]=x` request therefore does not reach the reported `ucfirst($type)` call in the current revision. Test does not handle that Builder exception into a dedicated preview validation message.

A separate gap remains for `sku`: Test passes it to product lookup without checking for a scalar string or a valid product ID. In the installed Mage-OS resource, SKU lookup casts it to string before binding it, so an array can raise an array-to-string warning; the original universal SQL-exception claim was not established. The ID branch passes the input directly to `load()`. `TestDataProvider` also casts `sku` to string; its `id`/`sku` type selection only normalizes form data after controller processing.

Add controller-level validation for the supported lookup mode and product input, with a clear recoverable message for malformed requests. Preserve valid string SKUs, including numeric SKUs, and validate positive IDs only in ID mode. Both routes retain the feed-view ACL. No injection, information disclosure, or live HTTP response behavior was demonstrated by this review.

### Finding 6 (Medium, pre-existing): hidden `use_microdata=1` default for non-Google feeds

Documented in `docs/reviews/2026-10-01-ui-component-chrome-acceptance.md` and confirmed here by code trace. This is not a migration regression; the old editor had the same Google-only field restriction and the same Builder default. The full impact chain:

1. `Builder.php:77-79`: when `use_microdata` is absent from the form data, it defaults to `1` for every non-`generic` type.
2. `Metadata.php:85`: the `use_microdata` control is only rendered for `google_shopping`. The new provider still projects `use_microdata` into the submitted envelope for all types, so the issue is the hidden initial default, not an invariant that non-Google forms never submit the field.
3. New non-Google feeds therefore save with `use_microdata=1` and no UI affordance to change it. Existing feeds are unaffected on re-save: `Builder` loads the feed after setting the default, so the stored value wins, and the projected envelope round-trips it.
4. `Model/Microdata.php:124` selects the storefront microdata source as *any* feed with `use_microdata=1` for the store, regardless of feed type. A freshly created Meta feed can silently become the store's schema.org microdata mapping source.
5. If another feed already holds the slot, `Plugin/FeedUseMicrodataUniqueByStore::beforeSave` silently flips the new feed to `0` and shows a warning that references a field the user cannot see.

Recommended fix: explicitly define the eligible feed types, default ineligible new feeds to `0`, and preserve existing saved selections during ordinary edits. Do not silently migrate existing stores or broaden storefront eligibility just to expose a missing control. This is a separate behavior change requiring focused default/preservation tests and a changelog note. No fix was applied.

### Finding 7 (High): new category mappings omit IDs used during generation

Chrome created category mappings with priority zero, taxonomy, product type, and exclusion. They persisted in the form, but a valid product preview failed. `categories.js::publish()` writes `d`, `p`, `tx`, and `ty` without `id`; generator sorting reindexes numeric keys and the category mapper then accesses the missing embedded ID. Existing saved entries may already have an ID, so preservation-only fixtures miss the failure. Fix the serializer and prove preview/full generation after creating a new mapping. See the [browser evidence](2026-10-01-ui-component-chrome-acceptance.md#high-new-category-mappings-omit-the-id-required-by-the-generator).

### Finding 8 (High): promotion dates can be erased by an unchanged save

Chrome saved month-first effective/display dates, while the provider expects `Y/m/d`. Reopening showed four blank controls, and an unchanged save persisted four empty strings. Title/counter checks do not cover this. Align the component/provider date contract and test all four values through two saves plus companion generation. Code rollback does not recover erased values. See the [browser evidence](2026-10-01-ui-component-chrome-acceptance.md#high-saving-and-reopening-a-google-feed-corrupts-promotion-dates).

## 3. Inspected protections and their limits

- **`Model/Adminhtml/FeedFormData.php`** (the new trust boundary): strict JSON decode with `JSON_THROW_ON_ERROR` and a recoverable `LocalizedException` on malformed input; envelope shape validation (`config` must be an array, the listed `ROW_CONFIG` collections must contain arrays; category maps are not covered by this list); top-level key whitelisting; child-row field whitelisting for schedules and uploads; `feed_id` stripped from child rows so a crafted envelope cannot reassign children across feeds; unsaved deleted rows filtered so they cannot create records; DynamicRows bookkeeping keys (`record_id`, `position`, `initialize`) removed before persistence; `redact()` keeps decrypted and newly entered passwords out of provider JSON and the recovery persistor; `encodeForProvider()` applies all four `JSON_HEX_*` flags and additionally encodes `$` as `\u0024`, which prevents Magento's UI template engine from interpreting saved text such as `${ ... }` as a template expression while remaining lossless through `JSON.parse`. The unit test proves the Magento metadata sanitizer leaves the envelope byte-identical.
- **`Ui/DataProvider/Feed/FormDataProvider.php`**: recovery persistor merge requires matching `id`, `type`, and `store_id`, so a stale or cross-feed persistor payload cannot overwrite the loaded feed.
- **`Controller/Adminhtml/Feed/Save.php`**: decodes the envelope, re-injects via `setParams()` so the existing observer contract is preserved, catches `LocalizedException` and `RuntimeException` into admin messages, redacts before writing the recovery persistor, and strips `form_key`. Save and Continue flow is covered by the acceptance runs (see Limitations).
- **`Ui/DataProvider/Feed/Form/{Fields,Metadata,Options,Parameters,Promotions}.php`**: declarative metadata; notices and labels are translation strings; `Metadata::field()` and `rows()` apply the save-ACL disabled/add/delete configuration to built-in controls; `Parameters` sanitizes directive notices with `strip_tags` + `html_entity_decode`; `Options` throws on unknown option sources rather than guessing.
- **New JS components** (`provider.js`, `parameter.js`, `categories.js`, `select.js`, `multiselect.js`, `preserve-options.js`, `ordered-rows.js`, `dependent-*.js`, `promotion-counter.js`): the provider submits only the JSON envelope and re-adds deleted persisted child IDs as `{id, delete: true}` without mutating live form data; `preserve-options` keeps missing values (including `__proto__`/`constructor` strings and HTML-looking text) as inert labels.
- **New Knockout templates** (`categories.html`, `parameter.html`, `password.html`, `promotion-counter.html`): saved text uses `text:`, `value:`, or attribute bindings rather than raw `html:` rendering. No direct raw-HTML sink was found in these templates. Custom modifiers/components need their own escaping review; this is not a blanket XSS guarantee.
- **Buttons** (`Back/Delete/Save/Test/RunTestButton`): URLs via `json_encode` in `on_click`; `DeleteButton` checks the `MageOS_ShoppingFeed::delete` ACL.
- **`Ui/DataProvider/Feed/TestDataProvider.php`**: whitelists `type` to `id`/`sku` for the form.
- **`Model/Adminhtml/StockAttributes.php`**: column list built from `describeTable`, no injection surface.
- **Delimiter round-trip**: source model emits a real tab; `Options` and `project()` map it to the two-character `\t` for the UI (the required-entry validator would trim a literal tab); all three consumers (`Generator.php:828`, `Filter.php:89`, and glue selection in `AdditionalImageLink.php:74`) handle both the legacy real-tab storage and the new two-character storage correctly. The earlier fixtures produced identical delimiter serialization. This does not cover malformed headers or the category-generation regression.
- **Layout and UI XML**: edit layout is a single `<uiComponent>` reference; all changed XML is well-formed (xmllint) and passes `dev/tests/validate.php` (26 XML files, 8 feed types).
- **`etc/events.xml` / `etc/di.xml`**: the six tab prepare-form observers and the HideShippingTab/HideOptionsTab plugin registrations are removed; remaining plugins (`MicrodataRemoverPlugin`, `FeedUseMicrodataUniqueByStore`, `RegisterLocalInventoryProcessor`) are generation/frontend concerns and still apply.

## 4. Checks recorded by the original static review

| Check | Result |
|---|---|
| `php -l` on every PHP/phtml file changed since `55ee467` | Pass, zero failures |
| `node --test dev/tests/frontend/*.test.cjs` (node v26.7.0) | 28/28 pass |
| `php dev/tests/validate.php` | Pass: 26 XML files, 8 feed types, isolated Mage-OS runtime identifiers |
| `php dev/tests/validate-wiki.php` | Pass: 36 pages, 36 sidebar targets |
| `xmllint --noout` on every XML file changed since `55ee467` | Pass |

Note: `node --test dev/tests/frontend/` (directory form) exits 1 because node does not auto-discover `.cjs` files in directory mode; the glob form above is the correct invocation.

## 5. Reconciliation checks and limitations

The original static pass did not configure `MAGENTO_ROOT` or run the PHP/XML runtime checks. An unset variable did not establish that Magento was unavailable: `/Users/matt/code/mageos-latest` is available and was used for this reconciliation's isolated probes and focused tests. No database was accessed and no feed records were changed by these probes.

Fresh commands on PHP 8.4.24 / PHPUnit 12.5.33:

```sh
MAGENTO_ROOT=/Users/matt/code/mageos-latest /opt/homebrew/opt/php@8.4/bin/php -d xdebug.mode=off \
  /Users/matt/code/mageos-latest/vendor/bin/phpunit -c phpunit.xml.dist \
  Test/Unit/Controller/Adminhtml/Feed/BuilderTest.php \
  Test/Unit/Model/Adminhtml/FeedFormDataTest.php Test/Unit/Model/Feed/ConverterTest.php
node --test dev/tests/frontend/admin-ui-form.test.cjs
```

Results: **21 PHP tests / 58 assertions passed**, and **8 active-form JavaScript tests passed**. Separate isolated probes confirmed unenclosed header misalignment, valid quoted CSV structure for the same newline input, accepted malformed category-envelope data followed by `TypeError`, Builder's array-type rejection, and literal-dollar encoding. Local probe source and results are `/private/tmp/shopping-feed-review-reconciliation/probe.php` and `probe-results.jsonl`. These probes verify the disputed mechanisms; they are not code fixes or end-to-end HTTP tests.

The September 30 [fix report](2026-09-30-review-fixes.md) records **713** PHP tests, not 722. The later UI Component and Chrome runs record **722 tests / 1,908 assertions**. The original 36-page wiki result above predates the added Admin forms guide; current documentation validation covers 37 pages.

Save and Continue, individual deletion/cancellation, Run Now, View Log, bulk deletion, and clean Generic generation were browser-verified by the [Chrome report](2026-10-01-ui-component-chrome-acceptance.md), not repeated in this reconciliation. Bulk deletion has no confirmation dialog. Category UI round trips passed while generation failed, and promotion date preservation failed. Restricted-role/ACL browser acceptance remains unverified; the earlier statement that acceptance docs proved it was incorrect.

No new browser session, remote CI run, native Nebula bridge acceptance, live cron execution, external upload, provider ingestion, or large-catalog benchmark was performed for this reconciliation.

## 6. Recommended order of work

1. Fix category ID serialization and promotion-date preservation, with failing-before/passing-after tests and the full browser save/reload/generate sequences (Findings 7 and 8).
2. Reject malformed header names and category row shapes at shared server-side boundaries while preserving valid parameter text and custom settings (Findings 1 and 4).
3. Correct ineligible new-feed microdata defaults without silently rewriting existing selections (Finding 6).
4. Validate preview lookup input and provide a recoverable message; preserve numeric SKUs (Finding 5).
5. Audit retained files and replace meaningful legacy-only tests before scoped cleanup. Keep the live Nebula menu, category tree, and renderer-identifier contract until their callers are migrated and accepted (Finding 2).
6. Keep the custom configuration contract; validate known fields rather than adding a built-in-only key allowlist (Finding 3).

The [acceptance plan](../../ACCEPTANCE-TEST-PLAN.md) and [developer guide](../ui-component-editor.md) include these verification and compatibility boundaries. All code findings remain open after this documentation reconciliation.
