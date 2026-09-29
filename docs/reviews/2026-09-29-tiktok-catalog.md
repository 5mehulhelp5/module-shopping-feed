# TikTok Catalog local acceptance, 2026-09-29

Microsoft Merchant Center was committed as `b8b226d` on `feat/1.2-release`, following Meta commit `83cc583`. The next Tier 1 preset, TikTok Catalog, is implemented locally and remains uncommitted. Its seven-product, 24-column CSV passed all 187 independent checks in the isolated Mage-OS 3.5.0 demo. An actual TikTok Ads Manager import remains unverified.

## Implementation

The preset emits quoted UTF-8 CSV with TikTok's required `sku_id` field. It reuses product extraction, variant grouping, taxonomy mapping, generation, preview, and delivery. The five existing feed definitions are unchanged.

TikTok has its own availability formatter and row validator. Backorders become `available for order`; preorders stay `preorder`. Regular and sale prices include currency. Product and Google category paths retain their first three levels. The additional-image mapper supports an explicit separator configuration; a separate TikTok mapper instance writes commas inside the quoted cell while preserving the existing default behavior for other feeds.

Validation covers required fields, configured text limits, UTF-8 and control characters, stock and condition values, optional gender and age-group values, prices, dated sales, HTTP(S) links, image path formats, category depth, and GTIN formatting. Missing both GTIN and MPN produces a review warning. Equal or higher sale prices are omitted with their dates.

The current [TikTok product parameters](https://ads.tiktok.com/resources/help/article/catalog-product-parameters?lang=en), [catalog creation instructions](https://ads.tiktok.com/resources/help/article/create-manage-catalogs?lang=en), and [catalog quality guidance](https://ads.tiktok.com/resources/help/article/best-practices-for-a-high-quality-catalog) were checked on 2026-09-29. CSV was selected from the documented scheduled-feed formats. Admin guidance and the [merchant guide](../wiki/TikTok-Catalog.md) explain that sale dates do not expire discounts at TikTok; generation and destination refresh must keep sale prices current.

## Generated artifact

* Feed ID: 12, `TikTok Catalog Demo`.
* URL: `http://127.0.0.1:8099/media/mageos-shopping-feed/mageos_tiktok_catalog_12.csv`.
* File: `/private/tmp/meta-catalog-demo-20260929/app/pub/media/mageos-shopping-feed/mageos_tiktok_catalog_12.csv`.
* SHA-256: `78a142f7443935ed44e2ba72f1409b1e9ca181aeeae8227471a1cd136abe6734`.
* HTTP 200, `text/csv; charset=UTF-8`, with response bytes matching the validated disk file.

The existing synthetic catalog covers ordinary, discounted, sold-out, backorder, preorder, brandless, invalid-condition, and configurable products. Missing brand and invalid condition cause two rows to be skipped with specific generation-log messages. Both configurable children appear under the parent SKU with distinct colors, images, and option URLs. The discounted fixture preserves `Café "Weekend", Travel Bag` and exports `79.95 USD`, `59.95 USD`, and a valid sale interval.

All seven product and image links return HTTP 200 locally. JPEG images measure 1080 by 1340 pixels. Simple-product final prices match the landing pages. The file checker also verifies required fields, quoted CSV parsing, title and description limits, supported enums, one currency, unique IDs, grouping, category depth, and differentiating variant values.

## Verification

| Check | Result |
| --- | --- |
| Full PHPUnit 10 / PHP 8.4 | 529 tests, 1,195 assertions passed |
| Full PHPUnit 12 / PHP 8.5 | 529 tests, 1,366 assertions passed |
| Application integration | 10 tests, 26 assertions passed; rollback preserved all four feeds |
| JavaScript | 19 tests passed |
| Independent generated-file checker | 187 passed, zero failed |
| Module contracts and Magento XML | 24 XML files and six feed definitions passed |
| Wiki validation | 34 pages and 34 sidebar targets passed |
| Changed PHP syntax | Eight files passed |
| Coding standard on seven runtime/test PHP files | Zero errors, 146 warnings under the configured warning-tolerant policy |
| Dependency injection compilation | Passed on Mage-OS 3.5.0 |
| Admin setup guidance | TikTok delivery, sale refresh, and alternate stock instructions verified in the rendered form |
| Admin Test Feed | Invalid condition rejected; backorder preview rendered all 24 fields with `available for order` and preserved the normal CSV |
| Source/install parity | 585 module files matched by SHA-256 |
| Existing feed definitions | All five matched the committed definitions |
| Meta regeneration | Unchanged SHA-256 `92357826e46512c5b7ccf3be7db978d361c377c592b583c399c69ed2e6d21c89` |
| Microsoft regeneration | Unchanged SHA-256 `48821e8f362c4260e61efed8a8fd4d1acd69e82b48743f538a068c46b7a946f9` |

New preset tests and the additional-image separator regression failed before implementation. The mapper regression verifies both legacy pipe separation for ordinary CSV feeds and explicit comma separation for TikTok. Generator coverage verifies quoted CSV round trips, two additional image URLs in one cell, sale suppression, stock filtering before formatting, and invalid-row rejection in preview.

Scripts, logs, screenshots, and checker results are retained in `/private/tmp/tiktok-catalog-20260929`. Regenerate with `bin/magento mage-os:shopping-feed:generate 12` under the demo application, then run `python3 /private/tmp/tiktok-catalog-20260929/check-feed.py`. No schedules or uploads are configured.

## Remaining acceptance

The runtime fixture uses blank optional video, taxonomy, and additional-image fields. Category depth and multi-image CSV behavior are covered by unit tests, not populated demo catalog rows. Configurable option fragments use the existing behavior previously verified in the Meta storefront acceptance.

The validator does not establish identifier authenticity or check digits, currency membership, cross-row uniqueness, image dimensions or public fetchability, video suitability, complete emoji detection, policy compliance, or landing-page parity. The independent local checker covers some of these for the synthetic fixture only. Real merchant data, public URLs, account and market eligibility, tracking ID alignment, and destination diagnostics still require TikTok acceptance. Localhost URLs cannot be fetched by TikTok.

Nothing was pushed or published. TikTok changes remain uncommitted. Pinterest is next on the Tier 1 list.
