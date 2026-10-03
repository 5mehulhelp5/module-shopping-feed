# Extended local acceptance

**Local acceptance passed:** runtime candidate `133af71` is verified on Magento Open Source 2.4.8, Magento Open Source 2.4.9, and `mageos-latest`. Both real hourly cron cycles pass, fixtures are removed, and original data and output files are preserved.

The run continues the UI Component form acceptance on Magento Open Source 2.4.8, Magento Open Source 2.4.9, and the shared `mageos-latest` development store. It also exercises product selection, native MSI, prices, operational recovery, volume, frontend scope, and rollback. It is local acceptance evidence, not a release approval or external recipient acceptance.

## Candidate and environments

| Profile | Runtime | Admin and application mode |
| --- | --- | --- |
| Docker 2.4.8 | Magento Open Source 2.4.8, PHP 8.4.26, MySQL 8.4.11, OpenSearch 2.19.6 | Magento/backend, production mode, no Nebula |
| Docker 2.4.9 | Magento Open Source 2.4.9, PHP 8.4.26, MySQL 8.4.11, OpenSearch 3.8.0 | Magento/backend, production mode, no Nebula |
| `mageos-latest` | Mage-OS 3.5.0, PHP 8.4.24 | Magento/backend, developer mode, all 16 installed Nebula modules disabled |

The Docker applications use loopback ports 8128 and 8129. Separate rollback clones used ports 8138 and 8139 and their own application databases, configuration, media, caches, and generated code. Integration tests use a separate test database. No serving upload destination was configured.

All 408 runtime/package files match `133af71` in both Docker stores and `mageos-latest`. The candidate remains local and unreleased. No push, remote CI run, merge, tag, or package publication occurred in this acceptance continuation.

## Defects closed

The original review's category identity, promotion dates, malformed categories, column headers, preview inputs, and hidden microdata defaults were corrected and exercised in the earlier reports below. Retaining legacy files with live callers and supporting custom configuration keys remain intentional decisions, not unresolved application defects.

This extended run reproduced and corrected five further defects:

| Commit | Correction | Evidence |
| --- | --- | --- |
| `6c4bc72` | Feed stock settings take precedence over storefront out-of-stock hiding | Failing configurable regression, native grouped SQL inspection, stock integration tests, and all complex-product modes |
| `6c4bc72` | Configurable Local Inventory parents use their children's linked sources | Failing unit case and native custom-stock parent/child output |
| `6c4bc72` | Parent local availability requires an enabled source item at the same source | Failing positive-quantity/disabled-status case and native source-item disable/restore checks |
| `133af71` | Existing blank feed currency resolves to its effective generation currency instead of a differing store default | Two failing unit cases and a failing native provider/save integration case; new and explicit currency choices remain stable |
| `49897b7` | Microdata and Google Ads settings honor website/store overrides | Both enable and disable integration cases failed before repair; native rendered metadata and store switching pass afterward |

See the [stock correction report](2026-10-02-stock-acceptance-fixes.md), [frontend scope report](2026-10-03-frontend-scope-fix.md), and [currency correction](2026-10-03-existing-feed-currency-fix.md). Custom `Composite` or `Configurable` adapter subclasses with constructor overrides must forward the added linked-product collection factory. Existing frontend scope overrides take effect after upgrade; review them and clear applicable caches.

## Automated and functional evidence

| Check | Result |
| --- | --- |
| PHP unit suite | 809 tests on each of the three frameworks; 1,879 assertions on Magento 2.4.8 and 2,158 on Magento 2.4.9/Mage-OS |
| Official Magento integration suite | 21 tests and 53 assertions per Docker platform |
| Final production compilation | Pass on both Docker platforms; compiled separately from the active generated-code directories |
| Mage-OS dependency compilation | Pass after all three scoped deployments |
| JavaScript and XML coverage | The unchanged JavaScript and XML retain 29 frontend tests, five actual-framework form cases per platform, and 26 XML schema checks |
| Coding standards | Stock-candidate full scan: zero errors and 3,588 warnings across 404 files. Frontend changed-file scan: zero errors and 32 warnings. Currency changed-file scan: zero errors; seven existing provider warnings remain after removing the new test's line-length warning. Repository configuration permits warnings |
| Semantic catalog | 26 scenarios per Magento version |
| Native configurable MSI | 15 scenarios per Magento version |
| Pricing | Three scenarios per Magento version |
| Configurable presets | All eight presets produce expected child/source rows; three additional cases verify rejection of control characters |
| Native frontend rendering | Nine store-scope/simple/child/fallback cases per Magento version |

The semantic fixtures cover all three configurable, grouped, fixed-price bundle, and dynamic-price bundle modes; downloadable, disabled, backordered, and unmanaged-stock products; grouped minimum/summed prices; and explicit stock inclusion/exclusion. Full Generic generation contains 16 expected unique IDs. Google contains 15 without a backorder date and 16 after a valid future date is supplied. A visible configurable child retains its parent grouping; shared bundle selections do not produce duplicate IDs.

Preset validation is format-specific. Pinterest, TikTok, and OpenAI Google-compatible reject the fixture's tab/newline description, then pass with a clean description. Configurable presets are checked in child mode. Local Inventory emits only children with source records linked to the active stock. These are recorded validation outcomes, not provider ingestion results.

Pricing probes verify regular and special prices, a single-unit tier discount, exclusion of a bulk-only tier from a single-unit offer, a known 10% tax, and USD/EUR conversion at 0.8. Their database changes roll back, and the generator restores the original store currency.

## Native MSI

Magento's stock/source APIs establish two linked sources and one unlinked source. Native indexers build the custom stock index. Red has seven units at source A and zero at B; Blue has zero at A and eleven at B. The unlinked source has 99 units for each child and contributes no rows.

Parent-only, child-only, and combined modes pass at baseline, after a native negative-two reservation, after compensation, after disabling Blue's source item at B while retaining positive quantity, and after restoring it. Parent quantities and availability use the same source, child/source pairs remain unique, and unrelated sources are excluded. This verifies reservation APIs and indexing, not checkout-order placement or asynchronous consumer behavior.

## Volume envelope

During volume testing, each platform contained 5,000 synthetic simple products in a separate attribute set. All eight presets generate 5,000 correctly shaped rows with unique IDs and no remaining temporary file. Generic output is byte-identical on repeat and through 500-item queue batches. The batch probe uses real queue/process services in one transaction and one PHP process; interrupted and competing-process behavior is covered separately in the earlier operational report.

| Preset | 2.4.8 seconds / peak MiB | 2.4.9 seconds / peak MiB |
| --- | --- | --- |
| Generic | 40.3 / 176.5 | 74.5 / 186.5 |
| Google Shopping | 54.6 / 184.5 | 77.3 / 196.5 |
| Meta | 45.4 / 174.5 | 57.9 / 184.5 |
| Pinterest | 52.0 / 174.5 | 57.6 / 186.5 |
| TikTok | 72.1 / 174.5 | 68.7 / 186.5 |
| Microsoft | 65.0 / 174.5 | 63.9 / 186.5 |
| OpenAI Google-compatible | 56.7 / 174.5 | 58.1 / 186.5 |
| Local Inventory | 60.2 / 168.5 | 48.8 / 172.5 |

These runs overlap other local work and compilation. They establish a synthetic operating envelope below the 2 GiB PHP limit, not an equal-load platform benchmark or a capacity promise for a production catalog. Production shipping calculations, catalog rules, custom directives, checkout contention, and hosting resources require a representative store test.

## Operations and rollback

The [earlier operations report](2026-10-02-docker-operations-acceptance.md) records ten scheduler edge checks, nine killed-worker/competing-writer checks, seventeen checks per FTP/SFTP transport, and nine controlled simple-product MSI scenarios per version. FTP/SFTP receivers used actual protocols in isolated Docker containers, including gzip, encrypted credential persistence, repeated successful delivery, failure preservation, and temporary-file cleanup. No external recipient was contacted.

The real cron soak invokes Magento's `mageos_shopping_feed` group once per minute. The unchanged hourly schedule job runs at 04:10 and 05:10 UTC, corresponding to 00:10 and 01:10 in the configured America/New_York timezone. A dedicated feed contains one downloadable product and no upload destination. Schedule, generator, and queue code remain unchanged throughout the soak; the frontend-scope and Admin currency-provider fixes were applied between cycles. Both hourly jobs succeed at 04:10 and 05:10 UTC. The workers finish at 04:11:04 and 05:11:02 UTC on each version. Each output contains one product plus its header, is 477 bytes, and has the same checksum across the two cycles on that platform. Both queues clear. The runners stop at 05:17 UTC after 112 successful invocations per version and 111 successful process jobs. No failed or missed jobs occur. Maximum observed queue age is 60 seconds on 2.4.8 and 59 seconds on 2.4.9.

Both rollback clones passed module disable, DI compilation, storefront product-page and Admin sign-in reachability, re-enable, two successive `setup:upgrade` runs, compilation, and CLI preview. All seven module-table hashes and nine original output hashes match before and after. Both clones also reached their authenticated sixteen-feed grids after recovery. The disable/re-enable rehearsal used `6c4bc72`; the subsequent scope and currency changes add no schema or constructor changes. This rehearsal changes neither the shared Mage-OS installation nor the running cron databases.

## Shared Mage-OS deployment

Deployment applied seven PHP stock-fix paths, three frontend-scope paths, and finally the single currency-provider path. Each deployment uses a fresh module archive, original-data snapshot, tracked-file manifest, and restore script that refuses later edits. All preserve the 254 unrelated tracked/configuration paths, the six original feeds, 370 configuration rows, 290 process rows, six output hashes, and empty schedule/upload/queue/shipping tables. Cron remains at its original enabled setting; maintenance is off. Nebula remains disabled.

The currency deployment's first DI cleanup reported that `generated/code/Magento` could not be removed because it was not empty. A guarded native compilation retry passed, followed by cache cleanup and restoration of the prior maintenance state. Both Docker production compilations also pass at the final candidate.

Chrome recorded three ViewTransition opt-in aborts across preview navigations, without a module-specific stack. This is not a zero-console-error claim. Browser automation also intermittently detached; navigation was verified from the rendered page before retrying.

Native form providers prepare and round-trip every original feed without saving it. Google previews pass for existing simple, configurable, grouped, bundle, and downloadable products. Chrome verifies the original editor and configurable SKU preview, rejects product ID `-1` with a clear positive-integer message, and succeeds on retry with product ID 98. A later pass exposed the blank-currency defect described above; after repair, the original editor displays USD without saving it. Disabled fixture 187 passes an unchanged browser save and reopen with USD. All four configurable preview rows preserve their prices and other values; comparison normalizes only the time-of-day fallback in product 96's sale-date range. The date and timezone remain compared. The saved fixture also passes the browser preview. Fixture 187 was then removed after preserving its final data; the complete original module-data/file/configuration snapshot matches afterward.

Backups:

- `var/backups/shopping-feed-stock-20261003T040351Z/`
- `var/backups/shopping-feed-frontend-20261003T041340Z/`
- `var/backups/shopping-feed-currency-20261003T043602Z/`

Follow the `ROLLBACK.md` inside the applicable backup: maintenance, guarded PHP-file restoration, DI compilation, cache cleaning, and restoration of the prior maintenance state. These code rollbacks do not require database restoration or a Nebula configuration change.

## Cleanup and retained evidence

The two temporary rollback containers and their separate `shopping_feed_rollback` schemas were removed after saving final database backups. Their private application files and browser evidence remain. Both requested Magento applications remain running. The shared Mage-OS currency fixture was removed, and its six original feeds and output files match their baseline.

Both Docker databases were restored from their complete pre-test backups after the cron runners stopped. Each restored SQL dump matches its baseline, excluding only the dump completion timestamp, before cache cleanup and reindexing. Both stores return to two original products, sixteen disabled feeds, 1,073 configuration rows, sixteen process rows, and empty schedule/upload/queue/shipping tables. Nine original output files per store retain their hashes.

Cleanup removes 5,015 synthetic products and eleven test feeds per platform, including the extra native stock/store index tables. All 409 original table/view names are restored. Thirteen generated feed files and one downloadable fixture per platform are quarantined. Before/after database backups remain private. Indexers are rebuilt and ready, the invalidated page cache is refreshed, production mode and module configuration are preserved, maintenance is off, and code remains at `133af71`. Both restored applications pass authenticated Chrome feed-grid checks with their sixteen original feeds. The module state still matches its baseline after those checks. The two requested Magento applications remain available; no acceptance cron runner remains active.

Private evidence lives under `/private/tmp/shopping-feed-magento-docker-20261002/{248,249}/evidence/acceptance/`, `/private/tmp/shopping-feed-mageos-stock-20261003/`, `/private/tmp/shopping-feed-mageos-frontend-20261003/`, and `/private/tmp/shopping-feed-mageos-currency-20261003/`. It includes exact rows, hashes, native index/reservation observations, timing, failing and passing tests, cron history, deployment manifests, and cleanup results. Do not publish database backups or credentials.

## Scope boundaries

This continuation does not establish external provider ingestion, a customer's FTP/SFTP destination, consent-managed Google tag delivery, Varnish behavior, native Nebula UI Bridge rendering, a new Mage-OS 3.4.0 runtime acceptance, original Rocket Web package coexistence, or production-catalog capacity. These remain separate target/release checks where applicable. Local fixes do not restore promotion dates already erased by an earlier candidate.

Earlier UI evidence covers the unchanged controls, with the final currency provider exercised separately: [Docker forms](2026-10-02-magento-docker-acceptance.md), [six-role permissions](2026-10-02-grid-permission-acceptance.md), [Mage-OS form/output deployment](2026-10-02-mageos-latest-deployment-acceptance.md), and [preview recovery](2026-10-02-preview-followup-acceptance.md). The reusable [production acceptance plan](../../ACCEPTANCE-TEST-PLAN.md) remains a checklist for a separately approved release.
