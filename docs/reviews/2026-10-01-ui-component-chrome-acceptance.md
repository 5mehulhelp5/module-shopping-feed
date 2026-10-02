# Deployed UI Component forms: Chrome acceptance

> Follow-up: the [Mage-OS deployment acceptance report](2026-10-02-mageos-latest-deployment-acceptance.md) records the subsequent `mageos-latest` deployment and two additional preview fixes. Deployment-status statements below describe this earlier run.

Date: October 1, 2026. Candidate: `9f07e46b56e5cd6daa403d67f48a45ba5eddc896` on `feat/ui-component-editor`.

Target: `http://mageos-latest.localhost:8080/admin/`, Mage-OS 3.5.0, PHP 8.4.24, Magento/backend Admin theme. Nebula remains disabled. Tests used authenticated Chrome sessions and 13 disposable `UIQA-20261001-*` feeds across the initial and resumed runs. No module source was changed during this review.

**Result: browser acceptance failed on two UI Component regressions.** The resumed Chrome run confirmed a category-mapping generation failure and promotion-date data loss. The automated suite passed but did not catch these failures. Broader lifecycle and generation checks are recorded below. No fixes were applied during this review.

**Subsequent local work:** the [repair and verification report](2026-10-02-ui-component-editor-fixes.md) records fixes tested in a separate disposable installation. It does not change this deployed `9f07e46` result or imply deployment to `mageos-latest`.

This is the current runtime acceptance decision for `9f07e46`. The [earlier disposable-platform report](../ui-component-editor-acceptance.md) and [reconciled code review](2026-10-01-ui-component-editor-review.md) have different coverage; neither establishes restricted-role browser acceptance or overrides the failures below. The code review adds isolated probes of malformed category input and header serialization, not additional browser passes. The [operating guide](../wiki/Admin-UI-Component-Forms.md) and [developer contract](../ui-component-editor.md) describe the candidate and upgrade implications.

## Local deployment record

The candidate was committed and deployed to the local `mageos-latest` module at `/Users/matt/code/mageos-latest/app/code/MageOS/ShoppingFeed`. Deployment verification recorded 405 runtime files matching `9f07e46`, successful DI compilation and Admin asset deployment, maintenance off, and all 16 Nebula modules disabled. The stable Admin entry point is `http://mageos-latest.localhost:8080/admin/`; session-specific keyed URLs are not reusable documentation links.

The deployment backup, `VERIFICATION.md`, `ROLLBACK.md`, manifest, and conflict-protected `restore-module.py` are in `/Users/matt/code/mageos-latest/var/backups/shopping-feed-ui-20261001T114023Z/`. That verification predates authenticated testing; this report supplies the subsequent browser results. The rollback restores only the 41 changed/added migration paths and requires DI/static/cache rebuilds. It does not restore database data or change Nebula configuration.

No remote push, merge, candidate CI run, tag, release, or wiki publication is established by this local deployment or review. The editor change is unreleased. Code rollback would not repair erased promotion dates; retain configuration backups separately. All mutations in this browser run used disposable fixtures and were cleaned up.

## Confirmed findings

### High: new category mappings omit the ID required by the generator

On temporary Google feed 172, set Living Room priority to `0`, taxonomy to `Furniture`, and product type to `UIQA > Living`; exclude Gift Cards; save and reopen. The UI correctly preserves these values and propagates taxonomy/product type to empty descendants. However, Test Feed for the valid `atlas-pouf` SKU reports generation failed.

The current exception is `Undefined array key "id"` in `Model/Product/Mapper/Generic/Simple/ProductTypeByCategory.php:110`, reached through `GoogleCategoryByCategory`. The persisted category objects contain `d`, `p`, `tx`, and `ty`, but no `id`. The new `view/adminhtml/web/js/form/categories.js:33` creates exactly that incomplete object. Sorting reindexes numeric category keys, so the mapper needs the embedded ID to match the product's categories. Existing objects that already contain an ID can retain it through the merge; newly created mappings do not acquire one.

This is a migration regression. Fix the category serialization contract and add a browser-save-to-generator regression test. Preservation in the editor alone is insufficient. Evidence: `config-before-second-save.json`, the exception at `2026-10-02T00:24:21Z`, and `category-preview-failure.png` in the resumed evidence directory.

### High: saving and reopening a Google feed corrupts promotion dates

The promotion title and counter persist, but all four date controls for Free Shipping displayed blank after reopening. The saved values before that reload were `10/01/2026` and `4/02/2027`. Saving again without changing the dates persisted empty strings for effective-from, effective-to, display-from, and display-to. The counter remained `1`, so ordinary saves did not increment it again.

`Model/Promotions/Provider.php:122` expects `Y/m/d`; `prepareDate()` rejects the month-first strings written by the UI. `Ui/DataProvider/Feed/Form/Metadata.php:184` declares a year-first date contract, but the actual browser serialization was month-first. The UI/provider date conversion must agree with the existing stored contract. This is a migration regression and a data-preservation blocker. No original sales rule or feed was edited. Evidence: `config-before-second-save.json`, `config-172.json`, `settings-after-second-save.log`, and `promotion-dates-blank.png`.

### Medium: non-Google presets can silently select the feed for storefront microdata

Creating Meta feed 166 with no microdata control in its General section saved `use_microdata=1`; the grid displayed `[microdata]`. Original feeds all had that flag disabled. Subsequent non-Generic presets produced the one-feed-per-store warning despite offering no control to opt out.

`Controller/Adminhtml/Feed/Builder.php:78` defaults an omitted `use_microdata` to `1` for every non-Generic type. `Ui/DataProvider/Feed/Form/Metadata.php:85` exposes the control only for Google Shopping. The previous editor's `Block/Adminhtml/Feed/Edit/Tab/AddGoogleGeneralMicrodataFieldsObserver.php:62` has the same Google-only restriction, and the Builder default was not changed by this migration. This is existing behavior, not a demonstrated UI Component regression.

Impact: creating a non-Google feed can unexpectedly select its mapping for storefront microdata, and users cannot switch it off from that feed's editor. A future fix should define eligible types explicitly, default ineligible new feeds to off, and preserve explicit saved values during ordinary edits. No fix was applied in this review. Deleting the temporary Meta feed removed its temporary selection.

### Medium: cloning a feed with a literal filename retains the same output destination

Cloning feed 172 created disabled feed 173 with its own identity and `_clone` name, but retained `uiqa_20261001_resume.tsv`. `Model/Feed/Copier.php:75` copies all configuration unchanged, and `Model/Feed/OutputPath.php:207` substitutes the feed ID only when the filename contains `%s`. A clone using a literal filename therefore targets the same output file if subsequently enabled and generated. The collision was verified from UI values and source; no overwrite was performed. This behavior predates the UI migration.

## Chrome checks completed

| Check | Observation |
| --- | --- |
| Original grid | Six existing feeds loaded under the default Admin theme. |
| All eight presets | Generic, Google Shopping, Meta, Pinterest, TikTok, Microsoft, OpenAI, and Google Local Inventory were created and saved through the UI; each appeared in the grid. |
| Required validation | Empty Name was rejected with a required-field error. |
| Conditional fields | Other Delimiter and the alternate stock attribute appeared under their controlling selections. |
| General persistence | Name, unique filename, USD currency, and custom delimiter survived save and reopen. |
| Save controls | Primary Save and Save and Continue worked. Intermittent Chrome input failures were not classified as a Save defect. |
| Columns Map | Added a static-value mapping, preserved a duplicate order of 10, and round-tripped quotes, backslash, HTML-like text, literal `${literal}`, and Unicode. No multiline browser-input claim is made. |
| Product Filters | Changed product-type selection and added/saved a find-and-replace rule. |
| Schedule | Edited batch mode and batch limit; persistence verified. |
| Upload destination | Saved a synthetic SFTP destination on reserved `uiqa.invalid`; stored password was encrypted. No connection or upload was attempted. |
| Unsafe path | Server rejected `pub/media/../uiqa-outside` and recovered the form with a clear allowed-directory error. |
| Failed-save password recovery | Newly entered synthetic password was absent from returned HTML; the old password remained masked and the form asked for re-entry. |
| Final-row deletion | Last find/replace rule, schedule, and upload row were removed in the form and saved; all three persisted collections were empty. |
| Grid filtering/sorting | Name filter isolated exactly eight temporary records; Name sort worked. |
| Bulk status | Disabled and then enabled exactly the eight temporary feeds; all eight grid statuses changed accordingly. |
| Empty-selection guard | Mass action showed the expected “You haven't selected any items!” dialog. |

Synthetic feed IDs were 164 through 171. Final-row deletion was checked on feed 164. The initial six feed IDs were 63, 153, 154, 155, 156, and 157.

## Supporting generator checks

While Chrome was blocked, the module CLI performed single-product previews for `atlas-pouf` on all eight temporary feeds. These are backend checks, not acceptance of the Admin Test Feed workflow.

| Preset | Result |
| --- | --- |
| Generic | One product, 18 columns; the custom literal retained quotes, backslash, template text, and Unicode. Normal output processing stripped HTML tags. |
| Google Shopping | One product, 31 columns. |
| Pinterest | One product, 23 columns. |
| Google Local Inventory | One product, seven columns; default source, quantity 100, in stock. |
| Meta and TikTok | Zero products with an explicit missing-brand diagnostic for this catalog fixture. |
| Microsoft | Zero products with an explicit identifier-mapping diagnostic. |
| OpenAI | Zero products with explicit missing-brand/identifier diagnostics. |

All CLI commands exited zero, including those that skipped the unsuitable fixture. Those zero exits do not establish valid output for the four skipped presets. No original output file was replaced and no remote provider was contacted.

## Automated checks rerun

| Command | Result |
| --- | --- |
| `MAGENTO_ROOT=/Users/matt/code/mageos-latest php /Users/matt/code/mageos-latest/vendor/bin/phpunit -c phpunit.xml.dist` using PHP 8.4.24 | 722 tests, 1,908 assertions passed. |
| `node --test dev/tests/frontend/*.test.cjs` | 28 passed. |
| `php dev/tests/validate-magento-xml.php /Users/matt/code/mageos-latest` | 26 XML files passed. |
| `php dev/tests/validate.php` | Consolidation passed, eight feed types. |
| `php dev/tests/validate-wiki.php` | 36 pages and 36 sidebar targets passed. |
| `composer validate --strict --no-check-publish` | Passed. |

An initial PHPUnit invocation omitted `MAGENTO_ROOT` and stopped before tests; the corrected invocation above passed. This was a test invocation error, not a product failure. Prior disposable Magento 2.4.8/2.4.9 acceptance is recorded separately in [UI Component editor acceptance](../ui-component-editor-acceptance.md), not counted as fresh Chrome coverage here.

## Coverage status and remaining gates

- View Log and bulk deletion through the UI passed in the final resumed session, recorded below. No authentication-blocked checks remain.
- Category generation and promotion-date preservation require fixes and a new acceptance run.
- Restricted-role/ACL acceptance, external upload delivery, live scheduled execution, provider ingestion, and large-catalog performance were not exercised by this run.

Chrome repeatedly returned `Detached while handling command`, then explicitly reported: “Google Chrome is blocking automation because another extension UI is open on this page.” Reads occasionally worked while input/navigation remained blocked. That interruption is not recorded as an application defect. The user was asked to dismiss the extension UI. No alternate browser or browser-session extraction was used.

## Resumed Chrome run

The user supplied a new authenticated Chrome window and asked to continue. A new baseline was captured before creating Google feed 172 and Generic feed 174. The existing six feeds were excluded from all mutation selections.

| Check | Result |
| --- | --- |
| Category round trip | Priority zero, taxonomy inheritance, custom product type, and exclusion persist in the form. Generation fails as described above. |
| Product Options | Changed to concatenated single-row mode; saved `options_mode=0`. |
| Configurable Products | Disabled inherited parent out-of-stock status and saved the exact separator ` | `, including spaces. |
| Grouped Products | Changed and saved minimal-price mode. |
| Bundle Products | Enabled and saved combined weight. |
| Shipping | Selected United States and disabled minimum-price-only mode; both persisted. No carrier call was made. |
| Schedule | Removed the final schedule from Google feed 172; grid showed None. |
| Promotions | Feed-specific title persisted; Submit as new promotion disabled itself after one click and saved counter 1. Date preservation failed separately. |
| Store View | Switched to WANDS store 2 and reloaded. Its 2,024 category controls appeared, headed WANDS Catalog. Switched back to store 1 and saved. |
| Admin Test Feed | Clean Generic feed 174 previewed Atlas Pouf by SKU and by product ID 1, returning 17 fields, price 679.20 USD, quantity 100, and in_stock. |
| Preview validation | Invalid SKU produced a clear not-found error. Clearing Product displayed the required-field error. An early click before initialization did not establish a reliable validation result and was repeated after the form was ready. |
| Run Now | Cancel did not queue Google feed 172. Confirm on clean Generic feed 174 enqueued it and the grid showed Pending. |
| Full generation | Processed only queued feed 174 using `bin/magento mage-os:shopping-feed:generate 174`; exit 0. It processed 148 products, added 160 rows, and reported five skipped rows. Grid then showed Completed. Scheduled cron processing was not used to establish this result. |
| Download | Downloaded through the grid link. SHA-256 matched the server file: `3ea9a1c4456dc3683417c67722e1d6e5e909c0bad19eb9a7ef6fee745149245e`. All 160 data rows had 17 columns. |
| Clone | Created disabled feed 173 with a distinct ID/name; configuration copied, including the literal filename collision noted above. |
| Delete Feed | Cancel preserved clone 173. Confirm removed it through the Admin UI and returned the success message. |

Chrome returned to the sign-in page when opening View Log after the download. The user was asked to sign back in; no credentials or browser session stores were inspected. The sign-in page was left available. The remaining disposable data was cleaned up while authentication blocked further browser work.

## Final resumed Chrome run

After the user restored authentication, the remaining two checks passed:

| Check | Result |
| --- | --- |
| View Log | Opened the grid action for original feed 63 in a new tab. It rendered the feed-specific heading, START entries, product warnings, processed/added/skipped counts, and FINISHED entries. This was a read-only check of existing history; the feed was not regenerated. |
| Bulk deletion | Created Generic feed 175 (`UIQA-20261001-Final`) and cloned it to disabled feed 176. Selected exactly those two checkboxes, verified all six original feed checkboxes were unchecked, then used Actions > Delete. Admin reported `A total of 2 record(s) have been deleted.` The grid returned to six records. |
| Deletion interaction | Bulk Delete executes immediately, without a confirmation dialog. Cancellation therefore does not apply to this action. The grid XML has no confirmation configuration and was unchanged by the migration. Individual Delete Feed cancellation was tested earlier on clone 173. |
| Final grid | Cleared the temporary Name filter and sorted by ID. All six original feeds remained visible; the final page's browser error log was empty. |

Private evidence is under `/private/tmp/shopping-feed-chrome-20261001-final/`: `view-log.txt`, `view-log.png`, `final-grid.txt`, `final-grid.png`, `browser-results.json`, and `final-preservation-check.json`. The test records were backed up before the UI deletion, with an inverse restore script retained. The deletion removed exactly two feeds, 130 configuration rows, and two schedule rows. No generated files, uploads, queue entries, process rows, or shipping rows were created by these final fixtures.

The final comparison matched the new baseline exactly for all captured module table rows, original output-file hashes, `app/etc/config.php` hash, and the cron setting. All temporary feeds are gone. The two migration regressions remain unresolved; completion of these checks does not change the failed acceptance result.

## Cleanup and evidence

Because Chrome remained blocked, cleanup used the same feed model deletion method as the Admin controller, with exact ID/name guards and a transaction. This does not count as a passed Admin deletion test. Before deletion, the eight test records and their children were backed up with a restore script. Cleanup removed eight feeds, 529 configuration rows, and seven schedule rows. Uploads, queue, process, and shipping rows for these test feeds were already empty.

Final comparison exactly matched the baseline for all rows in the feed, feed_config, feed_schedule, feed_upload, feed_queue, and process tables. The six original output-file hashes, `app/etc/config.php` hash, and cron-enabled value matched too. No test feeds or schedules remain. Source changes, commits, pushes, and deployments were not performed during this test run.

Private evidence is under `/private/tmp/shopping-feed-chrome-20261001/`: `browser-results.json`, `preview-cli-164.log` through `preview-cli-171.log`, `preview-cli-results.json`, `final-preservation-check.json`, baseline/current snapshots, and the temporary-record cleanup backup and inverse script. The private snapshots contain configuration data and should not be published.

For the resumed run, evidence is under `/private/tmp/shopping-feed-chrome-20261001-resume/`. Clone 173 was deleted through Admin after testing Cancel. Guarded server-side cleanup then removed only feeds 172 and 174, their 136 configuration rows, one schedule, and 165 generated process rows. A private backup and inverse script were retained. The generated TSV, downloaded copy, and feed-174 log were moved into `quarantine/` with hashes and restore paths. The exception log retains the diagnostic from the failed category preview.

`final-preservation-check.json` confirms exact baseline equality for all six captured module tables, the original output files, configuration hash, and cron setting. There are again exactly six original feeds, 370 configuration rows, 290 process rows, and no schedules, upload destinations, or queued feeds. No temporary feeds remain. The new acceptance report is uncommitted; unrelated workspace files were preserved.
