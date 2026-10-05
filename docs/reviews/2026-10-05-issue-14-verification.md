# Issue #14 local verification

The [reported malformed-attribute regression](https://github.com/mage-os-lab/module-shopping-feed/issues/14) is reproduced and corrected locally. The candidate was tested on `fix/issue-14-malformed-markup` in `/private/tmp/shopping-feed-issue-14`, based on `main` commit `7333edacf3148035ab9da968524fd501d0639160`. The original checkout is unchanged.

## Correction

`Filter::removeMarkup()` previously required attribute quotes to pair and allowed a quoted match to cross subsequent tags. The three reported examples either retained an image tag or lost the following heading. Quoted matches now stop at the beginning of another tag, while allowing literal comparisons in attributes. A second pass removes remaining tag-shaped markup containing stray quotes.

The issue's suggested patch initially corrected the reported inputs but retained valid image tags containing `a < b`. The final matcher also handles those comparison attributes. Script/style removal, entity decoding, delimiter handling, and output limits retain their existing order.

Seventeen new data-provider cases cover the exact report, both quote characters, unclosed container attributes, escaped and double-escaped markup, limits after cleaning, literal comparisons, and quoted comparison attributes. Against unchanged `main`, the final focused suite produces nine assertion failures and no errors. Restoring the candidate passes 34 tests and 46 assertions.

## Validation

| Check | Result |
| --- | --- |
| Mage-OS 3.5.0, PHP 8.5.9, PHPUnit 12.5.33 | 863 tests, 2,254 assertions |
| Magento Open Source 2.4.8, PHP 8.4.24, PHPUnit 10.5.65 | 863 tests, 1,958 assertions |
| Magento Open Source 2.4.7-p10, PHP 8.3.33, PHPUnit 9.6.37 | 863 tests, 1,958 assertions |
| Frontend, CI-policy, and installed-framework form checks | 47 JavaScript tests passed |
| Composer metadata | `composer validate --strict --no-check-publish` passed |
| Consolidated module validation | 26 XML files, eight feed types passed |
| Mage-OS-backed XML schema validation | 26 files passed |
| Wiki source validation | 40 pages and 40 sidebar targets passed |
| PHP 8.5 syntax | All 523 PHP/PHTML files passed |
| Focused Magento2 coding standard | Zero errors; 17 warnings identical to baseline |
| Patch whitespace | `git diff --check` passed |

Run each platform's unit suite from the candidate worktree:

```sh
MAGENTO_ROOT=/path/to/platform php /path/to/platform/vendor/bin/phpunit -c phpunit.xml.dist
```

Other checks use the commands in `CONTRIBUTING.md`, plus:

```sh
MAGENTO_ROOT=/path/to/platform node --test dev/tests/frontend/*.test.cjs dev/tests/ci/*.test.cjs dev/tests/magento-ui-form.test.cjs
php /path/to/platform/vendor/bin/phpcs --standard=phpcs.xml.dist Model/Product/Filter.php Test/Unit/Model/Product/FilterTest.php
```

Evidence is retained under `/private/tmp/shopping-feed-issue-14-evidence`. These checks load platform framework code without installing the candidate or changing a store. Public CI, recipient acceptance, and release verification remain separate. The changelog and wiki source identify this correction as unreleased.
