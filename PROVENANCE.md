# Source provenance

This module is a consolidated derivative of four Rocket Web repositories. The initial Mage-OS import used these exact revisions:

| Source repository | Imported revision | Consolidated capability |
| --- | --- | --- |
| [rocketweb/m2-shopping-feed](https://github.com/rocketweb/m2-shopping-feed) | `f6a9ec6` | Core feed models, generic feed configuration, Admin UI, CLI, cron, and tests |
| [rocketweb/m2-shopping-feed-google](https://github.com/rocketweb/m2-shopping-feed-google) | `11d3f63` | Google Shopping mappings, taxonomy, microdata, and remarketing |
| [rocketweb/m2-shopping-feed-google-inventory](https://github.com/rocketweb/m2-shopping-feed-google-inventory) | `050a90d` | Google Local Inventory and optional Multi-Source Inventory integration |
| [rocketweb/m2-shopping-feed-google-promotions](https://github.com/rocketweb/m2-shopping-feed-google-promotions) | `5b30919` | Google Promotions configuration, generation, and upload integration |

The four package namespaces and Magento module identities were flattened into `MageOS\ShoppingFeed` and `MageOS_ShoppingFeed`. Their dependency injection, event, system configuration, layout, RequireJS, feed-definition, and database declarations were merged into a single module.

The consolidation also resolves source-level integration defects that could not be represented safely in a single package. These include exact-version cross-package constraints, a duplicate Google Shopping default column, a missing Local Inventory encoding value, ignored gzip settings, stale Promotions values, incorrect reuse of upload credentials, and the retired DoubleClick remarketing transport.

The imported unit suite was updated for the current Magento testing framework and PHP runtime, then expanded with regression coverage for the consolidated module. It runs through the repository bootstrap in `Test/Unit/bootstrap.php`.

Historical Rocket Web data patches were intentionally not imported. The new module creates fresh declarative-schema tables, so upgrades for serialized legacy rows are unnecessary. The legacy log migration also moves and deletes shared `pub/media/feeds/log_*.log` files, which would violate the coexistence boundary on a store running a paid package.

Original Rocket Web notices remain in the imported source. The package metadata, source notices, and bundled license in this repository are aligned on OSL-3.0. See [LICENSING.md](LICENSING.md).
