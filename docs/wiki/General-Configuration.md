# General configuration

General Configuration defines the store context, output location, delimiter, price behavior, and stock behavior for one feed.

> Documentation baseline: release `v1.0.0`. Last reviewed: 2026-09-09.

## Feed settings

| Setting | What it controls |
| --- | --- |
| Name | The label shown in the Admin. It is not the output filename. |
| Store View | The store context used for product attributes, URLs, prices, categories, and inventory. |
| Feed Currency | The currency used when formatting price directives. Only currencies allowed for the selected store are offered. |
| Feed Path | The directory and generated filename. Output is restricted to `pub/media/mageos-shopping-feed` and safe subdirectories. |
| Delimiter | The field separator for generated rows. Google templates default to tab-delimited output. |

Changing the store view can change the category tree and attribute values. Save the feed, then review Categories Map, currency, URLs, and representative product output again.

## Price and inventory settings

### Apply Catalog Price Rules

When enabled, catalog price rules participate in sale-price calculation. Confirm the resulting regular price, sale price, and sale dates against the selected store view and timezone.

### Use default Stock Statuses

When enabled, the module uses Magento stock information. Set it to **No** only when a product attribute intentionally carries the feed's availability state.

### Alternate Stock/Availability Attribute

Select the custom attribute used when default stock status is disabled. Supported output values are `in_stock`, `out_of_stock`, `backorder`, and `preorder`; spaces in `in stock` and `out of stock` are normalized to underscores. Unrecognized values fall back to `out_of_stock`.

### Use Qty Increments

When enabled, quantity increments participate in price calculation. Test products with non-default increments before applying this setting broadly.

### Use Stock Reservations

When enabled, reservations participate in quantity and availability calculations. Google Local Inventory enables this by default. Validate the result against the website stock and source configuration used by the selected store.

### Complex Product Context Prioritization

When enabled, simple products attached to configurable, grouped, or bundle products are prioritized for processing in their complex-product context. This can reduce duplicate or contextually wrong rows, but it adds work to generation. Measure it on large catalogs.

## Global settings

Open **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed**.

### General Info

* **Versions installed** reports the module version information available to the Admin.
* **Cron Enabled** controls the module's scheduled queue creation and processing. Direct CLI commands still work when module cron is disabled.

### Log Settings

* **Logging Level** sets the minimum log severity.
* **Log rotate (Kb)** controls when a feed log is archived based on size.

### Google

* **Enable Automatic Updates (Microdata)** adds schema.org offer data to product pages.
* **Enable Google Ads Dynamic Remarketing Events** permits a `view_item` event after a configurable selection resolves to a product.
* **Google Ads Destination ID** optionally adds a `send_to` value such as `AW-123456789`.

See [Automatic updates and schema.org](Automatic-Updates-and-Schema-org) and [Google Ads view_item events](Google-Ads-View-Item-Events) before enabling storefront behavior.

## Verify changes

After changing general settings:

1. Save and reload the feed.
2. Test a known product.
3. Confirm price, currency, availability, quantity, URL, and category context.
4. Generate a non-production file and compare row counts and values with the previous accepted file.

Text settings beginning with `[` or `{` remain text after saving and reloading. Structured array settings retain their array values. Version 1.0.0 also preserves malformed legacy text instead of failing while loading it as JSON.
