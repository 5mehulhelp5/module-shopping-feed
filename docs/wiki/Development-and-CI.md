# Development and CI

The repository validates module identity, configuration integrity, PHP behavior, and supported Magento-family platforms. Run focused checks before requesting review.

> Documentation baseline: release `v1.0.0` plus unreleased review fixes. Last reviewed: 2026-09-24.

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

The consolidated validation checks package and module identity, feed configuration, Magento XML, schema whitelist alignment, isolated runtime identifiers, storefront integration markers, and selected regression-sensitive behaviors. Wiki validation checks navigation, page baselines, and known legacy instructions.

## Disposable database regressions

The unreleased persistence tests use the actual Magento resource load/save path with session-local temporary tables and a synthetic encryption key. They do not bootstrap a store or read its database credentials. Start a disposable MariaDB container with database `shopping_feed_test`, an empty test-only root password, and port 3306 mapped to a random **127.0.0.1** port. Run:

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

The 1.0.0 local Mage-OS 3.5.0 profile passed 354 PHP tests with 721 assertions and five frontend tests. Its full-store and browser evidence is recorded in [Release 1.0.0](Release-1-0-0). This does not add Mage-OS 3.5.0 to the CI matrix or replace destination-specific acceptance.

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
