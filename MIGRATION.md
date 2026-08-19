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
