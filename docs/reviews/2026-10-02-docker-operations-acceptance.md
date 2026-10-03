# Docker scheduling, recovery, delivery, and MSI acceptance

Tested October 2, 2026, against commit `5290611acb1029c80a090a690c46e2ddde212172` on `feat/ui-component-editor`. Its runtime is unchanged from preview-fix commit `c07de81`. This extends the [Docker form acceptance](2026-10-02-magento-docker-acceptance.md) and [committed preview verification](2026-10-02-preview-followup-acceptance.md).

Both retained Magento Open Source installations, 2.4.8 and 2.4.9, run in production mode with PHP 8.4.26. All 408 runtime/package files match the candidate. This run found no application defects and changed no module code. The separate `mageos-latest` installation and its disabled Nebula configuration were untouched.

## Results

| Area | Per-platform result |
| --- | --- |
| Scheduling and queue eligibility | 10 checks passed |
| Interrupted batch and concurrent worker recovery | 9 checks passed |
| FTP delivery and failure handling | 17 checks passed |
| SFTP delivery and failure handling | 17 checks passed |
| MSI source and availability behavior | Nine scenarios passed |
| Original module data after cleanup | All seven table hashes match |
| Entire application database after cleanup | All row data and schema match, allowing consumed auto-increment counters and dump completion timestamps |

### Scheduling

The real scheduler and schedule command ran against temporary database fixtures inside a rollback transaction. The store timezone was `America/Indiana/Indianapolis`; the tests ran at hour 23 and included a next-hour schedule at hour 0.

Two due feeds each received exactly one queue entry with their saved batch settings. A future schedule and a disabled feed did not queue. Repeating the scheduler did not create duplicates. Editing an already processed schedule to the current hour made it eligible again, exactly once. Disabling global cron blocked an otherwise eligible feed. The explicit schedule CLI command, exercised through Symfony's command tester, still queued it because detached CLI execution intentionally bypasses the global cron switch.

This verifies scheduler/command behavior. It does not establish unattended Magento cron installation or two complete hourly cron cycles.

### Batch interruption and overlap

A dedicated Generic feed on each platform generated an accepted two-product baseline. Batch size one advanced the offset while preserving a sentinel representing the last accepted output. A separate PHP worker was terminated after writing a product in a later chunk. Its queue row remained marked running, and the accepted output survived.

The normal `bin/magento mage-os:shopping-feed:generate <fixture-id>` command restarted from the beginning. A competing invocation failed while another identified test process held the feed lock, without changing queue state or accepted output. Subsequent chunks completed, removed the queue entry, and produced exactly two unique product IDs. The recovered output was byte-identical to the baseline.

Only the identified acceptance worker PIDs were terminated. This proves the two-product recovery path and shared-filesystem locking, not large-catalog throughput or locks across independent application hosts.

### FTP, SFTP, and gzip

A temporary server ran only on the existing private Docker network, with no published host ports. Each platform had two generated accounts and separate directories. The module's real upload-processing method loaded saved destination rows and sent an accepted feed through the installed Magento FTP/SFTP clients. Plain and gzip payloads were independently read from the receiving server and compared with the local SHA-256; gzip payloads were decompressed before comparison.

The checks verified encrypted password reload, unchanged ciphertext after masked saves, separate credentials and account directories, repeated successful delivery to the expected filename, missing-directory rejection, invalid-credential rejection, refused-connection handling, no password in caught error messages, no fallback write in the login directory, and compressed temporary-file cleanup after both success and failure. Failed attempts preserved the previously delivered files. The accepted local file also remained unchanged.

There were 17 assertions per transport per platform. The upload method was invoked on a real generator with accepted output; it was not an Admin upload-form retest. These private destinations do not establish acceptance by an external provider. Transfer image dependencies and build evidence are retained with the run.

### MSI

Nine separate PHP workers used transactionally isolated stock/source fixtures around the existing simple product `ui-product-1`:

* Two linked sources mapped to `STORE-A` and `STORE-B`, with quantities 7 and 11; an unlinked source with quantity 99 was excluded.
* A reservation of -2 produced quantities 5 and 9 under the module's configured reservation behavior.
* A compensating +2 reservation restored quantities 7 and 11.
* A zero-quantity, unavailable source remained out of stock.
* Disabling one source preserved the other source's row.
* Disabling every linked source produced no unscoped fallback row.
* A disabled source item with positive physical quantity remained out of stock.
* Online backorders did not make the empty physical source available.
* Disabled stock management did not make the empty physical source available.

SKU/store-code pairs were unique. Every worker restored the website's original stock mapping. The temporary stock used a session-local copy of the existing stock index table. This verifies the listed simple-product source behavior, not asynchronous stock indexing, real order/reservation lifecycle, configurable/grouped/bundle modes, or an exhaustive MSI matrix.

## Preservation and cleanup

Full database backups were taken before testing. Scheduler, upload, and MSI database mutations rolled back after each probe. The one persistent batch fixture, feed 23 on each installation, was removed only after matching its recorded identity and confirming every original module row was unchanged. Its data snapshot, accepted/recovered files, log, and lock file were retained in the private evidence directory.

Both applications finish with their original 16 disabled feeds, 1,073 configuration rows, 16 process rows, and no schedules, uploads, queue entries, or shipping-cache rows. Whole-database SQL comparisons match after omitting only `CREATE TABLE` auto-increment options and mysqldump completion timestamps. Test inserts consume sequence numbers even after rollback; existing rows and all other schema are compared without normalization.

The transfer container was stopped and removed. Its generated credential files were removed. The two requested Magento installations remain running. No background cron or queue worker was installed, no external recipient was contacted, and no repository push, release, or wiki publication occurred.

Evidence and the inspected probe scripts are under `/private/tmp/shopping-feed-magento-docker-20261002/`, with results under `{248,249}/evidence/operations/`. These include protected before/after database dumps, scheduler assertions, batch state transitions, transfer payloads, MSI rows, cleanup manifests, and final runtime/data verification. Preserve this temporary directory if long-term reproduction is needed.

## Remaining limits

The recorded standard-Admin workflows, these operational checks, and the earlier unit/integration suites remain distinct evidence. Native Nebula bridge rendering, downstream editor customizations, complex-product combinations, asynchronous MSI indexing/order flows, unattended hourly cron cycles, external recipient ingestion, representative catalog volume, Mage-OS 3.4 installation, and production release approval remain separate acceptance items. This report does not turn the reusable production test plan into a completed release sign-off.
