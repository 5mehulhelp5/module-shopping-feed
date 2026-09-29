# Microsoft Merchant Center acceptance, 2026-09-29

The next Tier 1 preset, Microsoft Merchant Center, is implemented locally on `feat/1.2-release`, following Meta commit `83cc583`. It generated eight product rows and 24 columns in the existing isolated Mage-OS 3.5.0 demo. All 256 independent file, field, product-link, image, and price checks passed. An actual Microsoft Merchant Center import has not been performed.

## Implementation

The preset uses UTF-8 tab-delimited `.txt` with unenclosed cells. The required ID is placed last to prevent trailing tabs when optional columns are empty. Existing product extraction, configurable grouping, taxonomy mapping, queue, preview, and delivery code are reused. All four existing feed definitions are unchanged.

Microsoft has its own availability formatter and row validator. Backorders become `out of stock`; preorders remain `preorder`. Regular prices include currency, while sale prices use the native numeric format. The validator checks required preset values, field lengths, supported values, URLs, prices, dated sales, identifier formatting, and unsafe repeated/tab-separated cell values. It rejects contradictory identifier-absence declarations and logs incomplete manufacturer data for review.

The implementation follows the current [Microsoft product attributes](https://learn.microsoft.com/en-us/advertising/msa-help/hlp_ba_conc_aboutbingmerchantcentercatalogfile) and [file requirements](https://learn.microsoft.com/en-us/advertising/msa-help/hlp_ba_conc_bmcwhatiscatalog), checked on 2026-09-29. These correct two assumptions in the recommendations document: hosted text feeds use `.txt`, and `backorder` is not a listed availability value. The new [merchant guide](../wiki/Microsoft-Merchant-Center.md) covers setup and remaining catalog-specific review.

## Generated artifact

* Feed ID: 7, `Microsoft Merchant Center Demo`.
* URL: `http://127.0.0.1:8099/media/mageos-shopping-feed/mageos_microsoft_7.txt`.
* Local file: `/private/tmp/meta-catalog-demo-20260929/app/pub/media/mageos-shopping-feed/mageos_microsoft_7.txt`.
* SHA-256: `48821e8f362c4260e61efed8a8fd4d1acd69e82b48743f538a068c46b7a946f9`.
* HTTP 200, `text/plain`, with response bytes matching the validated disk file.

The synthetic catalog covers ordinary, discounted, sold-out, backorder, preorder, brandless, invalid-condition, and configurable products. Both configurable children appear with distinct colors, images, and option URLs under the same parent group. The sale fixture exports `79.95 USD`, `59.95`, and a valid dated interval. Unicode, embedded quotation marks, and commas are preserved.

The invalid-condition fixture is skipped. The brandless fixture remains with an identifier warning because Microsoft's brand requirement depends on whether the manufacturer assigned one. This intentionally differs from Meta's unconditional required-brand check. No manufacturer identifiers were invented for real products; all fixture identifiers are synthetic.

All eight product links and their images return HTTP 200 locally. The JPEG images are 1080 × 1340, below 16 MB. Simple-product prices match their landing pages. Configurable output is checked for grouping and distinct links/images; the previous Meta browser acceptance of those same option fragments and storefront prices remains applicable.

## Verification

| Check | Result |
| --- | --- |
| Focused Microsoft unit suite | 34 tests, 122 assertions passed |
| Full PHPUnit 10 / PHP 8.4 | 493 tests, 1,065 assertions passed |
| Full PHPUnit 12 / PHP 8.5 | 493 tests, 1,234 assertions passed |
| Application integration | 10 tests, 26 assertions passed; rollback preserved all three feeds |
| JavaScript | 19 tests passed |
| Independent generated-file checker | 256 passed, zero failed |
| Module contracts and Magento XML | 24 XML files and five feed definitions passed |
| Wiki validation | 33 pages and 33 sidebar targets passed |
| Changed PHP syntax | Five files passed |
| Coding standard on four runtime/test PHP files | Zero errors, 132 warnings under the configured warning-tolerant policy |
| Dependency injection compilation | Passed on Mage-OS 3.5.0 |
| Admin browser | Microsoft setup guidance displayed; invalid condition rejected; preorder preview rendered all 24 fields and preserved the generated file |
| Source/install parity | 580 module files matched by SHA-256 |
| Meta compatibility | Regenerated Meta output retained SHA-256 `92357826e46512c5b7ccf3be7db978d361c377c592b583c399c69ed2e6d21c89` |

The new preset tests failed before implementation. A separate generator regression reproduced an equal-sale-price row being rejected for missing dates; it now omits that non-discounted sale and its dates as intended. Existing Google, Generic, Local Inventory, and Meta tests pass.

Evidence, validation scripts, screenshots, and logs are retained in `/private/tmp/microsoft-catalog-20260929`. The demo uses the existing dedicated local database and search services, with no schedules or uploads configured. Regeneration is `bin/magento mage-os:shopping-feed:generate 7` under the demo application; its independent checker is `python3 /private/tmp/microsoft-catalog-20260929/check-feed.py`.

## Remaining acceptance

The current preset does not implement zero-down installment offers or infer country/category-specific shipping and apparel requirements. Identifier authenticity, every conditional attribute, real tax treatment, public image crawling, account eligibility, and destination diagnostics need review on an actual merchant catalog. Localhost URLs cannot be fetched by Microsoft. The Microsoft changes remain uncommitted and unpushed; only the preceding Meta implementation was committed in this task.
