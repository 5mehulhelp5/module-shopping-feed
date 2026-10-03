# Development and CI

The repository validates module identity, configuration integrity, PHP behavior, and supported Magento-family platforms. Run focused checks before requesting review.

> Documentation baseline: release `v1.1.0`, with explicitly marked unreleased `9f07e46` editor notes. Last reviewed: 2026-10-02.

## Unreleased editor implementation and evidence

The default forms in `9f07e46` live in `view/adminhtml/ui_component/mageos_shopping_feed_form.xml` and `mageos_shopping_feed_test_form.xml`, with providers under `Ui/DataProvider/Feed` and controls under `view/adminhtml/web/js/form`. Use `ShoppingFeedFormModifierPool` for custom metadata/data and the repository's `docs/ui-component-editor.md` for the contract. Previous form files remain for transition; their passing tests do not prove the replacement form. The legacy menu and category tree still have live callers, and renderer class names remain active configuration identifiers. Audit those dependencies and validator/test references before removing files.

The current candidate passes **801 PHP unit tests** on Mage-OS 3.5.0 and Magento Open Source 2.4.8/2.4.9, **29 frontend tests**, and schema validation of **26 XML files**. The repository's `docs/reviews/2026-10-02-mageos-latest-deployment-acceptance.md` records the current target deployment and two additional preview regressions with failing-before/passing-after checks. The final assertions are 1,866 on Magento 2.4.8 and 2,126 on Magento 2.4.9 and Mage-OS. Read [Admin UI Component forms](Admin-UI-Component-Forms) for exact scope and remaining limits.

Nine frontend tests cover the active form in `admin-ui-form.test.cjs`; the other tests cover retained legacy behavior and the storefront. Five cases using each platform's actual UI framework cover localized dates and null initialization/value links. The prior Docker run passed 16 official Magento integration tests and four database tests per platform, plus production compilation and browser checks for six role profiles. The complete unit and 16-test integration suites were repeated for the committed preview fixes in `c07de81`, together with all eight CLI previews per platform. The six-role matrix remains the earlier result. The repository's `docs/reviews/2026-10-02-preview-followup-acceptance.md` records passing SKU preview, invalid-ID recovery, and valid-ID retry checks on both versions, including the browser automation limits. Historical counts and failures remain in `docs/reviews/2026-10-01-ui-component-editor-review.md`, `docs/reviews/2026-10-02-ui-component-editor-fixes.md`, `docs/reviews/2026-10-02-magento-docker-acceptance.md`, and `docs/reviews/2026-10-02-grid-permission-acceptance.md`.

The operational follow-up in `docs/reviews/2026-10-02-docker-operations-acceptance.md` adds 10 scheduler checks, nine batch-recovery checks, 17 checks per FTP/SFTP transport, and nine simple-product MSI scenarios per Magento version. Original database rows and schema were preserved, allowing only consumed auto-increment counters. These runtime checks supplement the form acceptance; they do not establish unattended cron or external recipient acceptance.

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
