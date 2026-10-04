# Rocket Web migration companion

> Documentation baseline: core 1.2.1 and companion 1.0.0. Last reviewed: 2026-10-04.

`rocketweb/module-shopping-feed-migration-rocketweb` is an optional package for stores moving existing Rocket Web feed configuration to Mage-OS Shopping Feed. Stores with no legacy feeds do not need it. See its [repository and full instructions](https://github.com/rocketweb/module-shopping-feed-migration-rocketweb).

The 1.2.1 feed management screen detects legacy module registration or prefixed legacy tables without loading legacy PHP classes. It links to installation instructions, or to the installed migration screen for users with its dedicated ACL. The notice can be dismissed. No Composer operation or data import runs from that notice.

## Installation

Use staging and the store's normal deployment process. Back up the database, code, Composer lock file, generated output, configuration, and matching encryption key. Keep legacy modules installed and enabled through `setup:upgrade` to protect their declarative-schema tables. Pause individual legacy feeds and scheduled generation for cutover.

Install the companion from its published GitHub tags by adding its public source repository to the store's root Composer configuration, then select the coordinated releases:

```sh
composer config repositories.shopping-feed-migration-rocketweb vcs \
  https://github.com/rocketweb/module-shopping-feed-migration-rocketweb.git
composer require 'mage-os/module-shopping-feed:^1.2.1' \
  'rocketweb/module-shopping-feed-migration-rocketweb:^1.0' --no-update
composer update mage-os/module-shopping-feed \
  rocketweb/module-shopping-feed-migration-rocketweb --with-dependencies
bin/magento module:enable RocketWeb_ShoppingFeedMigration
bin/magento setup:upgrade
```

Complete DI/static/cache deployment as required by the store. Grant **Migrate Rocket Web feeds** to the operator, then open **Mage-OS Shopping Feed > Import Rocket Web Feeds**. Composer installation does not migrate data.

## Review, import, and activation

The initial supported source baseline is base/Google Shopping/Promotions 2.3.4 and Local Inventory 2.3.2. Source and destination must share one installation and encryption key. Retained tables alone cannot recover inherited defaults without the original module code and definitions.

Preview each feed, review diagnostics, and explicitly apply the import with its current token and backup reference. The replacement stays disabled, with schedules and uploads held. Generate test output and compare row counts, prices, stock, variants, identifiers, descriptions, category maps, promotions, encoding, and actual recipient requirements.

Before activation, disable the original feed, drain its queued work, and stop external commands that can restart it. Review a fresh activation preview and apply it explicitly. Shared global settings, custom PHP, recipient accounts, and public fetch URLs need manual review. Imports use a separate destination subdirectory; recipient URLs do not change automatically.

Encrypted receipts preserve held configuration. Stale/duplicate requests are rejected. Automatic rollback removes only unchanged inactive imports and retains the receipt; edited or activated feeds require a reviewed cutover reversal. Keep the matching key and receipt backup through the rollback period.

The companion's [verification record](https://github.com/rocketweb/module-shopping-feed-migration-rocketweb/blob/main/docs/VERIFICATION.md) distinguishes synthetic acceptance from an existing-store migration and recipient cutover. Opt-in legacy compatibility patches are documented separately and are never installed automatically.
