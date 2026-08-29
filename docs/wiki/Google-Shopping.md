# Google Shopping

The Google Shopping template supplies a product-data column map, Google taxonomy mapping, price filters, variant handling, shipping output, and optional promotion IDs. The merchant remains responsible for mapping those fields to the real catalog and meeting Google's current requirements.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Before configuration

Prepare:

* A Merchant Center account and non-serving test data source
* The target country and language
* The store view and currency that match the landing pages
* Catalog attributes for brand, GTIN or MPN, condition, and applicable variant data
* Known products covering simple, sale-price, out-of-stock, and configurable behavior

Use Google's current [product data specification](https://support.google.com/merchants/answer/7052112?hl=en) as the authority for account and item requirements.

## Configure the feed

1. Create a **Google Shopping** feed.
2. Select the target store view and currency.
3. Review the default tab delimiter and safe output path.
4. Map Magento categories to Google taxonomy values.
5. Map every identifier and variant field to the store's real attributes.
6. Review product-type, attribute-set, stock, and required-field filters.
7. Configure parent and associated-product behavior.
8. Configure Shipping only if the feed should calculate supported Magento methods.

## Critical column checks

Test at least:

* `id`
* `title`
* `description`
* `link`
* `image_link`
* `availability`
* `price`
* `sale_price`
* `sale_price_effective_date`
* `condition`
* `brand`
* MPN or GTIN, as applicable
* `google_product_category`
* `product_type`

For variants, test `item_group_id` and every applicable standard variant attribute. The current template also contains `item_group_title` and `variant_option`; validate all template columns with the intended data source rather than assuming every default is required.

## Prices and sale dates

The feed uses the selected store context and currency. Catalog price rules can participate when enabled. Sale dates use the configured store timezone.

If the rendered sale price is equal to or greater than regular price, the module leaves sale price and its effective date empty. The header and row shape remain intact.

## Product links and variants

Associated configurable rows can link to the parent product with option values in the URL fragment. The storefront integration selects both swatches and dropdowns. See [Configurable product deep links](Configurable-Product-Deep-Links).

## Promotions

When a Google Promotions companion feed is enabled, add a `promotion_id` column mapped to the **Promotion ID** directive. See [Google Promotions](Google-Promotions).

## Submit safely

1. Test representative SKUs in Magento.
2. Generate the complete file without an upload destination.
3. Parse it locally and compare sampled values with Magento.
4. Submit it to a non-serving Merchant Center data source.
5. Record item count, parse errors, warnings, and disapprovals.
6. Classify each result as a module defect, catalog-data issue, account configuration issue, or accepted warning.

Do not enable a production schedule until Merchant Center has processed the exact file successfully.
