# Migration and coexistence

`MageOS_ShoppingFeed` is intentionally a new module, not a renamed release of an installed Rocket Web package.

That boundary protects stores that paid for and currently depend on the original packages. Installing this module does not claim their Composer packages, register their Magento modules, read their configuration, or alter their tables.

For users moving from Rocket Shopping Feeds, the separately installed [Rocket Web migration companion](https://github.com/rocketweb/module-shopping-feed-migration-rocketweb) provides an explicit configuration importer. Version 1.0.0 is available on [Packagist](https://packagist.org/packages/rocketweb/module-shopping-feed-migration-rocketweb). Follow the [installation and migration guide](docs/wiki/Rocket-Web-Migration.md); installing or upgrading the main module does not run it.

## Identity map

| Surface | New identity |
| --- | --- |
| Composer package | `mage-os/module-shopping-feed` |
| Magento module | `MageOS_ShoppingFeed` |
| PHP namespace | `MageOS\ShoppingFeed` |
| Database tables | `mageos_shopping_feed_*` |
| Configuration section | `mageos_shopping_feed` |
| Admin route | `mageos_shopping_feed` |
| Cron group | `mageos_shopping_feed` |
| CLI prefix | `mage-os:shopping-feed` |
| Feed definition | `etc/mageos_shopping_feed.xml` |
| Default feed directory | `pub/media/mageos-shopping-feed` |

Event names, layout handles, UI component names, JavaScript aliases, ACL resources, log names, and generated feed defaults use the same new identity.

## What is not migrated

The main module does not automatically copy:

- Feed records and their serialized configuration
- Schedules and upload destinations
- Category mappings and filters
- System configuration
- Generated feed files or logs

This is deliberate. An implicit migration could modify a paid installation or produce a second live feed without review.

## Safe evaluation on an existing store

1. Back up the database and `pub/media/feeds`.
2. Install the Mage-OS module in a staging environment.
3. Keep its schedules and uploads disabled while recreating one feed manually or previewing an import with the companion.
4. Compare the generated columns, product count, prices, availability, URLs, and identifiers with the active feed.
5. Test any FTP or SFTP destination with a distinct remote filename.
6. Enable scheduling only after the output is accepted.
7. Disable or remove the original package only through a separately reviewed client migration.

Both module identities can be installed for evaluation because their runtime resources are isolated. They still solve the same operational problem, so running both schedules or uploads without distinct destinations can create duplicate submissions.

## Optional Rocket Web migration companion

The [companion extension](https://github.com/rocketweb/module-shopping-feed-migration-rocketweb) previews one legacy feed at a time and imports supported configuration into a disabled Mage-OS feed. It preserves the source records and holds schedules and encrypted upload configuration until separately reviewed activation. Imports require a current preview token and a reference to the operator's completed backup.

Keep the legacy modules installed and enabled through `setup:upgrade`; disabling a module that owns declarative schema can remove its tables. Pause individual feeds and their scheduled work for cutover. Source and destination must share one Magento installation and encryption key. The initial source baseline is Rocket Web base/Google Shopping/Promotions 2.3.4 and Local Inventory 2.3.2; validate other versions and customizations on staging.

Compare actual generated output before activation. Custom PHP, shared global settings, external schedulers, and recipient URLs need manual review. Automatic rollback removes only unchanged inactive imports and retains the encrypted receipt. Edited or activated feeds require a reviewed reversal. See the [migration guide](docs/wiki/Rocket-Web-Migration.md) and [companion instructions](https://github.com/rocketweb/module-shopping-feed-migration-rocketweb#installation).

## Upgrading existing Mage-OS editor customizations

The 1.2.0 UI Component editor, introduced at `9f07e46`, is a separate change from Rocket Web data migration. It keeps the Mage-OS schema, routes, configuration keys, and prepare-save event, but replaces the default PHP form blocks and tabs. The module's own former observers/plugins have been converted; downstream customizations of those hooks require their own migration.

Use `ShoppingFeedFormModifierPool` for UI metadata/data modifiers and declarative parameter definitions or custom UI components for directive editors. Legacy `prepare_form_*` observers, tab-block plugins, and arbitrary PHP/PHTML parameter renderers do not customize the new default form. Stores without such customizations do not need a custom adapter. See [the developer contract](docs/ui-component-editor.md).

The standard-grid permission repair also adds an `AuthorizationInterface` constructor dependency to `Ui/Component/Listing/Column/FeedActions`, after `$urlBuilder`. Custom subclasses with an explicit parent constructor call must pass it. Custom mass actions should declare `config/aclResource`; controller ACL remains mandatory. See the [grid extension contract](docs/ui-component-editor.md#grid-permissions-and-extensions).

Custom `Composite` and `Configurable` adapter subclasses with constructor overrides must also forward the new linked-product collection factory dependency. Existing website/store overrides for microdata and Google Ads settings now take effect; review them and clear applicable caches. See the [1.2.0 upgrade checklist](docs/wiki/Installation-and-Upgrade.md#upgrading-from-11-to-120).

The original `9f07e46` failed category-generation and promotion-date checks. The repairs, including two preview follow-ups, are now [deployed and verified on `mageos-latest`](docs/reviews/2026-10-02-mageos-latest-deployment-acceptance.md), and are included in release 1.2.0. Evaluate the exact release on staging. No schema change is required, but saved configuration still needs a backup: code rollback cannot restore erased date values. For Magento 2.4.7-p10, also review the [platform dependency guidance](docs/compatibility/magento-2.4.7-p10.md); its Composer blocker is an upstream Flysystem advisory. There is no old/new editor configuration switch, and changing the Admin theme does not restore the old editor.
