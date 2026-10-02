# Migration and coexistence

`MageOS_ShoppingFeed` is intentionally a new module, not a renamed release of an installed Rocket Web package.

That boundary protects stores that paid for and currently depend on the original packages. Installing this module does not claim their Composer packages, register their Magento modules, read their configuration, or alter their tables.

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

There is no automatic data migration in the first release. The module does not copy:

- Feed records and their serialized configuration
- Schedules and upload destinations
- Category mappings and filters
- System configuration
- Generated feed files or logs

This is deliberate. An implicit migration could modify a paid installation or produce a second live feed without review.

## Safe evaluation on an existing store

1. Back up the database and `pub/media/feeds`.
2. Install the Mage-OS module in a staging environment.
3. Keep its schedules and uploads disabled while recreating one feed.
4. Compare the generated columns, product count, prices, availability, URLs, and identifiers with the active feed.
5. Test any FTP or SFTP destination with a distinct remote filename.
6. Enable scheduling only after the output is accepted.
7. Disable or remove the original package only through a separately reviewed client migration.

Both module identities can be installed for evaluation because their runtime resources are isolated. They still solve the same operational problem, so running both schedules or uploads without distinct destinations can create duplicate submissions.

## Future importer

A migration utility, if added, should be an explicit preview-and-confirm command. It must copy into the new tables, preserve the original data, report unsupported fields, and leave generation and uploads disabled until approved. No such importer is included today.

## Upgrading existing Mage-OS editor customizations

The unreleased UI Component editor at `9f07e46` is a separate change from Rocket Web data migration. It keeps the Mage-OS schema, routes, configuration keys, and prepare-save event, but replaces the default PHP form blocks and tabs. The module's own former observers/plugins have been converted; downstream customizations of those hooks require their own migration.

Use `ShoppingFeedFormModifierPool` for UI metadata/data modifiers and declarative parameter definitions or custom UI components for directive editors. Legacy `prepare_form_*` observers, tab-block plugins, and arbitrary PHP/PHTML parameter renderers do not customize the new default form. Stores without such customizations do not need a custom adapter. See [the developer contract](docs/ui-component-editor.md).

The standard-grid permission repair also adds an `AuthorizationInterface` constructor dependency to `Ui/Component/Listing/Column/FeedActions`, after `$urlBuilder`. Custom subclasses with an explicit parent constructor call must pass it. Custom mass actions should declare `config/aclResource`; controller ACL remains mandatory. See the [grid extension contract](docs/ui-component-editor.md#grid-permissions-and-extensions).

The original `9f07e46` failed category-generation and promotion-date checks. [Local repairs and verification](docs/reviews/2026-10-02-ui-component-editor-fixes.md) now cover those failures, but they are not deployed to `mageos-latest` or released. Evaluate the exact repaired revision on staging. No schema change is required, but saved configuration still needs a backup: code rollback cannot restore erased date values. There is no old/new editor configuration switch, and changing the Admin theme does not restore the old editor.
