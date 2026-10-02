# Logs and troubleshooting

Start with the feed-specific log and the feed grid status. They distinguish queue problems, product skips, generation failures, and upload failures.

> Documentation baseline: release `v1.1.0`, with explicitly marked unreleased `9f07e46` editor notes. Last reviewed: 2026-10-01.

For unreleased `9f07e46`, consult [Admin UI Component forms](Admin-UI-Component-Forms) before treating a category-preview failure or blank promotion dates as store configuration errors. Both are reproduced candidate defects awaiting fixes.

## Log settings and location

Configure logging at **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed > Log Settings**.

* **Logging Level** can be Debug, Info, Notice, Warning, or Error.
* **Log rotate (Kb)** archives a feed log after it exceeds the configured size.

Feed logs are restricted to `var/log` and use a safe name based on the feed ID, normally:

```text
var/log/mageos_shopping_feed_<feed_id>.log
```

Use **View Log** from the Feeds Management grid, or inspect the file as the Magento filesystem owner.

## Symptom guide

| Symptom | First checks |
| --- | --- |
| New category mapping causes `Undefined array key "id"` in candidate `9f07e46` | Known missing-ID serialization defect; preserve the fixture and do not rely on a successful editor save as generation acceptance. |
| Promotion dates reopen blank in candidate `9f07e46` | Stop before saving again, which can erase stored dates. Preserve the pre-upgrade configuration and compare it during recovery. |
| Feed stays Pending | Confirm Magento cron, the `mageos_shopping_feed` cron group, and the process command are running. |
| Direct command says generation failed | Check whether another process holds the per-feed lock, then open the feed log. |
| Feed reaches Error | Read the first exception or error for that run, not only the final status line. |
| No final file appears | Confirm the configured directory and filename, filesystem permissions, disk space, and whether a `.tmp` generation is still active. |
| Product is absent | Test the SKU, then check enablement, catalog visibility, stock settings, filters, category rules, and complex-product configuration. |
| Row columns do not align | Review delimiter choice, mapped directives, source values containing delimiters or line breaks, and row counts across representative products. |
| Upload fails | Confirm mode, hostname, port, credentials, remote path, permissions, DNS, and firewall access from the Magento host. |
| `require is not defined` on a Hyva simple-product page | Confirm 1.0.0 or later is installed, the `hyva_` layout override is active, and stale theme overrides or full-page caches do not retain the Luma template. |
| Configurable link does not select a Hyva variant | Regenerate the feed and confirm its fragment includes numeric attribute IDs. Inspect selected options, price updates, and browser errors. |
| Available configurable variants appear out of stock | Confirm the parent is salable and 1.0.0 or later is installed. Check child stock, parent inheritance, and the selected website stock independently. |
| Local Inventory rows are missing | Confirm the website stock, source assignment, source-to-store mapping, salable quantity, and reservation state. |
| Google backorder/preorder is skipped | Map a valid future `availability_date` within one year. The log names the skipped row and required setting. |
| Google identifiers are blank | Map actual manufacturer identifiers. The Identifier Exists directive no longer infers absence from missing data. |
| Custom comma feed looks quoted or has unexpected splits | Use a CSV parser. Version 1.1 preserves embedded commas and doubles quotes; splitting on commas is not sufficient. |
| Standard-grid keyword search returns every feed | Run `setup:upgrade` and confirm the feed-name full-text index exists, then clear caches and retry. |
| Saved upload credentials fail to load | Replace unreadable credentials after checking schema upgrade status; previously truncated ciphertext cannot be recovered. |
| Google rejects data | Compare the exact generated value with the current Google specification and account diagnostics. |

## Locks and interrupted runs

Generation uses `flock` on `var/tmp/mageos_shopping_feed_<feed_id>.lock`. An empty lock file can remain after a successful run, but the operating-system lock is released. Do not treat the file's existence as proof of a stuck process.

Before intervening, confirm whether a worker is active and whether the file or log is still changing. If no process holds the lock, a new run can acquire it normally.

## Temporary files

A `.tmp` feed is an in-progress artifact. The module moves it to the final filename only after the full generation completes. Diagnose the failed run before removing artifacts, especially when another worker may still be active.

## Escalation evidence

Capture the minimum useful evidence before reporting a defect:

* Module version and exact commit
* Mage-OS or Magento Open Source version and PHP version
* Feed type, feed ID, store view, and relevant settings
* Exact command or Admin action
* First relevant error and surrounding log lines with secrets removed
* Test SKU and expected versus actual field values
* Whether the problem reproduces without upload destinations

For slow or incomplete large feeds, continue with [Performance and large catalogs](Performance-and-Large-Catalogs).
