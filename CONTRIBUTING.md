# Contributing

Changes should preserve the module's independent `MageOS_ShoppingFeed` identity and its ability to coexist with the original Rocket Web packages.

Before opening a pull request, run:

```bash
composer validate --strict --no-check-publish
php dev/tests/validate.php
find . -path './.git' -prune -o -type f \( -name '*.php' -o -name '*.phtml' \) -print0 | xargs -0 -n1 php -l
```

When a Magento or Mage-OS checkout is available, also run:

```bash
php dev/tests/validate-magento-xml.php /path/to/magento
```

Do not add an implicit migration from legacy tables or configuration. A migration feature must preview its work, preserve the source data, and require explicit confirmation before activating schedules or uploads.
