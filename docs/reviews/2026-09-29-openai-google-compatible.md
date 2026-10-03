# OpenAI / ChatGPT beta local acceptance, 2026-09-29

Pinterest Catalog was committed as `28e19ef` on `feat/1.2-release`. The next Phase 1 entry, OpenAI / ChatGPT (Google-compatible, beta), is implemented locally and remains uncommitted. The Mage-OS 3.5.0 demo generated seven products across 29 columns, with 164 independent checks passed. OpenAI ingestion and ChatGPT display remain unverified.

## Implementation and specification

The preset follows the stable [Google-compatible file profile](https://developers.openai.com/commerce/specs/file-upload/products#google-compatible-product-data-feeds), checked on 2026-09-29. Current primary documentation takes precedence over the recommendations attachment. The profile accepts TSV/CSV, requires brand and conditional identifiers, and requires dates for preorder/backorder rows. It needs confirmation during onboarding and does not support XML or automatically enable checkout.

The implementation reuses the existing extraction, variant mapping, generation, preview, scheduling, and upload tools. A separate validator enforces the compatibility profile's requirements. A scoped price formatter preserves zero for validation of the mobile subscription exception. A separate additional-image mapper configuration keeps comma-separated URLs when the output delimiter is changed. All seven existing feed definitions are unchanged.

The default map contains 29 fields, with real GTIN and MPN mappings initially empty. `identifier_exists` is an explicit per-product exemption and remains empty by default. It does not infer an exemption from missing data. Configurable rows require a shared group ID distinct from the item ID. The validator checks GTIN checksums and restricted prefixes, stock and condition values, text limits, currency prices, sale relationships, dates, and credential-free URLs.

The default expiration timestamp is tomorrow. Dates are metadata, not automatic stock, sale, or removal controls. The [merchant guide](../wiki/OpenAI-ChatGPT.md) explains daily full snapshots, registered seller identity, discovery eligibility, supported delivery, and the distinction from native OpenAI feeds. The [file upload guide](https://developers.openai.com/commerce/specs/file-upload/overview) specifies SFTP with stable filenames; [onboarding](https://developers.openai.com/commerce/guides/get-started) remains a separate step.

Local validation is deliberately stricter than ingestion tolerance for malformed optional GTINs, URLs, and enums: the merchant gets a skipped row and an actionable reason instead of silently losing supplied data. It does not verify real identifier assignment, currency membership, public availability, cross-row uniqueness, merchant approval, or actual destination processing.

## Runtime evidence

* Feed ID: 22, `OpenAI Google-compatible Demo`.
* Local URL: `http://127.0.0.1:8099/media/mageos-shopping-feed/mageos_openai_google_compatible_22.tsv`.
* File: `/private/tmp/meta-catalog-demo-20260929/app/pub/media/mageos-shopping-feed/mageos_openai_google_compatible_22.tsv`.
* SHA-256: `55affde2611369f63c14046e74790582d24a8c7d71f570140b96d34037ba333a`.
* HTTP 200, TSV content type, response bytes matching the disk artifact.

Before mapping availability dates, generation produced five rows and skipped four: missing dates for preorder/backorder, missing brand, and invalid condition. After mapping a synthetic `2026-10-15T09:00:00Z` date in this demo feed only, generation produced seven rows and skipped the two invalid products. No existing catalog products were changed.

A separate preview probe removed the demo MPN mapping in memory. Missing identifiers produced zero rows; explicitly setting `identifier_exists=false` produced one row while retaining the required brand. These probes did not save their mappings or replace the generated file.

The independent Python checker validates required cells, all 29 columns, Unicode and quoting, stock, prices, identifiers, date formats, short expiration, grouping, and unique variant links/images. Every exported product and primary image returned HTTP 200 locally. Simple-product prices matched their landing pages. The sale fixture retained `79.95 USD`, `59.95 USD`, its ISO interval, and the title containing Unicode, quotes, and a comma.

Actual Admin browser acceptance verified the beta setup guidance, two configurable preview rows with 29 fields each, shared group `meta-pack`, selected colors, distinct images and option URLs, and prices of `64.95 USD` and `69.95 USD`. A missing-brand preview was rejected with the specific reason. Preview preserved the generated file's checksum. Variant option fragments use the storefront selection behavior already verified for Meta.

## Verification

| Check | Result |
| --- | --- |
| Full PHPUnit 10 / PHP 8.4 | 637 tests, 1,501 assertions passed |
| Full PHPUnit 12 / PHP 8.5 | 637 tests, 1,677 assertions passed |
| Application integration | 10 tests, 26 assertions passed; transaction rollback preserved six feeds |
| JavaScript | 19 tests passed |
| Independent artifact and HTTP checks | 164 passed, zero failures |
| Module contracts and Magento XML | 24 XML files, eight feed definitions passed |
| Wiki | 36 pages and 36 sidebar targets passed |
| Changed PHP syntax | Six files passed |
| Coding standard on five runtime/test files | Zero errors, 155 warnings under the configured warning-tolerant policy |
| Mage-OS dependency injection compilation | Passed |
| Existing feed definitions | All seven match the committed definitions |
| Meta, Microsoft, TikTok, Pinterest regeneration | All four output SHA-256 values unchanged |
| Source/install parity | 594 files matched by SHA-256, including this report |

The new preset and validator tests failed before implementation. Further red tests caught loss of a boolean identifier exemption in the writer and the need to preserve a zero regular price for the scoped exception. Coverage includes 65 OpenAI-specific cases. The default empty optional taxonomy, additional-image, sizing, and subscription fields remain empty in the full runtime sample; their nonempty validation paths have unit coverage.

An extra PHPCS invocation against `dev/tests/validate.php`, outside the configured coding-standard directories, flagged its pre-existing `exit(1)`. The configured runtime/test check passes with no errors. The validator script itself passed execution and PHP syntax checking.

Evidence, scripts, logs, and inspected screenshots are retained in `/private/tmp/openai-catalog-20260929`. Under the demo application, regenerate with `bin/magento mage-os:shopping-feed:generate 22`, then run `python3 /private/tmp/openai-catalog-20260929/check-feed.py`. Expiration timestamps change on regeneration, so later valid files have a different checksum.

## Remaining acceptance

The URLs are localhost-only and the catalog is synthetic. Real merchant onboarding, format confirmation, market/currency agreement, public resources, SFTP transfer, ingestion diagnostics, and actual discovery behavior have not been tested. The local sample does not prove provider acceptance. No schedules or uploads are configured.

The four Tier 1 templates and the associated Phase 1 OpenAI beta are implemented on the development branch. Nothing was pushed, published, or released. OpenAI changes remain uncommitted.
