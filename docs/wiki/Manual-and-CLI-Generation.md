# Manual and CLI generation

Use manual generation while building or diagnosing a feed. The Admin queue action and the direct CLI command have different execution behavior.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Admin actions

Go to **Catalog > Mage-OS Shopping Feed > Feeds Management**.

* **Run Now** adds an unread item to the generation queue and changes the feed status to **Pending**. A queue worker must process it.
* **Test Feed** renders one product for inspection. It does not replace the normal feed file.
* **View Log** opens the feed-specific log.

If the feed already has unread queued work, **Run Now** does not add a duplicate queue item.

## CLI command

Run commands from the Magento application root as the filesystem owner.

Process one unread queue item:

```bash
bin/magento mage-os:shopping-feed:generate
```

Generate one feed directly by numeric feed ID:

```bash
bin/magento mage-os:shopping-feed:generate <feed_id>
```

Render one SKU in test mode:

```bash
bin/magento mage-os:shopping-feed:generate <feed_id> <sku>
```

Add `-v` when console log detail is useful. The command exits unsuccessfully when generation fails or when another process holds the feed lock.

## Concurrency and file completion

The worker obtains an operating-system lock for each feed at:

```text
var/tmp/mageos_shopping_feed_<feed_id>.lock
```

The lock prevents two workers from generating the same feed concurrently. The presence of the file alone does not prove that a lock is active, so do not delete it solely because it exists.

Normal generation writes to a `.tmp` file first. The module replaces the final feed only after generation completes, which prevents consumers from reading a partially written final file.

## Verify the result

Confirm all of the following:

1. The command returns success and prints `Done!`.
2. The feed grid reaches **Completed**.
3. The expected final file exists under `pub/media/mageos-shopping-feed`.
4. Header and row column counts match.
5. The feed log reports expected processed, added, and skipped counts.

For queued automation, continue with [Scheduling and queues](Scheduling-and-Queues).
