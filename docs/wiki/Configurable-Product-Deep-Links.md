# Configurable product deep links

Associated configurable rows can link to the parent product with the child's option selections encoded in the URL fragment. The storefront script reads those values and selects matching swatches or dropdowns.

> Documentation baseline: release `v1.0.0`. Last reviewed: 2026-09-09.

## Enable unique links

In the feed's **Configurable Products** tab:

1. Choose a mode that emits associated products.
2. Enable **Unique urls for associated products not visible**.
3. Map the feed's link column to **Product URL**.
4. Test a child product row.

The generated link uses the parent product URL. Configured query parameters remain in the query string, while super-attribute values are appended as a fragment.

Numeric attribute IDs are always included. Swatch attributes also retain their codes for legacy renderers. For example, if attribute `fabric` has ID `152` and the selected option is `49`, the fragment is `#152=49&fabric=49`. The numeric ID lets Hyva select the variant during its normal initialization.

When microdata is enabled, an `aid` query parameter can identify the associated product used for server-rendered offer data.

## Storefront behavior

On Luma, the configurable selection script:

* Reads fragment parameters first, then query parameters
* Selects matching swatch options
* Selects matching configurable dropdown options
* Resolves the selected associated product after every complete option selection

The deep-link selection works independently of the Google Ads event setting.

## Theme compatibility

Luma uses the bundled RequireJS integration. Hyva uses its native configurable-product initialization to read numeric IDs from the generated fragment. Both single-attribute and multiple-attribute selections were verified on the local Hyva acceptance catalog, including sale prices.

The legacy Google Ads event bridge is separate and still uses RequireJS. Successful Hyva preselection does not imply that bridge is active. Customized themes can also replace the containers, selectors, swatch implementation, or script loading.

Test the exact production theme. Backend URL generation alone does not prove the landing page selects the correct child.

## Verify

For both swatch and dropdown configurables:

1. Copy an associated row URL from a generated feed.
2. Open it in a new browser session.
3. Confirm the parent product page loads.
4. Confirm every intended option is selected.
5. Confirm price, image, SKU, availability, and structured data reflect the selected child where the theme supports those updates.
6. Confirm the URL remains valid with the feed's tracking query parameters.
