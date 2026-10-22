# PR 24 verification: product exclusion attribute and store-view option labels

Prepared locally on October 22, 2026. The worktree `/Users/matt/code/module-shopping-feed` was at `79b6dbc`, the head of [PR 24](https://github.com/mage-os-lab/module-shopping-feed/pull/24) on `fix/product-exclusion-store-labels`, with `main` at `7f62ecd`. No tracked file was changed during verification. GitHub CI for the PR head was green across all 38 checks before this pass started.

## Result

No high-severity defect and no security issue was found in the diff or in a sink sweep of the module. One low-severity behavioral regression was confirmed in the option filter for non-standard sources, plus two low-severity hardening and test items and several pre-existing observations. None blocks the PR; the regression is a one-line fix.

## Checks run

Tooling: PHP 8.4.26 and 8.5.11 (unit suite), PHP 8.1 syntax on changed files, pinned development tools from the `chore/module-standards-compliance` checkout through a temporary `vendor` symlink (same `composer.json`, removed afterwards), Mage-OS 3.6.0 store copies under `/private/tmp`.

| Check | Result |
| --- | --- |
| Unit suite, PHP 8.4 and PHP 8.5 | 950 tests, 2611 assertions passed on both |
| Module integration suite against the functional Mage-OS 3.6 store copy (fresh install of `shopping_feed_issues_22_23`) | 34 tests, 110 assertions passed |
| Probe integration tests (below) | 4 tests, 21 assertions passed |
| Disposable-database persistence tests (`Test/Database`, temporary tables on the local MySQL 8.4 container, database created and dropped for the run) | 4 tests, 18 assertions passed |
| PHPStan level 8 with reviewed baseline | No errors; baseline shrank by one entry, growth check passed |
| Magento coding standard (pinned 41) | Passed; only tool deprecation notices |
| Composer metadata, wiki validator, PHP lint (whole tree on 8.5, changed files on 8.1), `git diff --check` | Passed |
| Consolidation validator on a clean `git checkout-index` export | Passed; an injected unknown `Setup/Patch/Data/Rogue.php` was rejected |
| Node checks with `MAGENTO_ROOT` set | 84 passed |
| Sink sweep (`exec`, `eval`, `unserialize`, raw SQL interpolation, `@noEscape`, request-driven file paths) | Nothing new; `@noEscape` uses are the secure renderer, form HTML and button HTML |

Magento behavior the fix depends on was read in the Mage-OS 3.6 vendor tree rather than assumed: `Table::getAllOptions()` keys its option cache by the attribute's store ID and the option collection falls back to the admin label through `IF(tsv.value_id > 0, tsv.value, tdv.value)`; `Catalog\...\Eav\Attribute::getStoreId()` only consults a data object when an admin form attached one; `EavSetup::addAttribute()` assigns a grouped attribute to every existing set; `PatchReader` discovers `Setup/Patch/Data/*.php` non-recursively, matching the validator's allowlist glob.

## Probe tests

Written to the scratchpad (`Pr24ProbeTest.php`), run against the installed integration database with cleanup disabled. They cover what the PR's own tests leave open.

| Probe | Observed |
| --- | --- |
| Configurable children with a second store view: `Skip from Being Submitted` Yes only in the second store | Child excluded from the second-store feed only; default-store feed unchanged |
| Yes in All Store Views on a second child, then an explicit No override on the first child in the second store | Default-scope Yes excluded everywhere; the No override restored that child in the second store only |
| Yes on the configurable parent | Every child row suppressed in both stores |
| Core non-Table sources through the changed select branch: `country_of_manufacture`, `status`, `visibility`, `tax_class_id`, `quantity_and_stock_status`, plus the Boolean exclusion attribute mapped as a column | `United States`, `Enabled`, `Catalog, Search`, `Taxable Goods`, `In Stock`, `No` |
| Multiselect containing a deleted option ID | Stale ID dropped, remaining labels kept in saved order |
| Shared attribute store context after generation | Unchanged |
| Attribute set created after the patch from the default skeleton | Field present in its `Shopping Feed` group |
| Patch `apply()` after the attribute was removed | Field recreated |

## Live store

On the upgraded functional store copy (`shopping_feed_functional_22_23`, two fixture products, English and Chinese feeds): CLI previews returned `SF Test Brand` / `Red, Blue` for the English feed and `本地品牌` / `红色, Blue` for the Chinese feed. Inserting a Chinese-only `Yes` value for product 3 left the English preview unchanged, made the Chinese preview and a full Chinese generation skip the product with the logged reason `'Skip from Being Submitted' = 'Yes'`, and the row was then deleted; the attribute value rows matched the pre-test snapshot exactly and the Chinese feed was regenerated back to two rows. Database state checked: the patch is registered once, the attribute is `int`/`boolean`/Boolean source, default `0`, store scope, visible, all product types, and assigned to both product attribute sets.

## Findings (low severity)

1. **Regression, confirmed.** `Model/Product/Adapter/AdapterAbstract.php:900` keeps every option whose `value` is not the empty string. A source option without a `value` key throws `Undefined array key "value"` under Magento's error handler on every supported PHP version, and `value => null` throws `Using null as an array offset is deprecated` on PHP 8.5 (on older PHP it is stored under the empty key). The previous code skipped both silently through the option object's null `getValue()`. Magento's Table source and the core sources always provide `value`, so this only affects custom source models. Reproduced with a throwing error handler on PHP 8.1, 8.4 and 8.5. Suggested fix: `if (!is_scalar($item['value'] ?? null) || $item['value'] === '') { continue; }`, which also turns the pre-existing optgroup crash (array values, `TypeError` before and after the PR) into a silent skip.
2. **Hardening.** `dev/tests/validate.php:182` uses `glob(...) ?: []`, so a `false` return from `glob()` would pass the patch allowlist check with nothing inspected; the replaced `!is_dir()` check failed closed. On this platform an invalid pattern returned an empty array rather than `false`, so the exposure is theoretical. Assert the result is an array.
3. **Tests.** Neither `Test/Unit/Setup/AddSkipSubmitAttributeTest.php` nor `Test/Integration/ProductAttributeTest.php` asserts `user_defined` or `sort_order`, so dropping `user_defined => true` keeps every test green while turning the field into a non-deletable system attribute. `AttributeSelectValueTest::attribute()` still mocks `getOptions()`, which the implementation no longer calls. The store-scope semantics (default-scope Yes, store-view No override, configurable children, parent exclusion) are covered only by the manual acceptance record and by the probe test above; folding those probe cases into `ProductAttributeTest` would put them in CI.

## Observations (non-blocking, pre-existing)

1. The generation summary and grid report "rows skipped" from adapter-creation failures and validator rejections only. A product excluded by the new attribute is logged by reason but is not counted, so a run that excludes one product reports `0 rows skipped`. With the exclusion now usable, merchants will see this.
2. The attribute is `user_defined`, so Admin can delete it. Data patches run once and this one is not revertable, so a later `setup:upgrade` does not recreate it, `module:uninstall` leaves it in place, and after a deletion the exclusion check matches nothing silently. Document this or create it as a system attribute.
3. The patch preserves any existing `mageos_shopping_feed_skip_submit` by existence alone. A merchant-created dropdown or text attribute with that code is kept verbatim and the `== 1` check in `isSkipped()` never fires, with no log line. Consider warning during `setup:upgrade` when `frontend_input` is not `boolean`.
4. Sources that ignore the attribute store (for example `country_of_manufacture`, which uses the process locale) are unaffected by the store swap, and their labels are now cached under the feed's store key. Output is unchanged from before the PR; the store-view label guarantee holds for attributes using Magento's options table.
5. `dev/tests/magento-ui-form.test.cjs` throws without `MAGENTO_ROOT`, so the plain Node command fails until it is set. The file documents this.
6. `dev/tests/validate.php` fails when run from this worktree root because the nested `.claude/worktrees/affectionate-moser-4dc809` checkout still contains legacy identifiers. A clean export passes. CI is unaffected.
7. `.gitignore` uses `/vendor/`, which does not match a `vendor` symlink; a symlinked toolchain shows as untracked.

## Independent review

A second, read-only review pass over the diff ran in parallel with the test runs, with the Mage-OS 3.6 vendor tree for verifying framework behavior and no knowledge of the conclusions above. It reported no high-severity defect and surfaced findings 1 to 3 and observations 2 to 4, each verified against vendor source or a standalone PHP snippet; finding 1 was then reproduced independently on all three PHP versions before being recorded here. It also confirmed, with file references, the points this record relies on: Table-source store keying and admin fallback, the data-object exception in the catalog attribute's `getStoreId()`, per-process `Generator\Cache` scope (which makes the store-keyed cache necessary for alternating-store cron runs), transaction wrapping of data patches in `PatchApplier`, store propagation to configurable, grouped and bundle children, JSON round-trips of option maps, and the property mapping of `global`, `visible`, `required` and `default`.

## Fixes applied after review

Applied on the PR branch on October 22, 2026, after the findings above were accepted, and committed as `30f8bc5` (not pushed at the time of writing):

- `Model/Product/Adapter/AdapterAbstract.php`: the option loop now skips source rows whose `value` is missing, null, non-scalar (option groups) or the empty string, instead of reading them. Finding 1 closed; the pre-existing option-group `TypeError` becomes a silent skip.
- `dev/tests/validate.php`: a `false` from `glob()` is now a failure of its own instead of an empty allowlist check. Finding 2 closed.
- `Test/Unit/Setup/AddSkipSubmitAttributeTest.php` and `Test/Integration/ProductAttributeTest.php`: assert `user_defined` and `sort_order` (100 on every set assignment). `Test/Unit/Model/Product/Adapter/AttributeSelectValueTest.php`: dead `getOptions()` mock removed; new case runs the adapter under a throwing error handler against a source returning a row without `value`, a null value, a nested option group, the empty option, a normal option and value `0`, expecting `Brand, Zero`. Against the unfixed adapter the case errors with `Undefined array key "value"`. Finding 3 closed.
- The four probe scenarios are now `ProductAttributeTest` methods: store-view exclusion on configurable children and the parent, core non-Table sources with a stale multiselect ID, attribute sets created after the upgrade, and patch re-application after deletion.

| Check after the fixes | Result |
| --- | --- |
| Unit suite, PHP 8.4 and 8.5 | 951 tests, 2614 assertions passed |
| Integration suite, fresh install, functional store copy with the fixed adapter synced in | 38 tests, 133 assertions passed |
| PHPStan level 8 and baseline growth check | No errors; baseline still one entry smaller than `main` |
| Magento coding standard, full gate | Passed after wrapping one 121-character line |
| Consolidation validator on a clean export, with and without an injected rogue patch | Passed; rogue patch rejected |
| PHP 8.1 lint of changed files, `git diff --check` | Passed |

## State after verification

The five files above are committed in `30f8bc5`; nothing else changed. The temporary `vendor` symlink was removed. The functional store copy and its database are as found, except that the Chinese feed output file was regenerated with the same two rows. The `shopping_feed_issues_22_23` integration database was reinstalled by the suite run, as it is on every run. The throwaway `shopping_feed_test` database was created and dropped. Probe sources and logs remain in the session scratchpad.
