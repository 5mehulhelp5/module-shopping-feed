# Contributing

Changes should preserve the module's independent `MageOS_ShoppingFeed` identity and its ability to coexist with the original Rocket Web packages.

Before opening a pull request, run:

```bash
composer validate --strict --no-check-publish
php dev/tests/validate.php
php dev/tests/validate-wiki.php
node --test dev/tests/frontend/*.test.cjs dev/tests/ci/*.test.cjs
find . -path './.git' -prune -o -path './vendor' -prune -o -type f \( -name '*.php' -o -name '*.phtml' \) -print0 | xargs -0 -n1 php -l
```

Install the pinned development tools once, using the newest PHP the module supports, then run the coding standard and static analysis:

```bash
composer update
vendor/bin/phpcs
vendor/bin/phpstan analyse --memory-limit=-1
php dev/tests/check-phpstan-baseline.php --git-ref=origin/main phpstan-baseline.neon
```

PHPCS warnings fail the run. Keep any exception narrow: an inline `// phpcs:ignore Sniff.Code -- reason` on the line that needs it, never a file or directory exclusion without discussion. PHPStan runs at level 8 against `phpstan-baseline.neon`, the reviewed debt from earlier releases. New and changed code must not add baseline entries or raise their counts. When you fix baselined debt, PHPStan reports the pattern as unmatched; remove that entry by hand. Never regenerate the whole baseline.

When a Magento or Mage-OS checkout is available, also run:

```bash
php dev/tests/validate-magento-xml.php /path/to/magento
MAGENTO_ROOT=/path/to/magento php /path/to/magento/vendor/bin/phpunit -c phpunit.xml.dist
MAGENTO_ROOT=/path/to/magento node --test dev/tests/magento-ui-form.test.cjs
```

Make new user-visible text translatable: `__()` with `%1` placeholders in PHP, `$t()` from `mage/translate` in JavaScript, `i18n` bindings in Knockout templates, and `translate` attributes in XML. Add the phrases to `i18n/en_US.csv`. Storefront scripts go through `$secureRenderer->renderTag()` on Luma or `$hyvaCsp->registerInlineScript()` on Hyvä so they work with a strict Content Security Policy.

Do not add an implicit migration from legacy tables or configuration. A migration feature must preview its work, preserve the source data, and require explicit confirmation before activating schedules or uploads.

Admin editor changes must target the active UI Component forms and providers, not the retained legacy tab templates. Read [the implementation guide](docs/ui-component-editor.md), [current browser acceptance](docs/reviews/2026-10-01-ui-component-chrome-acceptance.md), and [release acceptance plan](ACCEPTANCE-TEST-PLAN.md). Passing tests for old templates do not establish new-form coverage. Verify saved values through reload and generation, including category IDs and all promotion dates.

Update the relevant wiki source, README/changelog, and acceptance record when behavior or release status changes. Follow [wiki maintenance](docs/WIKI-MAINTENANCE.md); the public wiki is published separately. Preserve historical results and link them to newer evidence rather than turning them into an unsupported current acceptance claim.

Report security problems privately as described in [SECURITY.md](SECURITY.md).
