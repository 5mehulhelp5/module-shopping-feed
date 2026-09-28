# Migration and coexistence

`MageOS_ShoppingFeed` is a new module. It is not a renamed release of an installed Rocket Web package, and it does not copy Rocket Web data.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

## Isolated identities

| Surface | Mage-OS identity |
| --- | --- |
| Composer package | `mage-os/module-shopping-feed` |
| Magento module | `MageOS_ShoppingFeed` |
| PHP namespace | `MageOS\ShoppingFeed` |
| Database tables | `mageos_shopping_feed_*` |
| Configuration section | `mageos_shopping_feed` |
| Admin route | `mageos_shopping_feed` |
| Cron group | `mageos_shopping_feed` |
| CLI prefix | `mage-os:shopping-feed` |
| Default output directory | `pub/media/mageos-shopping-feed` |

Events, layout handles, UI component names, JavaScript aliases, ACL resources, cache identifiers, logs, locks, and generated filenames follow the same new identity.

## Data that is not migrated

The module does not automatically copy:

* Feed records or serialized feed configuration
* Schedules
* Upload destinations or credentials
* Category mappings
* Filters and transformations
* System configuration
* Generated files
* Logs

There is no bundled importer. Do not expect `setup:upgrade` to create Mage-OS feeds from an older package.

## Safe evaluation

1. Back up the database and both modules' output directories.
2. Install `MageOS_ShoppingFeed` on staging.
3. Keep its schedules and uploads disabled.
4. Recreate one feed manually under the new module.
5. Use a distinct local and remote filename.
6. Compare columns, row counts, prices, availability, URLs, identifiers, and skipped products.
7. Test one upload against a non-serving destination.
8. Plan any production cutover as a separate reviewed change.

Both module identities can be installed for evaluation because their runtime resources are isolated. They still solve the same operational problem. If both modules generate or upload to the same destination, duplicate or conflicting submissions are possible.

## Cutover boundary

Installing or validating the Mage-OS module does not authorize disabling or uninstalling the older package. A production cutover should identify:

* The feed and schedules that will stop
* The new files and schedules that will start
* Remote destination and filename changes
* A rollback procedure
* The person approving the change
* The Merchant Center or marketplace evidence that confirms acceptance
