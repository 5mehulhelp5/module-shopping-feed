# Shipping

The Shipping tab configures the output produced by a column mapped to the **Shipping** directive. It does nothing unless that directive is present in Columns Map.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Settings

| Setting | Purpose |
| --- | --- |
| Methods | Selects eligible Magento shipping methods. |
| Countries | Selects the destination countries used for calculation. |
| Shipping Weight Column | Selects the saved feed column used as weight input. |
| Only Minimum Price | Emits only the lowest eligible shipping result. |
| Only Free Shipping | Emits shipping only when a free option is available. |
| Add Tax to Shipping Price | Includes tax in the calculated shipping price. |

## Carrier boundary

The module excludes real-time carrier calls from feed generation to avoid excessive or abusive requests to carrier services. Configure carrier-calculated shipping in the recipient platform when the feed cannot safely calculate it.

Select only the countries needed by the data source. Every additional destination can increase processing work and output size.

## Set up the column

1. Add a `shipping` column in Columns Map.
2. Map it to the **Shipping** directive.
3. Save the feed.
4. Return to Shipping and select methods, countries, and a saved weight column.
5. Test products across weight, price, free-shipping, and tax thresholds.

## Google configuration

Shipping requirements depend on target country and account configuration. Use the module to generate only values you can verify against Magento. Confirm the current account-level and item-level requirements in Google's product data documentation.

## Performance

Shipping is one of the more expensive directives because it can evaluate several methods and destinations for every product. If a large feed slows down, compare a test run with the Shipping directive removed before changing batch size or infrastructure.
