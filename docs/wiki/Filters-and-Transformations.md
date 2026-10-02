# Filters and transformations

Product Filters decides which products reach the output and how selected column values are changed. Rule order matters.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

## Catalog selection

### Allow Out of Stock

Controls whether out-of-stock products remain eligible for the feed. Complex-product sections have separate out-of-stock controls for associated products.

### Submit only products of these types

Limits the feed to selected Magento product types. Product visibility and complex-product context still apply. A not-visible simple product may be emitted as part of an eligible configurable product.

### Submit only products that have these attribute sets

Limits output to selected product attribute sets.

## Output transformations

### Replace empty values

Fills an empty output column from a configured value or another source. The target columns must already exist in the saved Columns Map.

Replacement rules can be ordered and nested. Test the first successful source and the all-empty case.

### Find And Replace

Applies string replacement at column output. Large rule sets add work to every applicable row, so measure their effect on large catalogs.

### Limit column output

Truncates selected output columns to a character limit. In 1.1, limits count UTF-8 characters without splitting a multibyte character. Limits run before output encoding and HTML cleanup. If several limits target the same column, they run in order.

### Skip Products with empty

Rejects rows when selected required columns are empty. Save Columns Map before selecting fields here.

## Google-only filters

Google Shopping adds:

* **Skip Products with Price above**
* **Skip Products with Price below**
* **Adwords Price Buckets**

The upper and lower price filters do nothing when left empty. Price buckets build a value for a column mapped to the matching directive. The Admin label retains the older Adwords name, but the output is simply a configurable bucket value.

## Processing order

Think of the feed as a pipeline:

1. Select eligible catalog products.
2. Build mapped column values.
3. Replace empty values.
4. Apply find-and-replace rules.
5. Apply output limits.
6. Reject rows missing required output.

Test interacting rules together. A transformation that produces an empty value can affect a later required-field filter.

HTML entities are decoded before a final pass removes line breaks and, for output without an enclosure, the active field delimiter. An explicitly configured enclosure preserves embedded delimiters. Generic comma-delimited feeds preserve commas and quote each CSV field, including embedded quotes. Encoded tabs such as `&#09;` therefore cannot add an extra column to a tab-delimited row. Verify the complete generated file, including rows containing encoded punctuation or whitespace.

## Verification

For every filter change, keep one fixture that should be included and one that should be excluded. Use **Test Feed** first, then compare expected product and skipped counts in a complete run.
