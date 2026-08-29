# Generic feeds

Use a Generic feed when the recipient accepts a delimited product file but does not match one of the bundled Google templates.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Starting schema

The Generic template starts with common product fields including ID, title, description, URL, prices, images, availability, quantity, SKU, options, sale dates, shipping weight, and review data.

Every column can be renamed, reordered, removed, or mapped to another product attribute or directive. Treat the defaults as a working example, not a recipient specification.

## Output format

The default output is UTF-8 and tab-delimited:

```text
pub/media/mageos-shopping-feed/mageos_shopping_feed_<feed_id>.txt
```

The Admin permits a supported delimiter and a safe filename under `pub/media/mageos-shopping-feed`. The path cannot escape that directory, and filenames are restricted to approved characters and `.txt`, `.csv`, `.tsv`, or `.xml` extensions.

Changing the extension does not create a recipient-specific XML schema. The generator remains driven by Columns Map and output parameters. Confirm the actual file structure expected by the recipient.

## Recommended setup

1. Obtain the recipient's current field specification and a valid sample file.
2. Set the store view, currency, and delimiter.
3. Reduce Columns Map to the required fields first.
4. Map stable identifiers, URL, price, availability, and images.
5. Add optional fields and transformations after the base file parses.
6. Use required-field filters for columns that cannot be empty.

## Useful directives

Generic feeds can use static values, concatenated attributes, category paths, category-defined product types, custom options, review values, expiration dates, shipping, and complex-product inheritance. See [Columns and directives](Columns-and-Directives).

## Verification

Parse the complete file using the recipient's delimiter and encoding rules. Confirm:

* The header and every row have the same number of columns.
* Embedded delimiters, quotes, HTML, line breaks, and Unicode do not corrupt rows.
* Product count matches the intended filters.
* Complex products follow the chosen parent and associated-product modes.
* The recipient accepts a non-production upload before scheduling begins.
