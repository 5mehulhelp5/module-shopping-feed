# Magebox 3.5 full-feed validation

The six review fixes were committed as `6448e82b7c2d2c76297733406435ef3054d221c2` and installed on the local Magebox instance. Full-catalog validation then identified and resolved two additional configurable-product defects. These are compatibility and feed correctness issues; this testing does not establish a Mage-OS 3.5 regression.

## Additional fixes

- Swatch URLs contained attribute codes alone, such as `#fabric=49`. Hyva reads numeric attribute IDs from URL fragments, so the advertised variant was not selected. Generated URLs now include numeric IDs and retain the existing codes, such as `#152=49&fabric=49`. The regression test failed with the original implementation. All 38 available variants in this feed selected the correct child and displayed the advertised price in the local Hyva browser after the fix.
- Parent-stock inheritance applied a positive-quantity check to configurable parents. Their indexed quantity was zero even when they were salable, incorrectly marking 38 available children out of stock. Default-stock inheritance now checks the parent's Magento salability before evaluating child inventory. Custom stock attributes and disabled inheritance keep their existing behavior. Six regression cases cover available and unavailable parents, unavailable and backordered children, disabled inheritance, and custom stock attributes. Three cases failed before the correction.

## Local generation

- Runtime: Mage-OS 3.5.0, based on Magento 2.4.9, PHP 8.4.24.
- Module location: `<MAGENTO_ROOT>/app/code/MageOS/ShoppingFeed`.
- Store: default storefront, store ID 1, `http://mageos-latest.localhost:8080/`.
- Feed: `Magebox 3.5 Google Shopping validation`, ID 63. No SKU restriction, schedules, uploads, or shipping-cache generation.
- Command: `XDEBUG_MODE=off php bin/magento mage-os:shopping-feed:generate 63` from the Magento root.
- Result: completed status, 100% progress, 148 eligible visible products processed, 160 exported rows, five products skipped for missing images. Composite expansion and duplicate suppression mean exported rows are not a one-to-one count of visible products.
- Skipped image-less catalog entries: `giftcard-50`, `giftcard-100`, `giftcard-250`, `giftcard-500`, and `giftcard-1000`, product IDs 124 through 128. Their catalog data was not changed.
- Output: `pub/media/mageos-shopping-feed/magebox35-google-shopping-63.txt`, 182285 bytes.
- SHA-256: `c419c3a369a7bb5ae5e6f66cfba57519dbed548b5776d9d5ad513056b79de35b`.

## Validation

- UTF-8 with BOM, tab-delimited text, 29 columns in every row, 160 unique IDs.
- No missing values in ID, title, description, link, image link, availability, or price.
- Prices and currencies, sale-price relationships, sale-date intervals, boolean fields, field lengths, stock enums, shipping weights, and URL syntax passed the local validator.
- All 160 availability values matched Magento salability: 158 in stock, two out of stock.
- All 663 unique local URLs returned HTTP 200: 129 product pages and 534 primary/additional image URLs. Image responses had image content types. HTTP checks were reused after regeneration only where the URL was unchanged; variant fragments were checked separately in the browser.
- All 38 available configurable variants selected the expected child and displayed the expected regular or sale price. Browser errors were empty, with RequireJS absent.
- All 160 prices matched the corresponding page price data or browser price display. The three grouped offers matched their lowest displayed item price. Cirkel Dining Table uses the catalog's click-for-price behavior; opening its price details displayed the feed's $1,981.00 amount.
- Full PHP suite: 353 tests, 719 assertions passed. Five frontend tests passed. XML/feed-type validation and changed-file PHP syntax checks passed.

This establishes local generation and the listed format/runtime checks. It is not a Google Merchant Center acceptance result. The feed contains localhost URLs and demo catalog data; merchant identity, shipping/account configuration, identifiers, image editorial quality, and destination-specific eligibility were not certified. Format checks were informed by Google's [product data specification](https://support.google.com/merchants/answer/7052112?hl=en) and [tab-delimited data-source instructions](https://support.google.com/merchants/answer/14989239?hl=en).

## Release preflight

GitHub Actions run [34400736738](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/34400736738) exposed the previously noted null-default deprecation as an integration-test error. Explicit string normalization before column sanitization resolved it. The added regression test reproduced the failure before the fix; the full local suite then passed 354 tests with 721 assertions. Transactional probes saved and reloaded Generic, Google Shopping, and Google Local Inventory defaults without deprecation notices.

Regenerating feed 63 after this correction again processed 148 visible products and exported 160 rows with 29 columns, with no validation errors. All 160 prices and availability values still matched the recorded storefront checks. The 663 URLs were unchanged, so their earlier successful HTTP checks were reused; a fresh request for the feed returned HTTP 200 with a matching file checksum. Generated sale-date timestamps advanced with the new run. The resulting 182285-byte file has SHA-256 `4a26d2a38106d67aa7cc9a2fa2d9d1a779b632d38aa699e60c0016c4f9ae48fe`.

## Evidence and recovery

Local evidence is retained under `<MAGENTO_ROOT>/var/shopping-feed-review-20260909/`: `full-feed-config.json`, generation and validation logs, `full-feed-validation.json`, `full-feed-catalog-check.json`, and `full-feed-browser-variants.json`. `installed-commit.json` records the final installed source hashes. `variant-baseline/` retains the original full output, validation evidence, and affected files before the additional corrections.

The existing database backup and `ROLLBACK.md` remain available. No Composer dependencies, catalog entries, schedules, or upload destinations were changed. This record covers local validation before release publication. No remote storefront deployment or Merchant Center submission was performed.
