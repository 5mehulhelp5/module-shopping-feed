# Pinterest Catalog local acceptance, 2026-09-29

TikTok Catalog was committed as `bcdef0b` on `feat/1.2-release`. Pinterest Catalog is now implemented locally and remains uncommitted. The isolated Mage-OS 3.5.0 demo generated eight products across 23 columns. Its independent checker passed 267 checks and found eight image-dimension issues. The demo file is not fully compliant with the published image guidance, and Pinterest import acceptance remains unverified.

## Implementation

The preset uses quoted UTF-8 TSV and reuses extraction, variant grouping, taxonomy mapping, generation, preview, scheduling, and delivery. All six existing feed definitions remain unchanged.

Pinterest has a separate availability formatter and row validator. Backorders become `out of stock`; preorders stay `preorder`. Brand and condition are optional, but a supplied condition must be supported. Configurable output requires `item_group_id`. The shared category formatter now accepts a depth: Pinterest uses five levels for one merchant category path; TikTok retains its three-level default. Mapped Google taxonomy is preserved separately for Pinterest.

Validation covers required fields, field lengths, scalar cells, UTF-8, control characters, plain descriptions, supported enums, positive currency prices, dated sales, URLs, optional GTIN formatting, image lists, and paired variant-name/value lists. Missing identifiers and undated discounts produce review warnings. Equal or higher sale prices are omitted with their dates.

The [merchant guide](../wiki/Pinterest-Catalog.md) includes setup and a manually configured supplemental-feed pattern using Generic feeds. A narrow supplemental file cannot use the Pinterest validator because it requires a complete primary row. The module does not infer regions, configure tracking, or create sources in Pinterest.

## Specification evidence

The current [retail catalog specification](https://help.pinterest.com/en/business/article/before-you-get-started-with-catalogs), [source setup instructions](https://help.pinterest.com/en/business/article/data-source-ingestion), [supplemental source guidance](https://help.pinterest.com/en/business/article/add-a-supplemental-data-source), and official [TSV example](https://s.pinimg.com/sub/helpcenter/assets/pinterest_product_sample_tsv_feed.tsv) were checked on 2026-09-29. These take precedence over the recommendations document where details differ, including optional condition and the current field limits.

Pinterest's Help Center labels the optional sale-date column `sale_price_effective_date_attribute`. Its official [API schema and ingestion diagnostics](https://github.com/pinterest/api-description/blob/main/v5/openapi.yaml) use `sale_price_effective_date`. The preset follows the latter. The downloadable examples do not contain a sale-date column. Actual ingestion must confirm date handling; this source discrepancy is not resolved by local parsing.

## Generated artifact

* Feed ID: 17, `Pinterest Catalog Demo`.
* URL: `http://127.0.0.1:8099/media/mageos-shopping-feed/mageos_pinterest_catalog_17.tsv`.
* File: `/private/tmp/meta-catalog-demo-20260929/app/pub/media/mageos-shopping-feed/mageos_pinterest_catalog_17.tsv`.
* SHA-256: `026b83a25881ca3995674d6e24c440107dced5e853b3357a05a8b0d43510e33c`.
* HTTP 200, `text/tab-separated-values; charset=UTF-8`; response bytes matched the disk file.

The synthetic fixtures cover ordinary, discounted, sold-out, backorder, preorder, missing-brand, invalid-condition, and configurable products. The missing-brand product remains eligible. The invalid-condition row is skipped with a specific log message. Both configurable children retain their parent group, distinct colors, images, and option URLs. The sale fixture preserves Unicode, embedded quotes, and a comma, with `79.95 USD`, `59.95 USD`, and an ISO date interval.

All eight product and image links return HTTP 200 locally. Simple-product final prices match their landing pages. Variant grouping and distinct URLs/images pass; those option fragments use the storefront behavior previously verified for Meta.

## Image findings

All eight primary-image checks found JPEGs measuring 1080 by 1340 pixels. Pinterest's current published primary-image guidance calls for at least 1000 by 1500. The checker records these as failures and exits nonzero. It reports zero failures among the other 267 checks.

The implementation validates image URL syntax without fetching merchant images. It does not enlarge images or substitute placeholders to make this fixture pass. Use suitable source imagery before destination acceptance. Localhost and port 8099 are also unsuitable for a hosted Pinterest source; a real source needs a public URL and the documented hosting ports.

## Verification

| Check | Result |
| --- | --- |
| Full PHPUnit 10 / PHP 8.4 | 572 tests, 1,337 assertions passed |
| Full PHPUnit 12 / PHP 8.5 | 572 tests, 1,511 assertions passed |
| Application integration | 10 tests, 26 assertions passed; rollback preserved five feeds |
| JavaScript | 19 tests passed |
| Independent file and runtime checker | 267 passed; eight image-dimension findings; no other failures |
| Module contracts and Magento XML | 24 XML files and seven feed definitions passed |
| Wiki validation | 35 pages and 35 sidebar targets passed |
| Changed PHP syntax | Six files passed |
| Coding standard on five runtime/test files | Zero errors, 140 warnings under the configured warning-tolerant policy |
| Dependency injection compilation | Passed on Mage-OS 3.5.0 |
| Installed category formatters | Pinterest retained five levels; TikTok retained three through actual Magento DI |
| Admin browser | Setup guidance verified; brandless preview rendered 23 fields; invalid condition rejected; normal file preserved |
| Source/install parity | 589 module files matched by SHA-256 |
| Existing feed definitions | All six matched the committed definitions |
| Meta regeneration | Unchanged SHA-256 `92357826e46512c5b7ccf3be7db978d361c377c592b583c399c69ed2e6d21c89` |
| Microsoft regeneration | Unchanged SHA-256 `48821e8f362c4260e61efed8a8fd4d1acd69e82b48743f538a068c46b7a946f9` |
| TikTok regeneration | Unchanged SHA-256 `78a142f7443935ed44e2ba72f1409b1e9ca181aeeae8227471a1cd136abe6734` |

The new preset and five-level formatter tests failed before implementation. Coverage includes optional brand and condition, invalid values, Unicode lengths, malformed dates, sale suppression, quoted TSV round trips, multiple image URLs in one cell, configurable grouping requirements, preview rejection, and stock filtering before formatting.

Evidence, scripts, logs, and screenshots are retained in `/private/tmp/pinterest-catalog-20260929`. Regenerate with `bin/magento mage-os:shopping-feed:generate 17` under the demo application, then run `python3 /private/tmp/pinterest-catalog-20260929/check-feed.py`. The checker intentionally returns failure until the image findings are resolved. No schedules or uploads are configured.

## Remaining acceptance

Image dimensions, the sale-date naming discrepancy, public hosting, real merchant data, account eligibility, and actual import diagnostics remain open. Built-in validation does not establish identifier authenticity, currency membership, cross-row uniqueness, image fetchability or suitability, tax/shipping treatment, or landing-page parity. Optional taxonomy and additional-image fields are blank in the runtime fixtures; their formatting has unit coverage, with category-depth DI checked separately. Supplemental feeds are documented but were not created or imported.

Nothing was pushed or published. Pinterest changes remain uncommitted. All four Tier 1 templates are implemented on the development branch; this is not a released or destination-approved version.
