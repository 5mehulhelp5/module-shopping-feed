# Scheduling and queues

Schedules decide when work enters the queue. A separate worker consumes queued work and generates feed files.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

## Enable module cron processing

Go to **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed > General Info** and set **Cron Enabled** to **Yes** when Magento cron should manage the module.

The module defines its own `mageos_shopping_feed` cron group:

| Job | Built-in schedule | Purpose |
| --- | --- | --- |
| `mageos_shopping_feed_schedule` | Minute 10 of every hour | Adds feeds due in the current store-timezone hour to the queue |
| `mageos_shopping_feed_process` | Every minute | Processes one queue item |

Magento's normal cron runner must be installed and healthy. Saving a feed schedule does not replace the platform cron requirement.

## Configure a feed schedule

Open a feed, select **Run Schedule**, then add one or more rows:

* **Start At** selects the daily hour.
* **Batch Mode** splits one complete generation across multiple worker runs.
* **Batch Limit** sets the maximum number of product objects processed per batch.

Allow enough time between schedules for the earlier generation and any uploads to finish. The first schedule row's batch settings are also used by **Run Now**.

## Queue behavior

* Manual requests are selected before scheduled requests when both are waiting.
* The oldest unread item is processed first within that priority.
* A worker handles one queue item per invocation.
* A schedule does not enqueue the same feed again while queued or running work for that feed exists.
* Queue lookups remain independent across feeds. An already queued feed does not suppress another due feed, and a previously empty lookup does not hide newly queued work.
* Completed batch state is retained so the next worker continues from the prior offset.

**Recovery in 1.1:** a queue row left marked running is eligible on the next worker invocation, including the same day. The worker acquires the feed lock and reloads the row before starting. If another worker completed it, no duplicate generation starts. An interrupted run restarts from the beginning so partially appended output cannot duplicate rows. Completed batches still resume normally. Repeated failures remain visible as errors and will be retried; correct the underlying error in the logs.

File locks require workers to share the same lock filesystem. This is not a distributed lease across independent hosts.

## Standalone command scheduling

If **Cron Enabled** is **No**, arrange both commands in the hosting scheduler. The CLI commands run in detached mode and are allowed even when module cron processing is disabled:

```text
10 * * * * cd /path/to/magento && php bin/magento mage-os:shopping-feed:schedule
* * * * * cd /path/to/magento && php bin/magento mage-os:shopping-feed:generate
```

Use the correct PHP binary, Magento filesystem user, and absolute application path for the environment. Do not run both Magento cron and these standalone entries unless duplicate invocations are understood and monitored.

## Verify scheduling

1. Save a schedule a few minutes ahead in the store timezone.
2. Confirm the schedule runner creates a **Pending** item.
3. Confirm the process runner changes the feed to **Processing**, then **Completed**.
4. Check the final file timestamp and feed log.

If the feed stays pending, see [Logs and troubleshooting](Logs-and-Troubleshooting).
