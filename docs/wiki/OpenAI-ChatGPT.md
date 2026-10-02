# OpenAI / ChatGPT (Google-compatible, beta)

> Documentation baseline: 1.2 development preset with unreleased `9f07e46` editor notes. Last reviewed: 2026-10-01.

When evaluating the unreleased UI Component editor, read [Admin UI Component forms](Admin-UI-Component-Forms). The repairs are now deployed and tested on `mageos-latest`; the guide records the exact candidate, follow-up fixes, and remaining limits. Template generation checks do not establish provider ingestion acceptance.

This preset generates quoted UTF-8 TSV for OpenAI's Google-compatible discovery profile. It is a beta integration: generated files have been checked locally, but OpenAI ingestion and ChatGPT display have not been verified. OpenAI must confirm this profile for the registered feed. It is separate from the native OpenAI schema and does not provide account access or checkout.

## Configure

Choose **OpenAI / ChatGPT (Google-compatible, beta)** in **Catalog > Mage-OS Shopping Feed > Feeds Management**. Select the store view and agreed currency, then review **Columns Map**. The default file is `mageos_openai_google_compatible_<feed_id>.tsv` in `pub/media/mageos-shopping-feed`.

The default map has 29 columns. IDs use Magento product IDs. Configurable children share their parent's SKU as `item_group_id`, retain selected attributes, and link to the parent with option fragments. Keep IDs and option combinations unique and stable. Product links include `utm_source=openai_google_compatible`.

Brand uses Manufacturer and is required. Map real GTIN or MPN attributes; both start empty. `identifier_exists` starts empty, meaning identifiers are required. For products that truly have none, map an explicit `no` or `false` per product. Do not blanket-exempt an incomplete catalog or substitute SKU for MPN. Brand remains required for exempt products. The validator checks GTIN length and checksum; restricted prefixes cannot satisfy the identifier requirement.

Map real `availability_date` values for preorders and backorders. Use a date or ISO timestamp with timezone. The preset leaves this column empty until mapped, so those rows are skipped by default. It preserves `in_stock`, `out_of_stock`, `preorder`, and `backorder`.

Condition defaults to `new`; map actual used or refurbished condition. Optional sizing, subscription, and taxonomy fields must describe the actual offer. Use **Filters** to restrict the export to products intended for discovery. Search opt-out columns do not exclude products in this profile.

## Validation and freshness

Generation and **Test Feed** use the same row validator. It checks required cells, plain-text title and description limits, stock and condition values, credential-free HTTP(S) links, variant group IDs, identifiers, prices, and dates. Invalid rows are skipped with reasons in the feed log. Default output limits are 150 characters for title and 5,000 for description.

Sale prices must be positive, below the regular price, and in the same currency. Invalid sale relationships reject the row. Sale intervals require a sale price; date-only boundaries include the full UTC day. The normal Magento sale mapper supplies current offers.

The price formatter preserves zero only for validation. Zero-price rows need a mobile category ID (`267` or `4745`) and a valid same-currency `subscription_cost`. Other free products are skipped. Additional images use comma-separated URLs even if the delimiter is changed to CSV. Encode commas inside URLs.

The default expiration mapper emits a timestamp for tomorrow. Expiration and sale dates are metadata: they do not schedule stock changes, sales, or removal. Refresh full snapshots at least daily and whenever prices or availability change. Removal can take time; agree on removal handling with OpenAI before relying on omission.

Local validation cannot confirm public accessibility, assigned identifier authenticity, approved currencies or markets, unique IDs across rows, landing-page prices, merchant eligibility, or destination acceptance. Review these against the exported file and OpenAI diagnostics. Credentials must never appear in product or image URLs.

## Delivery and acceptance

1. Complete OpenAI onboarding and confirm the Google-compatible format, merchant display name, markets, currency, and destination. The merchant name comes from registration, not an uploaded seller column.
2. Generate with uploads disabled. Test ordinary products, variants, discounted items, missing identifiers, and unavailable stock.
3. Review a small sample with OpenAI before sending the full catalog. Use TSV, CSV, or the corresponding supported gzip format; XML is not supported on this path.
4. Configure the agreed SFTP destination in **Uploads**, using the assigned credentials and a stable filename. Gzip is supported by the module. Keep the same filename on each refresh.
5. Check ingestion diagnostics, row counts, rejected products, and actual discovery behavior before enabling scheduled transfers.

Accepted compatibility rows are search eligible with checkout disabled. Uploaded native eligibility flags, return-policy fields, and seller fields do not enable those capabilities. Ads eligibility and any additional markets depend on the registered integration. This preset does not send API requests, configure Ads, or synchronize orders.

## References

Verified against the stable [Google-compatible product specification](https://developers.openai.com/commerce/specs/file-upload/products#google-compatible-product-data-feeds), [file upload guide](https://developers.openai.com/commerce/specs/file-upload/overview), and [onboarding guide](https://developers.openai.com/commerce/guides/get-started). These current primary sources take precedence over earlier template recommendations.
