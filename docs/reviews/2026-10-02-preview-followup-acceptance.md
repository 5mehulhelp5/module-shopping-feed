# Committed preview fix verification

Candidate: `c07de812e8cb740073c213734bc54f029b8a31c1` on `feat/ui-component-editor`. Tested October 2, 2026. This continues the [Mage-OS deployment acceptance](2026-10-02-mageos-latest-deployment-acceptance.md) for the null URL parameter and Test Now button fixes.

## Server-side checks

| Check | Magento Open Source 2.4.8 | Magento Open Source 2.4.9 |
| --- | --- | --- |
| Complete unit suite | 801 tests, 1,866 assertions | 801 tests, 2,126 assertions |
| Official integration suite | 16 tests, 32 assertions | 16 tests, 32 assertions |
| CLI single-product preview | All eight presets passed | All eight presets passed |
| Committed runtime parity | All 408 files match | All 408 files match |
| Application mode | Production | Production |

The integration suites use the dedicated `shopping_feed_integration` databases and separate generated metadata. The previews use the retained disabled fixture feeds, with no schedules or uploads. Generic's stored URL directive parameter is null, so both CLI runs exercise the repaired PHP 8.4 path. Each preset returns one product for `ui-product-1`, without deprecation output. Feed, configuration, schedule, upload, and queue table hashes are unchanged after the CLI checks.

The test commands are Magento's `vendor/bin/phpunit -c app/code/MageOS/ShoppingFeed/phpunit.xml.dist` with `MAGENTO_ROOT` set, Magento's official `dev/tests/integration/phpunit.xml` profile, and `bin/magento mage-os:shopping-feed:generate <fixture-feed-id> ui-product-1`. Evidence remains under `/private/tmp/shopping-feed-magento-docker-20261002/{248,249}/evidence/preview-followup-*`.

## Browser checks

Magento 2.4.8 passes Generic SKU preview, invalid Product ID recovery, and a valid retry with Product ID 2. The SKU submission is a POST to the preview controller and returns HTTP 200 with the expected product URL. The invalid ID remains visible with the message `Product ID must be a positive integer.`; the valid retry returns product 2.

Magento 2.4.9 now passes the same three cases after the blocking Chrome extension popup was dismissed. The Generic SKU preview contains the expected product URL and `1.00 kg` shipping weight. An invalid Product ID of `-1` retains the input and displays the recoverable validation error. Retrying with Product ID 2 returns product 2; captured network events show a POST to `/admin/mageos_shopping_feed/feed/test/` followed by HTTP 200, without the former navigation to the missing index controller.

All three 2.4.9 submissions used the rendered Test Now button through the browser's normal click action. The captured browser error log is empty. Screenshots, response snapshots, the retry's request/status evidence, and table-preservation results are retained alongside the server-side logs.

Fresh before/after hashes match across all seven module tables on 2.4.9: 16 feeds, 1,073 configuration rows, 16 process rows, and no schedules, uploads, queued feeds, or shipping-cache rows. All feeds remain disabled, the Generic URL parameter remains null, and the instance remains in production mode. No new fixtures or accounts were created for this follow-up.

Chrome's input automation still requires explicit field change events sent through its debugging interface after replacing field text. These events reach the normal field handlers; no UI registry data is assigned and no form save method is called directly. The 2.4.8 checks also used the rendered button's click handler through the debugging interface. Native keyboard entry remains outside this acceptance claim, as recorded in the deployment report.

## Deployed state

The 408 runtime files on `mageos-latest` also match `c07de81`. Its deployment manifest now records the committed source revision while retaining the pre-repair backup and rollback hashes. All 254 preserved target files remain unchanged. A fresh database/file snapshot confirms exact preservation of all seven module tables, six original output files, application module configuration, cron setting, and disabled Nebula modules. Maintenance remains off.

The broader eight-preset persistence/output checks and six-role permission matrices remain the evidence in the preceding reports. Native Nebula bridge rendering and the other release gates were not added to this follow-up. Nothing was pushed, merged, released, or published to the wiki.
