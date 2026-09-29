# Product options

Product Options controls how Magento custom options affect row count and option output.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

## Output modes

### One row

The product remains one feed row. Applicable option values are concatenated into the output used by the **Product Option** directive.

### Multiple rows

The product can produce one row for each applicable option value. This increases row count and can change IDs, URLs, prices, and other option-aware values.

## Restrict multiple rows by category

Use **Multiple rows only for products in these categories** when only part of the catalog should expand into option rows.

## Column requirement

Add a column mapped to the **Product Option** directive when the recipient needs option details. The directive parameter controls which option data is mapped.

## Storefront deep links

Simple-product option links use fragments such as `#optionId=valueId`. Luma initializes the existing RequireJS widget. Hyva selects a native JavaScript template through its layout handle, waits for Alpine initialization, and dispatches the option's change event so selection, price, and validation update together.

Dropdown, multiselect, radio, and checkbox options are supported. Unknown, empty, or malformed values leave existing selections unchanged. Hyva requires `hyva.alpineInitialized`; see [Status and compatibility](Status-and-Compatibility) for the helper's minimum versions and CSP handling.

## Verify

Test products with:

* No custom options
* One dropdown option
* Radio or checkbox options
* Price adjustments
* Options inside and outside the restricted categories

Confirm the row count, ID, URL, option value, and calculated price for every generated row.
