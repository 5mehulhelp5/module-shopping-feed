# Commands, paths, and settings

This is the compact operational reference for the current Mage-OS module identity.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Identity

| Item | Current value |
| --- | --- |
| Composer package | `mage-os/module-shopping-feed` |
| Magento module | `MageOS_ShoppingFeed` |
| PHP namespace | `MageOS\ShoppingFeed` |
| License | `OSL-3.0` |

## Admin paths

| Purpose | Path |
| --- | --- |
| Manage feeds | **Catalog > Mage-OS Shopping Feed > Feeds Management** |
| Global settings | **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed** |

## Commands

| Purpose | Command |
| --- | --- |
| Process one unread queue item | `bin/magento mage-os:shopping-feed:generate` |
| Generate one feed directly | `bin/magento mage-os:shopping-feed:generate <feed_id>` |
| Test one SKU | `bin/magento mage-os:shopping-feed:generate <feed_id> <sku>` |
| Add due schedules to the queue | `bin/magento mage-os:shopping-feed:schedule` |

## Runtime paths

| Purpose | Path or pattern |
| --- | --- |
| Default feed directory | `pub/media/mageos-shopping-feed` |
| Default feed filename | `mageos_shopping_feed_%s.txt` |
| Default Promotions filename | `mageos_shopping_feed_promotion_%s.txt` |
| Feed log | `var/log/mageos_shopping_feed_<feed_id>.log` |
| Per-feed lock | `var/tmp/mageos_shopping_feed_<feed_id>.lock` |
| In-progress feed | Configured final path with `.tmp` appended |

Feed output is restricted to `pub/media/mageos-shopping-feed` and safe subdirectories. Supported configured file extensions are `txt`, `csv`, `tsv`, and `xml`.

## Global settings

| Group | Setting | Purpose |
| --- | --- | --- |
| General Info | Cron Enabled | Allows the module's Magento cron jobs to schedule and process feeds |
| Log Settings | Logging Level | Minimum Debug, Info, Notice, Warning, or Error level written |
| Log Settings | Log rotate (Kb) | Archives a log after the configured size |
| Google | Enable Automatic Updates (Microdata) | Adds supported schema.org product data to selected Google Shopping product pages |
| Google | Enable Google Ads Dynamic Remarketing Events | Calls `gtag` with a `view_item` event after a complete configurable selection |
| Google | Google Ads Destination ID | Optional `send_to` value such as `AW-123456789` |

## Built-in cron schedule

| Job | Schedule |
| --- | --- |
| Schedule due feeds | `10 * * * *` |
| Process the queue | `* * * * *` |

See [Manual and CLI generation](Manual-and-CLI-Generation), [Scheduling and queues](Scheduling-and-Queues), and [Logs and troubleshooting](Logs-and-Troubleshooting) for operating procedures.
