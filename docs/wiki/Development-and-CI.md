# Development and CI

The repository validates module identity, configuration integrity, PHP behavior, and supported Magento-family platforms. Run focused checks before requesting review.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Local validation

From the module repository root:

```bash
composer validate --strict --no-check-publish
find . -path './.git' -prune -o -type f \( -name '*.php' -o -name '*.phtml' \) -print0 | xargs -0 -n1 php -l
php dev/tests/validate.php
php dev/tests/validate-wiki.php
```

Run the unit suite after installing development dependencies:

```bash
vendor/bin/phpunit -c phpunit.xml.dist
```

The consolidated validation checks package and module identity, feed configuration, Magento XML, schema whitelist alignment, isolated runtime identifiers, storefront integration markers, and selected regression-sensitive behaviors. Wiki validation checks navigation, page baselines, and known legacy instructions.

## CI coverage

The GitHub Actions workflow runs:

* Composer metadata validation
* PHP syntax checks across supported PHP versions
* Consolidated module and wiki validation
* Magento Open Source compatibility checks
* Mage-OS 3.4.0 compatibility checks

The exact supported PHP constraint remains authoritative in `composer.json`. The current [Status and compatibility](Status-and-Compatibility) page translates that metadata for users.

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
