# Automatic updates and schema.org

The module can add schema.org offer data to product pages using the same feed mapping used for Google Shopping. Google can use structured product data to reconcile selected price and availability differences.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## What the module renders

When enabled and valid mapped data is available, the product page includes:

* Product name
* SKU
* Offer price
* Price currency
* Availability
* Item condition, when available

The module uses HTTPS schema.org values such as `https://schema.org/InStock` and `https://schema.org/OutOfStock`.

It also disables Magento's default price microdata in the affected renderer to avoid conflicting offer markup.

## Enable the feature

1. Open **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed**.
2. Set **Enable Automatic Updates (Microdata)** to **Yes** for the intended scope.
3. Open the Google Shopping feed for that store.
4. Set **Use for microdata** to **Yes**.
5. Save and clear all applicable Magento, full-page, reverse-proxy, and CDN caches.

Only one feed per store can be marked for microdata. If another feed already owns that role, the module refuses the second selection and shows a warning.

## Mapping behavior

The module looks for mapped `title`, `price`, `sale_price`, `availability`, `condition`, SKU, and currency values. It uses a lower sale price when present; otherwise it uses regular price.

If title, price, or availability is missing, the module does not render its offer block. Fix the feed mapping first.

## Configurable products

Associated configurable URLs can include an `aid` query parameter when microdata is enabled. That identifies the selected child for server-rendered offer data, while the URL fragment selects the visible swatch or dropdown options in the browser.

## Verify

1. Open a fresh, uncached product page.
2. Inspect the rendered HTML, not only the visible page.
3. Confirm name, SKU, price, currency, availability, and condition match the feed.
4. Test a configurable parent and selected child URL.
5. Confirm the theme has not added a conflicting offer block.
6. Use Google's current structured-data and Merchant Center diagnostics.

Google controls whether and how automatic updates are applied. See [Allow Merchant Center to update product information automatically](https://support.google.com/merchants/answer/12157888?hl=en).
