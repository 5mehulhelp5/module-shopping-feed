# Product attribute fixes for issues #22 and #23

Prepared locally on October 21, 2026, from main `7f62ecd` on `fix/product-exclusion-store-labels`. This record covers verification completed before the commit, push, and PR.

## Changes

- [Issue #22](https://github.com/mage-os-lab/module-shopping-feed/issues/22): `AddSkipSubmitAttribute` creates `mageos_shopping_feed_skip_submit` as an optional, store-scoped Yes/No product attribute, default No, in the Shopping Feed group of every existing product attribute set. An existing attribute is left untouched. Run `bin/magento setup:upgrade` when installing this fix.
- [Issue #23](https://github.com/mage-os-lab/module-shopping-feed/issues/23): option labels come from the attribute source with the feed's store set explicitly. Magento's table source handles admin-label fallback. Cache keys include the store ID; the attribute's previous store context is restored even on an exception. Select and multiselect output retain their existing ordering and separator.
- Include Setup in coding-standard and static-analysis checks. Permit only the new setup patch in the consolidation validator, retaining rejection of unapproved historical patches. Remove one PHPStan baseline entry made obsolete by the option-source change.
- Update the changelog, README, wiki source, translation dictionary, and existing option-source test fixtures. Documentation identifies these fixes as unreleased.

## Verification

The focused regression suite first failed with three missing-patch errors and five option-label failures against the unchanged implementation. After the fixes, it passed 11 tests and 46 assertions. Xdebug Clover coverage confirms execution of `AddSkipSubmitAttribute::apply()` and `AdapterAbstract::getAttributeSelectValue()`.

| Check | Result |
| --- | --- |
| Full PHPUnit unit suite, Mage-OS 3.6.0 / PHP 8.4.26 | 950 tests, 2611 assertions passed |
| Full module database integration suite, Mage-OS 3.6.0 / PHP 8.4.26 | 34 tests, 110 assertions passed |
| JavaScript frontend, CI, and Magento UI form checks | 84 tests passed |
| PHPStan level 8 | No errors; baseline shrank by one entry |
| Magento coding standard | Passed; tool deprecation notices remain |
| Composer metadata | Strict validation passed |
| Consolidation validator | Passed on a clean source snapshot; an injected unknown setup patch was rejected |
| Wiki source | 43 pages and 43 sidebar targets passed |
| Magento XML | 52 files passed |
| PHP syntax | 552 PHP/PHTML files passed; changed runtime files also passed PHP 8.1 syntax checks |
| Whitespace | `git diff --check` passed |

Commands run from the module checkout, with `MAGENTO_ROOT=/private/tmp/shopping-feed-standards-mageos36-20261019/store`:

```sh
XDEBUG_MODE=off /opt/homebrew/opt/php@8.4/bin/php "$MAGENTO_ROOT/vendor/bin/phpunit" -c phpunit.xml.dist
XDEBUG_MODE=coverage /opt/homebrew/opt/php@8.4/bin/php "$MAGENTO_ROOT/vendor/bin/phpunit" -c phpunit.xml.dist --filter 'AddSkipSubmitAttributeTest|AttributeSelectValueTest' --coverage-filter Model/Product/Adapter --coverage-filter Setup --coverage-clover /private/tmp/shopping-feed-issues-22-23/coverage.xml
node --test dev/tests/frontend/*.test.cjs dev/tests/ci/*.test.cjs dev/tests/magento-ui-form.test.cjs
vendor/bin/phpcs --standard=phpcs.xml.dist
vendor/bin/phpstan analyse -c phpstan.neon.dist --memory-limit=2G --no-progress --debug
php dev/tests/check-phpstan-baseline.php --git-ref=origin/main phpstan-baseline.neon
composer validate --strict --no-check-publish
php dev/tests/validate.php
php dev/tests/validate-wiki.php
php dev/tests/validate-magento-xml.php "$MAGENTO_ROOT"
git diff --check
```

The integration runner at `/private/tmp/shopping-feed-issues-22-23/run-integration.py` used a separate disposable database, `shopping_feed_issues_22_23`, and search prefix `sf_issues_22_23`. It temporarily installed the candidate module into the isolated test instance and restored the original module afterward. Tests verify setup patch registration, attribute metadata and assignment to every existing set, preservation of merchant metadata and saved product values, exclusion after save/reload, and generated select/multiselect labels in alternating store views with admin fallback. The initial second-store fixture order triggered Magento's category-index table transaction error; loading the product fixture before creating the second store resolved that test-fixture failure.

Pinned tools were reused through a temporary vendor symlink to the standards checkout; the symlink was removed after verification. The consolidation validator was run in a clean snapshot because the primary checkout contains a pre-existing nested `.claude/worktrees` checkout whose legacy identifiers cause unrelated scan failures. That nested checkout and the pre-existing untracked review document were preserved.

No production store was changed. Initial verification covered attribute metadata, persisted values, and generated output. Browser-based functional acceptance was completed afterward, as recorded below. These local results do not establish the supported Magento/PHP CI matrix; its results must be verified separately for the PR head.

## Functional acceptance, October 21, 2026

Both fixes passed functional testing on an independent copy of the Mage-OS 3.6.0 / PHP 8.4.26 store. All 641 installed candidate files matched the working checkout. The copy used its own filesystem, database `shopping_feed_functional_22_23`, search prefix `sf_functional_22_23`, caches, sessions, and loopback web server on port 8187. No schedules or upload destinations were enabled. Test data comprised two disposable simple products, a separate attribute set, an additional Chinese store view, manufacturer options with and without translations, a multiselect attribute, and two Generic feeds.

| Functional case | Observed result |
| --- | --- |
| Real `bin/magento setup:upgrade --keep-generated` | Installed the exclusion attribute and registered the patch |
| Admin product form | Shopping Feed section rendered the store-view Yes/No field, default No |
| Save Yes in All Store Views, then reload | Value persisted; Product A was absent from both complete feeds; Product B remained |
| Save No in All Store Views | Product A returned to both complete feeds |
| Chinese store view, inherited default | Field showed No, disabled, with Use Default Value checked |
| Chinese-only Yes override, save and reload | Stored a separate store-2 value of 1; Product A was absent only from the Chinese feed |
| Restore Use Default Value, save and reload | Removed the store-2 override row; both feeds included Product A again |
| Select labels | English feed contained `SF Test Brand`; Chinese feed contained `本地品牌` |
| Multiselect labels and ordering | English contained `Red, Blue`; Chinese contained `红色, Blue`, in saved selection order |
| Missing translations | Both feeds retained `SF Fallback Brand` and `Blue` from admin labels |
| Alternating stores in one process | Chinese, English, Chinese generations retained the correct labels without cross-store cache contamination |
| Admin Test Feed by SKU | Rendered Product A with the Chinese brand and multiselect labels |
| Real CLI SKU previews for both feeds | Returned the expected labels and left both complete output-file hashes unchanged |
| Admin Run Now | Confirmed the action, observed a real pending queue row and Pending status, then processed only that feed with the CLI worker; queue row was consumed and Admin showed Completed |
| Queued full output | Byte-identical to the previously verified Chinese TSV file |
| Second `setup:upgrade` | All 11 checked product, attribute, and feed table counts/hashes remained unchanged; patch registered exactly once |
| Browser errors | No uncaught browser errors recorded in the product/grid or preview tabs |
| Original review instance | All 11 checked product, attribute, and feed table counts/hashes and the original installed module files remained unchanged |

Verification parsed 16 complete feed generations and checked three SKU previews: two CLI previews and one Admin preview. Five product states were each generated in Chinese, English, Chinese order, followed by the Admin-queued full CLI run. TSV artifacts retain the generated UTF-8 BOM and tab delimiter.

Local evidence and reproducibility scripts are under `/private/tmp/shopping-feed-issues-22-23/functional/`:

- `evidence/*-results.json` and `evidence/*.tsv`: exact row sets, translated/fallback values, and output hashes for each state.
- `evidence/chinese-override-persistence.json` and `evidence/inherited-default-persistence.json`: stored and effective exclusion values.
- `evidence/admin-pending-queue.json`, `evidence/admin-completed-queue.json`, and `evidence/queued-cli-full-generation.log`: Admin-to-worker queue evidence.
- `evidence/second-upgrade-{before,after}.json`: repeat-upgrade data comparisons.
- `original-data-baseline.json` and `evidence/original-data-after.json`: original-instance comparison hashes.
- `evidence/file-verification.json`: candidate parity and original-module preservation.
- `evidence/exclusion-saved-yes-context.png`, `evidence/chinese-override-saved.png`, and `evidence/admin-chinese-preview.png`: visually inspected Admin screenshots.
- `original-database.sql`: private backup of the original disposable review database, permissions 0600.
- `fixtures.php`, `verify-generation.php`, `verify-state.php`, `verify-queue.php`, `verify-idempotence.php`, and `verify-original.php`: local fixture and verification scripts.

Two initial fixture issues were corrected before acceptance: manufacturer was not assigned to the new attribute set, and the output parser initially assumed CSV rather than the configured TSV. Neither required a module change. The local Admin retained its pre-existing invalid-indexer warning; the tested product-save and feed-generation paths passed. This record covers the two changed product-attribute flows, not the complete release acceptance plan, every product type, recipient acceptance, or the platform CI matrix.

The browser session and temporary web server were closed after testing. The independent test database, store copy, fixtures, and evidence were retained for review. No additional implementation changes, commit, push, or publication were performed during functional testing.
