# OpenAI / ChatGPT (Google-compatible, beta)

> Documentation baseline: release 1.2.4 (`v1.2.4`); earlier acceptance is identified by version. Last reviewed: 2026-10-20.

**Confirm the format before use.** The bundled preset retains its historical Google-compatible name and validator. OpenAI's public [product specification](https://developers.openai.com/commerce/specs/file-upload/products), checked October 20, 2026, now documents an OpenAI-format contract and no longer contains the previously linked Google-compatible section. This preset has not been updated to that contract. A locally valid file does not prove current OpenAI compatibility, ingestion, or ChatGPT display.

The differences include:

| Area | Bundled 1.2.4 preset | Current public OpenAI format |
| --- | --- | --- |
| Seller | No `seller_name` column; the historical guide relied on registration | `seller_name` is required per row; registration does not supply it |
| URLs | `link`, `image_link`, `additional_image_link` | `url`, `image_url`, `additional_image_urls` |
| Variants | `item_group_id` plus Google-style variant columns | Variant grouping needs `group_id`, `listing_has_variations=true`, and `variant_dict`. `item_group_id` is an accepted ID alias, but does not supply the other fields |
| Preorders | Local validator accepts `preorder` | The public format uses `pre_order` |
| Eligibility and removal | No native search opt-out mapping; expiration defaults to tomorrow | The current guide uses `is_eligible_search=false` for search opt-out and says date fields do not schedule removal |

This is a format gap requiring separate implementation and acceptance. Do not rename a few headers and assume the validator or variant mapping now meets the current contract. Use this beta only if OpenAI explicitly confirms acceptance of the exact exported sample and agreed transfer format.

## Existing preset behavior

Choose **OpenAI / ChatGPT (Google-compatible, beta)** in **Catalog > Mage-OS Shopping Feed > Feeds Management**. It produces quoted UTF-8 TSV at `pub/media/mageos-shopping-feed/mageos_openai_google_compatible_<feed_id>.tsv` and uses the shared UI Component editor, generation queue, Test Feed, schedules, and uploads. Test Feed requires **Generate Feed** permission in 1.2.4.

The default map has 29 columns. IDs use Magento product IDs. Configurable children share their parent's SKU in `item_group_id`, retain mapped attributes, and link to the parent with option fragments. Product links include `utm_source=openai_google_compatible`. Keep IDs and option combinations stable and unique.

Brand uses Manufacturer and is required by the local validator. Map real GTIN or MPN attributes; both start empty. `identifier_exists` starts empty, so the local validator requires identifiers. For products that truly have none, map an explicit `no` or `false` per product; brand remains required. The validator checks GTIN length and checksum and rejects restricted prefixes as identifiers.

The local validator also requires a mapped `availability_date` for its `preorder` and `backorder` states. It leaves that column empty by default, so those rows are skipped until mapped. These are the bundled validator's rules, not confirmation of current destination behavior.

Condition defaults to `new`. Map real used/refurbished condition where applicable. Optional sizing, subscription, and taxonomy fields must describe the actual offer. Restrict the exported products with **Product Filters**; the preset does not implement native search opt-out semantics.

## Local validation and freshness

Generation and **Test Feed** use the same row validator. It checks required cells, plain-text title and description limits, its stock and condition values, credential-free HTTP(S) links, variant group IDs, identifiers, prices, and dates. Invalid rows are skipped with reasons in the feed log. Add explicit [Limit column output](Filters-and-Transformations#limit-column-output) rows for the local 150-character title and 5000-character description limits; preset limits are not applied automatically in 1.2.4.

The local validator requires a positive sale price below the regular price in the same currency; invalid sale relationships reject the row. Sale intervals require a sale price, and date-only boundaries cover the full UTC day. It allows zero regular price only with mobile category `267` or `4745` and a valid same-currency `subscription_cost`. This historical exception does not establish acceptance under the current public format, which calls for positive regular discovery prices.

Additional images use comma-separated URLs even if the file delimiter changes. Encode commas inside URLs. The default expiration mapper emits tomorrow's timestamp. Expiration, sale, and availability dates do not schedule stock changes, prices, or removal. Publish current full snapshots at least daily and whenever prices or stock change; agree on removal behavior with OpenAI before relying on omission.

Local validation cannot prove public URL access, identifier authenticity, agreed currencies/markets, unique IDs across rows, landing-page prices, merchant eligibility, or destination acceptance. Never include credentials in product or image URLs.

## Delivery and acceptance

1. Complete [OpenAI onboarding](https://developers.openai.com/commerce/guides/get-started). Confirm whether the historical Google-compatible sample is accepted for this integration, including seller identity, variants, availability, eligibility, markets, currency, and exact file/compression requirements.
2. Keep uploads disabled while generating a small sample. Include ordinary products, variants, discounts, missing identifiers, preorders/backorders, and unavailable stock.
3. Obtain sample acceptance before sending a full catalog. The current [file upload guide](https://developers.openai.com/commerce/specs/file-upload/overview) describes SFTP delivery and supported compressed formats; the module's gzip option does not establish schema acceptance.
4. Configure the agreed destination in [Uploads](Uploads), with assigned credentials, a stable filename, and a trusted SHA256 host-key fingerprint confirmed with the transfer operator. Save and reopen before testing delivery.
5. Check ingestion diagnostics, counts, rejected rows, displayed seller/variant data, and actual discovery behavior before enabling schedules.

The module does not provide account access, native checkout, Ads setup, or order synchronization. Search or checkout eligibility is determined by the agreed integration and destination processing.

## References

The [product specification](https://developers.openai.com/commerce/specs/file-upload/products), [file upload guide](https://developers.openai.com/commerce/specs/file-upload/overview), and [onboarding guide](https://developers.openai.com/commerce/guides/get-started) were retrieved on October 20, 2026. The former `#google-compatible-product-data-feeds` anchor is absent. Earlier specification checks describe the historical beta; current provider documentation and explicit sample acceptance take precedence.
