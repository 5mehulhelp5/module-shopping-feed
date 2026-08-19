# Production Acceptance Test Plan

## Purpose

Use this plan to decide whether `MageOS_ShoppingFeed` is ready for its first production release. Run it against the exact release commit on real store copies before enabling live schedules or uploads.

The minimum platform acceptance target is Mage-OS 3.4.0. Add another currently supported Magento Open Source store when available. A store with Multi-Source Inventory is required for Local Inventory acceptance.

## Release record

Complete this table once per candidate.

| Item | Value |
| --- | --- |
| Candidate commit | |
| Package version | |
| Tester | |
| Test start and end | |
| Store URL | |
| Platform and version | |
| PHP version | |
| Database version | |
| Search service and version | |
| Store timezone | |
| Base and display currencies | |
| Catalog size | |
| MSI enabled | Yes / No |
| Existing feed modules | |
| Google test destination | |
| FTP or SFTP test destination | |

Keep command output, screenshots, generated files, checksums, remote delivery evidence, and Merchant Center diagnostics with this record.

## Required store coverage

| Profile | Required purpose | Assigned store |
| --- | --- | --- |
| A | Mage-OS 3.4.0, PHP 8.4, clean module install | |
| B | Currently supported Magento Open Source release | |
| C | MSI store with at least two enabled sources linked to one stock | |
| D | Store with an original Rocket Web feed package installed, if coexistence is part of release acceptance | |

Profiles may share a store when it satisfies every stated condition. Profile A and Profile C are mandatory.

## Safety controls

- [ ] Test on staging or an isolated production copy with a current database and media backup.
- [ ] Record the restore command and responsible operator before installation.
- [ ] Keep module cron disabled until the schedule section of this plan.
- [ ] Keep uploads disabled until generated files have been reviewed.
- [ ] Use a Merchant Center test data source, test account, or non-serving destination.
- [ ] Use remote filenames that cannot overwrite an active production feed.
- [ ] Do not reuse a live promotion feed or live Local Inventory destination.
- [ ] Redact passwords, private keys, access tokens, customer data, and internal paths from shared evidence.
- [ ] If an original Rocket Web module is present, do not disable, uninstall, migrate, or alter it during coexistence testing.

## Test data

Prepare known SKUs and record their expected values before generation.

| Fixture | Required state | SKU or ID |
| --- | --- | --- |
| Simple A | Enabled, visible, in stock, regular price | |
| Simple B | Enabled, visible, sale price with active dates | |
| Simple C | Out of stock | |
| Simple D | Backorders enabled with zero quantity | |
| Simple E | Stock management disabled | |
| Simple F | Disabled or excluded from catalog | |
| Configurable A | Two enabled children with different price and stock states | |
| Configurable B | Children split across at least two MSI sources | |
| Grouped A | Two associated products | |
| Bundle A | Required and optional selections | |
| Downloadable A | Enabled and visible | |
| Text edge case | Quotes, commas, tabs, line breaks, HTML, and non-ASCII text | |
| Category edge case | Included, excluded, disabled, and mapped categories | |
| Promotion A | Active cart rule that should be exported | |
| Promotion B | Inactive or expired cart rule that should not be exported | |

For MSI, record the expected source code, Google store code, physical quantity, reservation-adjusted salable quantity, and availability for every Local Inventory SKU.

## 1. Candidate and automated checks

- [ ] Confirm the checkout is clean and points at the recorded candidate commit.
- [ ] Confirm `composer.json` declares `OSL-3.0`.
- [ ] Confirm the repository contains no conflicting license identifier.
- [ ] Run `composer validate --strict --no-check-publish`.
- [ ] Run `php dev/tests/validate.php`.
- [ ] Run PHP syntax checks for every PHP and PHTML file.
- [ ] Run `php dev/tests/validate-magento-xml.php <magento-root>`.
- [ ] Run the unit suite with the candidate checkout and the target platform's PHPUnit installation.
- [ ] Confirm the PHPUnit 12 run reports no PHPUnit notices.
- [ ] Run Magento coding-standard checks used by CI.
- [ ] Run the module integration suite used by CI.
- [ ] Run `bin/magento setup:di:compile` in production mode.
- [ ] Confirm every required GitHub check is green for the exact candidate commit.

Acceptance: every command exits successfully. Warnings require an attached explanation and explicit release approval.

## 2. Installation and upgrade behavior

Run on Profile A first.

- [ ] Capture `bin/magento module:status MageOS_ShoppingFeed` before installation.
- [ ] Install the candidate through the same Composer or source path intended for release testing.
- [ ] Run `bin/magento module:enable MageOS_ShoppingFeed`.
- [ ] Run `bin/magento setup:upgrade`.
- [ ] Run `bin/magento cache:clean`.
- [ ] In production mode, deploy static content for the store locales.
- [ ] Confirm `bin/magento module:status MageOS_ShoppingFeed` reports enabled.
- [ ] Confirm `setup:upgrade` is idempotent on a second run.
- [ ] Confirm the `mageos_shopping_feed_*` tables exist and contain no unexpected data.
- [ ] Confirm the output directory is `pub/media/mageos-shopping-feed`.
- [ ] Confirm module logs are limited to `var/log/mageos_shopping_feed_*.log`.
- [ ] Confirm the Admin and storefront load without PHP exceptions, JavaScript errors, or broken static assets.
- [ ] Confirm indexers remain valid or complete normally after reindexing.

Acceptance: installation is repeatable, compilation succeeds, and no unrelated tables, configuration, files, or modules are changed.

## 3. Identity and coexistence

Run Profile D when an original Rocket Web module is available.

- [ ] Record the original module's enabled state, table names, configuration, output files, logs, cron jobs, and CLI commands.
- [ ] Install and enable `MageOS_ShoppingFeed` without changing the original module.
- [ ] Confirm both Admin areas remain available under their own routes and ACL resources.
- [ ] Confirm the new module uses only `mageos_shopping_feed_*` tables and configuration paths.
- [ ] Confirm the original feed records, schedules, upload records, files, and logs are unchanged.
- [ ] Generate one new-module feed with a unique filename and no upload destination.
- [ ] Confirm the original module's file and status do not change.
- [ ] Confirm CLI commands, cron group, cache identity, RequireJS aliases, layout handles, and UI component names do not collide.

Acceptance: the modules can be evaluated together without migration, overwrite, duplicate remote submission, or mutation of the original installation.

## 4. Admin access and feed lifecycle

- [ ] Sign in as a full administrator and open **Catalog > Mage-OS Shopping Feed > Feeds Management**.
- [ ] Open global settings under **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed**.
- [ ] Create one Generic, one Google Shopping, and one Google Local Inventory feed.
- [ ] Save each feed, reload it, and confirm every configured field persists.
- [ ] Edit columns, filters, categories, product-type modes, schedules, and upload rows. Confirm add, edit, and delete controls work.
- [ ] Clone a feed. Confirm it receives its own identity, schedules, uploads, output file, and logs.
- [ ] Test grid filters, sorting, mass actions, generation action, log view, and delete action.
- [ ] Submit malformed required fields and confirm validation is clear and the stored feed is unchanged.
- [ ] Try an output path outside `pub/media/mageos-shopping-feed`. Confirm it is rejected or safely contained.
- [ ] Create a restricted Admin role with view access only. Confirm mutation routes and controls are unavailable.
- [ ] Use a role without module access. Confirm direct Admin URLs are denied.
- [ ] Confirm state-changing requests enforce the Admin form key.

Acceptance: authorized workflows work, unauthorized users cannot read or mutate feed configuration, and unsafe output paths cannot escape the module directory.

## 5. Single-product test generation

For every fixture SKU that applies, run:

```bash
bin/magento mage-os:shopping-feed:generate <feed_id> <sku>
```

- [ ] Confirm the command exits successfully and identifies the requested product only.
- [ ] Confirm invalid feed IDs and missing SKUs fail clearly without creating a normal feed or queue entry.
- [ ] Compare every displayed value to the Admin product, website, currency, tax, category, and inventory state.
- [ ] Confirm the test command does not alter the last successful production file.
- [ ] Confirm secrets and credentials do not appear in console output or logs.

## 6. Generic feed output

- [ ] Generate the full Generic feed from Admin and CLI.
- [ ] Confirm the file is created only in the configured safe output directory.
- [ ] Confirm headers, delimiter, enclosure, encoding, and line endings match configuration.
- [ ] Confirm columns remain in the configured order and duplicate column names behave as configured.
- [ ] Confirm quotes, delimiters, line breaks, HTML, Unicode, and empty values do not corrupt rows.
- [ ] Confirm included and excluded categories behave as configured.
- [ ] Confirm disabled products and products excluded by filters are absent.
- [ ] Confirm configured find-and-replace, price buckets, output limits, and empty-column rules work.
- [ ] Confirm simple, configurable, grouped, bundle, and downloadable product modes match their parent and associated-product settings.
- [ ] Enable complex-product duplicate checking and confirm a child is not emitted both standalone and as an associated product.
- [ ] Confirm URLs use the selected store view and are valid externally.
- [ ] Confirm image URLs, additional images, stock, quantity, weight, identifiers, and custom attributes match the source catalog.
- [ ] Confirm regular, final, special, tier, tax-inclusive, and tax-exclusive prices match the selected feed settings.
- [ ] Confirm currency conversion uses the selected store and feed currency.
- [ ] Count expected products and compare that count with file rows, generator status, and logs.

Acceptance: the file parses without row-shape errors and every sampled value matches the recorded source expectation.

## 7. Google Shopping feed

- [ ] Generate the Google Shopping feed with its default columns.
- [ ] Validate `id`, `title`, `description`, `link`, `image_link`, `availability`, `price`, `condition`, `brand`, `gtin`, `mpn`, `google_product_category`, and `product_type` for representative SKUs.
- [ ] Confirm configurable children use stable IDs, correct item group IDs, correct deep links, and the configured inheritance rules.
- [ ] Confirm every row with `item_group_id` also has `item_group_title`, `variant_option`, and each applicable standard variant attribute.
- [ ] For apparel variants, confirm `color`, `size`, `gender`, and `age_group` match the selected child product.
- [ ] Confirm sale price and sale price effective dates match Magento dates in the store timezone.
- [ ] Confirm equal or greater sale prices produce empty `sale_price` and `sale_price_effective_date` values.
- [ ] Confirm the header contains `sale_price` and `sale_price_effective_date` and has no empty column names.
- [ ] Confirm taxonomy autocomplete and saved category mappings work after reload.
- [ ] Confirm shipping values and weight units match Merchant Center expectations.
- [ ] Confirm the product header is `promotion_id`, never `promotions_id`, and IDs appear only for products and rules that qualify.
- [ ] Parse the full file with the intended delimiter and encoding. Confirm each row has the expected column count.
- [ ] Confirm no product line ends with a tab delimiter.
- [ ] Upload to a non-serving Merchant Center data source.
- [ ] Record item count, parse results, warnings, and disapprovals after processing completes.
- [ ] Investigate every account-level or item-level error. Classify warnings as accepted, store-data issues, or release blockers.

Acceptance: Merchant Center accepts the file format, sampled products match Magento, and no module defect causes an item disapproval.

## 8. Local Inventory and MSI

Run on Profile C with two sources and at least one reservation against a test SKU.

- [ ] Confirm Magento Inventory APIs are enabled and both sources are linked to the tested website stock.
- [ ] Configure an explicit Magento source code to Google store code mapping.
- [ ] Generate a Local Inventory feed in parent-only mode.
- [ ] Generate it in associated-only mode.
- [ ] Generate it in parent-and-associated mode.
- [ ] Confirm each eligible SKU has one row per intended source and no duplicate row for the same SKU and store code.
- [ ] Confirm sources not linked to the website stock are absent.
- [ ] Confirm each row uses the correct mapped store code.
- [ ] Confirm source quantity and availability are calculated for the same source.
- [ ] For configurable parents, confirm quantity and availability use their associated products while associated rows remain controlled by the selected mode.
- [ ] Confirm a second source row is not removed by normal duplicate-product protection.
- [ ] Confirm parent and child adapters do not remain in test mode after generation.
- [ ] Confirm in-stock, out-of-stock, backorder, and unmanaged-stock fixtures produce the expected availability.
- [ ] Place a test order or reservation, regenerate, and confirm reservation-aware salable quantity changes as expected.
- [ ] Cancel or compensate the reservation, regenerate, and confirm the quantity returns to the expected value.
- [ ] Disable one source, regenerate, and confirm its rows are removed without affecting the other source.
- [ ] Upload to a non-serving Local Inventory data source and record processing diagnostics.

Acceptance: source rows, store codes, quantities, availability, and parent or child modes match the recorded MSI state with no lost or duplicated rows.

## 9. Google Promotions

- [ ] Configure one eligible active cart rule and one ineligible rule.
- [ ] Save and reload all promotion fields. Confirm no values disappear or move to another feed.
- [ ] Generate the promotion file and confirm only eligible rules appear.
- [ ] Confirm promotion IDs in the Shopping feed match the generated promotion IDs.
- [ ] Use **Submit as new promotion** and confirm the intended ID changes exactly once.
- [ ] Confirm special-character and duplicate-word validation works in Admin.
- [ ] Confirm store, product, coupon, date, and redemption restrictions match the source rule.
- [ ] Upload to a non-serving Promotions data source and record diagnostics.

Acceptance: the promotion file and product promotion IDs agree, saved settings persist, and no inactive or ineligible rule is exported.

## 10. Scheduling, queue, and batch processing

- [ ] Set the store timezone to the recorded non-UTC test timezone.
- [ ] Add a schedule for the current store hour, save it, and run `bin/magento mage-os:shopping-feed:schedule -v`.
- [ ] Confirm exactly one eligible queue entry is created.
- [ ] Process or prepare another schedule earlier the same day, then change it to the current store hour and save it.
- [ ] Run the schedule command again. Confirm the edited schedule is eligible today and creates exactly one new queue entry.
- [ ] Run the schedule command again without another edit. Confirm it does not create a duplicate queue entry.
- [ ] Confirm a future schedule is not queued early.
- [ ] Confirm a disabled feed or disabled global cron setting is not queued.
- [ ] Run `bin/magento mage-os:shopping-feed:generate` and confirm it processes the queue successfully.
- [ ] Enable batch mode with a small limit. Confirm offsets advance, the final file contains every expected row once, and completion status is correct.
- [ ] Interrupt a batch between chunks. Restart processing and confirm it resumes or restarts safely without a truncated accepted file.
- [ ] Run overlapping process attempts. Confirm locking prevents concurrent writers from corrupting the file or processing the same queue twice.
- [ ] Run the `mageos_shopping_feed` cron group for at least two complete hourly schedule cycles and monitor queue age, process state, and logs.

Acceptance: timezone handling, same-day edits, de-duplication, batch completion, and recovery behave predictably with no stuck queue or partial accepted file.

## 11. FTP, SFTP, and gzip delivery

Use isolated remote directories and filenames.

- [ ] Configure two upload rows with different credentials or destinations. Save and reload them.
- [ ] Confirm each row uses only its own credentials and remote path.
- [ ] Upload an uncompressed feed over FTP if FTP support is part of the release environment.
- [ ] Upload an uncompressed feed over SFTP.
- [ ] Enable gzip for each supported transport and upload again.
- [ ] Download each remote file, decompress when needed, and compare its checksum or parsed content with the accepted local file.
- [ ] Confirm gzip-enabled uploads use the expected remote filename and contain a valid gzip stream.
- [ ] Configure an invalid host, credential, and remote path separately. Confirm each fails clearly without deleting or replacing the last valid remote file.
- [ ] Confirm connection errors do not expose passwords, private keys, or full credentials in Admin messages, console output, or logs.
- [ ] Confirm temporary compressed files are removed after success and failure.
- [ ] Confirm retrying a failed upload does not generate duplicate remote submissions.

Acceptance: every enabled transport delivers the exact accepted payload, settings remain isolated per destination, and failure is safe and diagnosable.

## 12. Frontend integration

- [ ] Enable Google microdata for one store view and disable it for another.
- [ ] Open simple and configurable product pages in both store views.
- [ ] Confirm enabled pages contain one valid product and offer data set with correct SKU, currency, price, availability, and condition.
- [ ] Confirm disabled pages contain no module microdata.
- [ ] Change a configurable selection and confirm its deep link and selected offer data remain correct after page load.
- [ ] Enable Google Ads `view_item` output with a test destination ID.
- [ ] Confirm one event is emitted with the expected product identity and value.
- [ ] Confirm no event is emitted when the feature is disabled or the destination ID is empty.
- [ ] Check the browser console, CSP report, page source, and structured-data validator for errors.
- [ ] Confirm full-page cache and Varnish do not leak one store view's currency, price, or feature state into another.

Acceptance: frontend output is store-scoped, valid, cache-safe, and absent when disabled.

## 13. Volume and operational behavior

Run with a catalog and configuration representative of the intended first production store.

- [ ] Record catalog size, eligible product count, feed row count, runtime, peak PHP memory, output size, and log size.
- [ ] Generate every enabled feed serially.
- [ ] Generate through cron and the queue using the intended batch size.
- [ ] Confirm runtime remains inside the store's cron window.
- [ ] Confirm memory remains below the configured limit with operational headroom.
- [ ] Confirm database connections, temporary storage, and disk space return to normal after completion.
- [ ] Confirm logs do not grow without rotation and do not contain repeated unexplained warnings.
- [ ] Confirm generation does not materially degrade storefront response time, indexing, checkout, or other cron groups.
- [ ] Repeat an unchanged generation and confirm stable row count and stable identifiers.

Record the accepted operating envelope:

| Metric | Result | Accepted limit |
| --- | --- | --- |
| Eligible products | | |
| Output rows | | |
| Runtime | | |
| Peak memory | | |
| Output size | | |
| Peak temporary disk | | |

## 14. Failure recovery and rollback rehearsal

- [ ] Back up feed configuration and generated files before the rehearsal.
- [ ] Disable module cron and uploads.
- [ ] Stop an in-progress generation and identify its queue, process, temporary file, and log state.
- [ ] Follow the documented operator recovery, then regenerate and compare the accepted output.
- [ ] Disable `MageOS_ShoppingFeed`, clean caches, and confirm the storefront and Admin remain usable.
- [ ] Re-enable it, run `setup:upgrade`, and confirm saved configuration and accepted files remain intact.
- [ ] Confirm rollback does not require deleting shared files or altering an original Rocket Web module.
- [ ] Record the exact rollback commands, expected data retained, and restore point.

Acceptance: operators can stop new work, restore service, and recover feed generation without data loss or changes outside the module boundary.

## Defect handling

Record each failure with:

- Candidate commit and store profile
- Exact prerequisite and test step
- Expected and actual result
- Reproduction rate
- Relevant feed, SKU, source code, queue ID, and timestamps
- Sanitized logs, screenshots, generated row, and configuration
- Severity, owner, fix commit, and retest evidence

Severity rules:

- **High:** incorrect price, availability, identifier, destination, credential use, authorization, path containment, duplicate or missing submission, data loss, corrupted output, or inability to install, generate, schedule, upload, or recover.
- **Medium:** incorrect non-critical field, broken Admin workflow with a practical workaround, misleading status, recoverable retry defect, or material compatibility problem outside the minimum release path.
- **Low:** cosmetic issue, copy problem, or low-impact usability defect that does not threaten output correctness or operation.

No open High or Medium defect may be accepted for the first production release without an explicit written exception.

## Final release gates

- [ ] The exact candidate passes all automated checks and required GitHub jobs.
- [ ] Mage-OS 3.4.0 installation, compilation, and full functional acceptance pass.
- [ ] Generic, Google Shopping, Local Inventory, and Promotions outputs pass where configured.
- [ ] Local Inventory parent and associated quantities pass on real MSI data.
- [ ] Same-day schedule edits and duplicate prevention pass in the store timezone.
- [ ] Every enabled transport delivers an exact copy of the accepted payload.
- [ ] Merchant Center test destinations parse the applicable files without a module-caused error.
- [ ] Coexistence passes when an original Rocket Web module is in release scope.
- [ ] Volume testing establishes an accepted runtime and memory envelope.
- [ ] Rollback and recovery are rehearsed and recorded.
- [ ] No unresolved High or Medium defects remain.
- [ ] Release notes, installation instructions, license, provenance, and this plan match the candidate.
- [ ] A human reviewer approves the exact commit for the permanent repository.

## Sign-off

| Role | Name | Decision | Date | Evidence location |
| --- | --- | --- | --- | --- |
| Engineering | | Pass / Fail | | |
| Store operator | | Pass / Fail | | |
| Feed or marketing owner | | Pass / Fail | | |
| Release owner | | Approve / Reject | | |

Approval applies only to the recorded commit and tested configuration. Any code change after sign-off requires affected checks to be rerun and a new release decision.
