# Development and CI

The repository validates module identity, configuration integrity, PHP behavior, and supported Magento-family platforms. Run focused checks before requesting review.

> Documentation baseline: release `v1.1.0`, with explicitly marked unreleased `9f07e46` editor notes. Last reviewed: 2026-10-02.

## Unreleased editor implementation and evidence

The default forms in `9f07e46` live in `view/adminhtml/ui_component/mageos_shopping_feed_form.xml` and `mageos_shopping_feed_test_form.xml`, with providers under `Ui/DataProvider/Feed` and controls under `view/adminhtml/web/js/form`. Use `ShoppingFeedFormModifierPool` for custom metadata/data and the repository's `docs/ui-component-editor.md` for the contract. Previous form files remain for transition; their passing tests do not prove the replacement form. The legacy menu and category tree still have live callers, and renderer class names remain active configuration identifiers. Audit those dependencies and validator/test references before removing files.

The deployed acceptance run passed **722 PHP tests / 1,908 assertions**, **28 JavaScript tests**, and schema validation of **26 XML files**, then found category-generation and promotion-date regressions in Chrome. Subsequent local repairs are recorded in `docs/reviews/2026-10-02-ui-component-editor-fixes.md`; they have not been deployed to `mageos-latest`. `docs/reviews/2026-10-02-magento-docker-acceptance.md` records the latest Magento Docker browser results, including the null-parameter regression and remaining read-only grid control issue; `docs/ui-component-editor-acceptance.md` records the earlier disposable Magento 2.4.8/2.4.9 checks. Read [Admin UI Component forms](Admin-UI-Component-Forms) for the current limits. The historical counts below describe earlier releases, not the candidate suite.

Only eight of those JavaScript tests are in the active-form suite, `admin-ui-form.test.cjs`; four other test files exercise retained legacy behavior and two cover the storefront. The reconciled `docs/reviews/2026-10-01-ui-component-editor-review.md` records 21 passing focused PHP tests / 58 assertions and eight passing active-form JavaScript tests, alongside separate probes that reproduce malformed category rows and unenclosed header corruption. The repair adds failing-before/passing-after regression checks plus disposable Mage-OS browser and output verification. The final Mage-OS PHP suite passes 788 tests / 2,067 assertions. The subsequent Docker run passes 788 unit tests, 14 official Magento integration tests, and four database tests per platform, plus 29 frontend tests and five framework JavaScript cases per Magento version. Nine frontend tests now cover the active form. Restricted-role route enforcement passes; permission-based grid control visibility remains incomplete.

## Local validation

From the module repository root:

```bash
composer validate --strict --no-check-publish
find . -path './.git' -prune -o -type f \( -name '*.php' -o -name '*.phtml' \) -print0 | xargs -0 -n1 php -l
php dev/tests/validate.php
php dev/tests/validate-wiki.php
node --test dev/tests/frontend/*.test.cjs
```

Run the unit suite with the PHPUnit installation from an existing Magento or Mage-OS checkout:

```bash
MAGENTO_ROOT=/path/to/magento /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
```

Run `MAGENTO_ROOT=/path/to/magento node --test dev/tests/magento-ui-form.test.cjs` for promotion-date conversion and directive initialization against the installed platform's actual date, abstract-field, and value-link components. This separate suite needs a framework checkout and covers three Admin locale formats plus preservation of null directive parameters.

The consolidated validation checks package and module identity, feed configuration, Magento XML, schema whitelist alignment, isolated runtime identifiers, storefront integration markers, and selected regression-sensitive behaviors. Wiki validation checks navigation, page baselines, and known legacy instructions.

## Disposable database regressions

The persistence tests use the actual Magento resource load/save path with session-local temporary tables and a synthetic encryption key. They do not bootstrap a store or read its database credentials. Start a disposable MariaDB container with database `shopping_feed_test`, an empty test-only root password, and port 3306 mapped to a random **127.0.0.1** port. Run:

```bash
SHOPPING_FEED_TEST_DB_PORT=<mapped-port> MAGENTO_ROOT=/path/to/magento \
  php /path/to/magento/vendor/bin/phpunit --bootstrap Test/Unit/bootstrap.php Test/Database
```

Stop and remove the disposable container afterward. The tests check raw ciphertext after an Admin-style masked save, repeated saves, passwords whose encrypted form exceeds 255 bytes, and same-day interrupted queue recovery. They complement the full Magento integration suite and do not replace an installed-store schema upgrade check.

## CI coverage

The GitHub Actions workflow runs:

* Composer metadata validation
* PHP syntax checks across supported PHP versions
* Consolidated module and wiki validation
* Hyva and Luma storefront auto-selection checks
* Magento Open Source compatibility checks
* Mage-OS 3.4.0 compatibility checks

The exact supported PHP constraint remains authoritative in `composer.json`. The current [Status and compatibility](Status-and-Compatibility) page translates that metadata for users.

The earlier Google/custom-feed acceptance profile on Mage-OS 3.5.0 passed 413 unit tests with 955 assertions, 10 application integration tests with 26 assertions, 14 frontend tests, and 33 generation checks. See [Release 1.1.0](Release-1-1-0) for the separate earlier Nebula, no-Nebula, and storefront runs. PHPUnit 9, 10, and 12 use the same data-provider coverage; compatibility fixtures explicitly configure optional arguments and date modification. The follow-up issue review passed 433 unit tests, 19 JavaScript tests, and 42 feed/stock checks and is recorded in the [issue acceptance report](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-28-github-issues.md). These local results do not add Mage-OS 3.5.0 to the CI matrix or replace recipient acceptance. Use the [CI run history](https://github.com/mage-os-lab/module-shopping-feed/actions/workflows/ci.yml) for the status of the exact release commit.

## Documentation changes

Update documentation in `docs/wiki`, not directly in the public wiki. Follow `docs/WIKI-MAINTENANCE.md` for source precedence, page conventions, drift checks, and the separate publication approval boundary.

When behavior changes, update the implementation, tests, relevant wiki page, and [release acceptance](Release-Acceptance) evidence in the same reviewable change. Do not carry historical instructions forward when current code disagrees.

## Review expectations

Provide:

1. The exact behavior or documentation claim changed
2. The current-code evidence for it
3. Focused validation output
4. Platform or runtime evidence when behavior depends on Magento
5. Any unverified external service behavior or remaining limitation

Passing local checks is not evidence that a wiki was published or that an external feed recipient accepted an output file.
