# Meta Catalog local acceptance, 2026-09-29

The Meta Catalog template on `feat/1.2-release` was installed in an isolated Mage-OS 3.5.0 demo and generated a seven-product, 23-column TSV. The final file passed 208 independent checks against the applicable Meta product-feed requirements, including local product and image HTTP checks. Native inventory, Admin preview, and configurable landing pages also passed. Commerce Manager import acceptance remains unverified.

## Specification findings and corrections

Meta's [product data specification](https://www.facebook.com/business/help/120325381656392), [image requirements](https://www.facebook.com/business/help/686259348512056), and [variant rules](https://www.facebook.com/business/help/2256580051262113) were read directly in a browser on 2026-09-29. The [official TSV sample](https://lookaside.facebook.com/developers/resources/?id=dpa_product_catalog_sample_feed.tsv) was also downloaded for comparison.

Two discrepancies in the initial implementation were reproduced and corrected:

* Product feeds accept `in stock` and `out of stock`. The initial mapping used the broader Catalog API vocabulary. Backorders and preorders now export as `out of stock` until available.
* Brand is required. The validator now rejects a missing brand even when MPN is supplied. GTIN and MPN remain optional, with a warning when neither is supplied.

The baseline file failed for two unsupported availability values and one missing brand. Four focused regression cases failed before the corrections and passed afterward. The final generator also rejected the deliberately invalid `damaged` condition fixture. Both rejections were logged with their reasons and counted as skipped rows.

## Feed comparison

| Area | Final observed result |
| --- | --- |
| File structure | UTF-8 quoted TSV; 23 unique headers; seven complete rows; embedded quotes, comma, and `Café` round-trip correctly |
| Required fields | All emitted rows contain id, title, description, availability, condition, price, link, image_link, and brand |
| Text | IDs, titles, descriptions, brands, and populated optional fields fit the documented limits; descriptions are plain text and distinct from titles |
| Availability and condition | Only `in stock` / `out of stock` and `new` appear; native zero-stock and backorder cases also pass |
| Prices | One currency, USD; regular and sale values parse correctly; 59.95 USD sale is below 79.95 USD regular price; sale interval is valid ISO 8601 |
| Images | Every primary image returns HTTP 200 locally, is a 1080 × 1340 JPEG, and is below 8 MB |
| Landing pages | All links return HTTP 200 locally; all simple-product final prices match the file |
| Variants | Two children share `meta-pack`; group ID differs from every content ID; color combinations, links, and images are distinct |
| Variant browser checks | Blue selects automatically at $64.95; gray selects automatically at $69.95; each shows its matching image |
| Admin Test Feed | Missing brand yields no row and an actionable log; a valid product displays all 23 fields; previews preserve the generated file |

The fixtures comprise ten synthetic products: five eligible standalone products, two negative fixtures, two configurable children, and their parent. Parent context supplies the group, while only its children are emitted. Synthetic MPNs belong only to these fixtures; the preset leaves MPN and GTIN unmapped. Images come from the official [Magento sample-data repository](https://github.com/magento/magento2-sample-data/tree/2.4-develop/pub/media/catalog/product/m/b).

Final artifact SHA-256:

```text
92357826e46512c5b7ccf3be7db978d361c377c592b583c399c69ed2e6d21c89
```

## Verification

| Check | Result |
| --- | --- |
| Independent generated-file checker | 208 passed, zero failed |
| Application inventory, preview, and DI checks | 10 passed |
| PHPUnit 10 / PHP 8.4 | 459 tests, 943 assertions passed |
| PHPUnit 12 / PHP 8.5 | 459 tests, 1,110 assertions passed |
| Application integration suite / PHP 8.4 | 10 tests, 26 assertions passed; rollback preserved both feeds |
| JavaScript suite | 19 tests passed |
| Module contracts and Magento XML | 24 XML files and four feed types passed |
| Wiki validation | 32 pages and 32 sidebar targets passed |
| Magento coding standard, five changed PHP files | Zero errors; 124 warnings under the repository's warning-tolerant exit policy |
| Magento dependency compilation | Passed; final feed generated again with compiled configuration |
| Source/install parity | 576 files matched by SHA-256 |
| Feed delivery | HTTP 200; `text/tab-separated-values`; response bytes exactly match the validated file |

The local application uses PHP 8.4.24, MySQL 8.4, OpenSearch 3.8.0, the standard Mage-OS Admin, and Luma. It reuses an existing official dependency tree with a fresh database and dedicated loopback-only services. The installed module is copied from this working tree and checked by SHA-256. The demo's PHP router was configured to serve TSV with the correct content type. No existing store database or application configuration was changed.

## Local demo and evidence

The demo is left running for review:

* Storefront: `http://127.0.0.1:8099/`
* Admin: `http://127.0.0.1:8099/admin`
* Main feed: `http://127.0.0.1:8099/media/mageos-shopping-feed/mageos_meta_catalog_1.tsv`
* Native inventory check: `http://127.0.0.1:8099/media/mageos-shopping-feed/mageos_meta_catalog_2.tsv`
* Application and scripts: `/private/tmp/meta-catalog-demo-20260929`
* Private credentials: `secrets.json` in that directory, mode 0600; Admin uses Google Authenticator two-factor authentication.
* Evidence: its `evidence` directory contains the baseline and final validation JSON, specification snapshots, test logs, browser screenshots, and source/install parity manifest.

Regenerate and check the current demo file:

```sh
cd /private/tmp/meta-catalog-demo-20260929/app
/opt/homebrew/opt/php@8.4/bin/php -d memory_limit=2G bin/magento mage-os:shopping-feed:generate 1
python3 /private/tmp/meta-catalog-demo-20260929/check-feed.py
```

The demo has no generation schedules or upload destinations. These localhost URLs cannot be fetched by Meta. No file was uploaded to Meta and no account, policy, real-world identifier, or public HTTPS delivery acceptance is claimed. This fixture set does not establish every category-specific requirement or every product type. The demo lives in a temporary directory and its processes must be restarted after a reboot. The branch remains local and uncommitted; no push or release was performed.
