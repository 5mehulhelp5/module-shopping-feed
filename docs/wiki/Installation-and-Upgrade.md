# Installation and upgrade

Install the module on staging first. Feed generation writes files and records queue, schedule, upload, and status data. A saved upload can also transfer completed files to an external system.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Before installation

1. Confirm the target Mage-OS or Magento Open Source and PHP versions are supported.
2. Record the exact module commit or package version.
3. Back up the database and relevant media and configuration files.
4. Confirm Magento cron is healthy.
5. If a Rocket Web feed package is installed, read [Migration and coexistence](Migration-and-Coexistence).
6. Plan to keep new schedules and uploads disabled until a generated file has been reviewed.

## Composer installation

Use this method only after confirming that `mage-os/module-shopping-feed` is available through a Composer repository configured in the Magento project.

```bash
composer require mage-os/module-shopping-feed
bin/magento module:enable MageOS_ShoppingFeed
bin/magento setup:upgrade
bin/magento cache:clean
```

In production mode, complete the normal deployment steps for the project, including dependency-injection compilation and static-content deployment where required.

## Source installation

Place or symlink the source at:

```text
app/code/MageOS/ShoppingFeed
```

Then run:

```bash
bin/magento module:enable MageOS_ShoppingFeed
bin/magento setup:upgrade
bin/magento cache:clean
```

## Verify the installation

```bash
bin/magento module:status MageOS_ShoppingFeed
bin/magento list mage-os:shopping-feed
```

Then verify:

* **Catalog > Mage-OS Shopping Feed > Feeds Management** opens for an authorized administrator.
* **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed** is available.
* The `mageos_shopping_feed_*` tables exist.
* The Admin and storefront load without new PHP or JavaScript errors.
* `bin/magento setup:di:compile` succeeds in the intended production configuration.

## Upgrade

Upgrade through the same installation path used for the module:

1. Record the current and target versions or commits.
2. Back up the database and generated feed configuration.
3. Update the Composer package or source checkout.
4. Run `bin/magento setup:upgrade`.
5. Clean the required caches and complete the project's deployment steps.
6. Regenerate a non-production feed and compare it with the previous accepted output.
7. Re-enable schedules and uploads only after the new output is accepted.

## Do not copy old installation instructions

Instructions for `rocketweb/module-google-shopping`, Magento Marketplace access keys, `rocketshoppingfeed:*` commands, or a `pub/media/feeds` output directory belong to the older Rocket Web packages. They do not install or operate `MageOS_ShoppingFeed`.
