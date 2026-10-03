# Performance and large catalogs

Treat feed performance as a measured workload. Product count alone does not explain cost because field directives and complex-product expansion can dominate generation time.

> Documentation baseline: release `v1.1.0`, with unreleased editor labels. Last reviewed: 2026-10-03.

## Start with a baseline

Use a representative store and feed configuration, then record:

* Product objects considered, added, and skipped
* Generated row count and file size
* Wall-clock duration
* Peak memory and PHP memory limit
* Batch size and number of worker runs
* Upload duration, if applicable

Change one setting at a time and compare the same product fixture or catalog snapshot.

## Higher-cost features

These commonly add work per product or expand the number of output rows:

* Shipping rate calculation, especially across many countries or services
* Find-and-replace rules across many columns
* Configurable, grouped, and bundle context inheritance
* Many variant, image, review, or category-derived columns
* Local Inventory expansion across multiple MSI sources
* Large numbers of configured fields and filters

Disable unused work rather than increasing resource limits first.

## Batch mode

Enable **Batch Mode** under **Run Schedule** in 1.1, or **Schedule** in the [UI Component candidate](Admin-UI-Component-Forms), and set **Batch Limit** to a product-object count that completes comfortably inside the environment's time and memory limits.

If no positive limit is stored, the batch object defaults to 1000. Generation can also switch itself into batch mode when it approaches available execution time or memory, preserving the next offset in the schedule or queue.

Batch mode creates the final file only after the last batch completes. Monitor the queue and temporary file across worker runs.

## Tuning sequence

1. Remove columns and transformations the destination does not use.
2. Test expensive features independently.
3. Enable batch mode with a conservative limit.
4. Run enough batches to observe stable duration and memory.
5. Increase the limit gradually while preserving operational headroom.
6. Validate the complete final file and upload after tuning.

The generator logs progress periodically, with a default interval of 30 product objects. Use Info or Notice for routine operation and Debug for bounded diagnosis. Log rotation controls file size; reducing logs should not substitute for identifying an expensive mapping.

## Acceptance

A large-catalog configuration is ready only when repeated complete runs finish within the operating window, produce stable product and row counts, leave no partial final file, and complete every required upload.

The unreleased candidate passed 5,000-product generation for all eight presets on Magento Open Source 2.4.8 and 2.4.9, with unique IDs and complete row shapes. The preset runs used approximately 169 to 197 MiB peak PHP memory under a 2 GiB limit. Generic output was stable on repeat and through 500-item batches. These synthetic runs overlapped other local work and do not establish production capacity or a platform speed comparison. The repository's `docs/reviews/2026-10-03-local-acceptance.md` records each result and its limits.
