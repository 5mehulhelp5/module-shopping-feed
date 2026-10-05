# Filters and transformations

Product Filters decides which products reach the output and how selected column values are changed. Rule order matters.

> Documentation baseline: release `v1.2.1`, with the unreleased issue #14 correction identified below. Last reviewed: 2026-10-05.

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

### HTML cleanup

Version 1.2.1 removes raw and escaped HTML tags, comments, and style/script content from column values. It also decodes double-encoded entities. Literal comparisons such as `3 < 5 > 2` remain text.

The unreleased [issue #14 fix](https://github.com/mage-os-lab/module-shopping-feed/issues/14) also removes tags with stray attribute quotes while preserving the following description text. For example, `<img alt="3.5" core" /></p><h3>3.5" Thick - "Hot Flow" Options</h3>` becomes `3.5" Thick - "Hot Flow" Options`. Quoted comparisons such as `<img alt="a > b">` and `<img alt="a < b">` are removed with the image tag.

### Limit column output

Truncates selected output columns to a character limit without splitting a multibyte UTF-8 character. Since 1.2.1, limits run after output encoding, HTML cleanup, entity decoding, and whitespace cleanup. If several limits target the same column, they run in order.

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
2. Resolve each mapped column value. For text values, apply find-and-replace rules, output encoding, markup/entity cleanup, delimiter/whitespace cleanup, and then output limits.
3. If a mapped value is empty, try its configured replacement sources.
4. Apply feed-specific row filters and formatters.
5. Reject rows missing required output.

Test interacting rules together. A transformation that produces an empty value can affect a later required-field filter.

HTML entities are decoded before a final pass removes line breaks and, for output without an enclosure, the active field delimiter. An explicitly configured enclosure preserves embedded delimiters. Generic comma-delimited feeds preserve commas and quote each CSV field, including embedded quotes. Encoded tabs such as `&#09;` therefore cannot add an extra column to a tab-delimited row. Verify the complete generated file, including rows containing encoded punctuation or whitespace.

## Verification

For every filter change, keep one fixture that should be included and one that should be excluded. Use **Test Feed** first, then compare expected product and skipped counts in a complete run.
