# Product options

Product Options controls how Magento custom options affect row count and option output.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Output modes

### One row

The product remains one feed row. Applicable option values are concatenated into the output used by the **Product Option** directive.

### Multiple rows

The product can produce one row for each applicable option value. This increases row count and can change IDs, URLs, prices, and other option-aware values.

## Restrict multiple rows by category

Use **Multiple rows only for products in these categories** when only part of the catalog should expand into option rows.

## Column requirement

Add a column mapped to the **Product Option** directive when the recipient needs option details. The directive parameter controls which option data is mapped.

## Verify

Test products with:

* No custom options
* One dropdown option
* Radio or checkbox options
* Price adjustments
* Options inside and outside the restricted categories

Confirm the row count, ID, URL, option value, and calculated price for every generated row.
