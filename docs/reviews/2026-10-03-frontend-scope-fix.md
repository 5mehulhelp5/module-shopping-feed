# Frontend store-scope acceptance correction

The extended local acceptance run found that microdata and Google Ads settings ignored website and store-view overrides. The Admin exposes these settings at all three scopes, but the storefront blocks and native-price-metadata plugin read only the global configuration.

The correction reads the current store scope for microdata enablement, dynamic remarketing enablement, and the optional Google Ads destination. Magento applies normal website and global inheritance. No saved configuration, feed mapping, constructor, schema, or JavaScript contract changes.

## Reproduction and verification

On Magento Open Source 2.4.8, an isolated transaction set global microdata to zero and the current store to one. Magento's explicit store lookup returned one, while the original block returned false. The dynamic remarketing block likewise ignored its store override and destination. The transaction was rolled back.

Two official integration tests failed before the correction: a store could neither enable a globally disabled feature nor disable a globally enabled feature. Both now pass on Magento 2.4.8 and 2.4.9, including native price-schema suppression and destination selection.

The completed verification includes:

- 805 unit tests on Magento 2.4.8, Magento 2.4.9, and the Mage-OS 3.5.0 framework. The assertion counts are 1,875 on Magento 2.4.8 and 2,150 on Magento 2.4.9 and Mage-OS.
- 20 official integration tests with 48 assertions on each Docker platform, including the two new scope regressions.
- Nine native frontend rendering cases per platform. The sequence switches from the enabled store to a disabled store and back. Simple-product metadata and an eligible configurable child contain the expected SKU, USD price, availability, and condition; an unrelated child ID uses the parent's metadata. Enabled cases render one module offer and one module SKU. Disabled cases render neither and preserve native price-schema handling. Tag configuration follows the selected store.
- Coding standards for the changed PHP files: zero errors and 32 warnings under the repository's configured gate. Warnings are not treated as errors by that configuration.

The rendering probes create temporary stores and feed selections inside transactions and roll them back. They do not establish full-page-cache, Varnish, consent-manager, Google delivery, or Merchant Center acceptance. The existing dropdown deep-link checks remain separate browser evidence.

## Operational effect

After upgrading, existing website and store-view overrides take effect. Review intentional overrides before enabling storefront features, and clear applicable configuration, full-page, reverse-proxy, and CDN caches after deployment. The correction does not enable a feature or add a Google tag on its own.

Local evidence is retained under `/private/tmp/shopping-feed-magento-docker-20261002/{248,249}/evidence/acceptance/`, including the failing and passing integration logs and `frontend-render-results.json`. Deployment and final cleanup belong to the consolidated local acceptance record.
