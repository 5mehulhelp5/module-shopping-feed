# Categories and taxonomy

Categories Map filters the catalog and maps Magento categories to marketplace taxonomy or product-type values.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Feed Localization

Localization selects the taxonomy locale used by a feed type that provides taxonomy data. Product titles, descriptions, and other catalog attributes still come from the selected store view.

Use a taxonomy locale that matches the language and target market of the product data. Save and retest category mappings after changing the feed's store view or locale.

## Include products without a category

When enabled, the feed can include products without an applicable enabled category. Those rows may not have a taxonomy value. Use a required-field filter or a replace-empty rule if the recipient requires one.

## Category priority

Products can belong to several categories. Category priority controls which applicable category wins when the feed needs one mapped value. Review products assigned across several category depths before relying on a global result.

## Mapping fields

The category tree can provide two distinct outputs:

* **Taxonomy** for the recipient's category identifier, such as `google_product_category`
* **Type** for a merchant-defined product type, often written to `product_type`

The mapped output only appears when Columns Map contains the matching directive.

## Filtering with the category tree

Use the category tree to include or exclude the intended catalog scope. Test:

* Products in an included category
* Products in an excluded category
* Products assigned to both
* Products in a disabled category
* Products with no category

## Google taxonomy

The Google Shopping feed downloads and searches taxonomy data for the selected locale. Treat the downloaded taxonomy as external data and confirm current accepted categories against Google's product data documentation.

## Local Inventory source mapping

Google Local Inventory changes this tab. It removes taxonomy localization and category-priority fields, then adds **Inventory Source to Google Store Code**.

Enter one mapping per line:

```text
warehouse_indy=INDIANAPOLIS-01
warehouse_chicago=CHICAGO-01
```

The left side is a Magento MSI source code. The right side is the store code expected by Google. Unmapped sources keep their Magento source code.

Verify mappings against the selected website stock. Sources that are not linked to that stock should not produce rows.
