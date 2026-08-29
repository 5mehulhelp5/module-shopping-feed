# Feed types and lifecycle

The feed type provides a starting column map and default behavior. All feeds then move through the same save, test, queue, generation, upload, and review lifecycle.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Feed types

| Feed | Use it for |
| --- | --- |
| Generic | Custom delimited feeds for systems without a bundled template |
| Google Shopping | Google product data, variants, taxonomy, shipping, and product-linked promotion IDs |
| Google Local Inventory | Store-level availability, quantity, and price, optionally expanded by MSI source |
| Google Promotions | A companion file configured and generated from a Google Shopping feed |

See the individual feed guides before relying on their default columns.

## Grid actions

The Feeds Management grid exposes these per-feed actions:

* **Configure** opens the feed tabs.
* **Run Now** adds the feed to the generation queue.
* **Test Feed** renders one SKU or product ID without replacing the normal production file.
* **View Log** opens the per-feed log.

The grid also supports mass enable, disable, clone, and delete operations when the Admin role has the required ACL permissions.

## Statuses

| Status | Meaning |
| --- | --- |
| Disabled | The feed is not active for normal operation. |
| Scheduled | The feed has saved schedule configuration. |
| Pending | Work is waiting in the generation queue. |
| Processing | A worker is generating the feed. |
| Completed | The last generation completed. Review the file and counts. |
| Error | Processing failed. Open the feed log before retrying. |

## Recommended lifecycle

1. Create the feed from the closest template.
2. Save its general settings and column map.
3. Test representative products.
4. Generate the complete file without an upload destination.
5. Compare product counts and sampled data with Magento.
6. Validate the file with the intended recipient.
7. Add one non-serving upload destination and test it.
8. Add schedules only after the output and transfer are accepted.
9. Record the accepted commit, configuration, file checksum, and external result.

## Clone carefully

Cloning is useful when two feeds share most settings, but the copy should have its own name, store view, file, schedules, and destinations. Review all of those before enabling it.
