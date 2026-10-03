# Stock defects found during local acceptance

The extended acceptance run found and repaired three generator defects after the UI Component editor checks. These are inventory and product-selection fixes; they do not change the form architecture or enable Nebula.

## Defects and corrections

| Defect | Reproduction | Correction |
| --- | --- | --- |
| Storefront stock visibility overrides feed settings | With Magento hiding out-of-stock products, a configurable child is absent even when the feed permits it. The same native filter affects simple, grouped, and bundle collections. | Feed collections apply the feed's stock settings independently. Grouped products use a native link collection created before Magento's storefront ProductLinks filter can run. |
| Configurable Local Inventory parent rows disappear on custom stock | Two enabled sources contain the children, but the parent SKU has no source item linked to that stock. Child-only output works; parent-only output is empty. | Parent rows use the distinct linked sources of their children. Their existing mappers calculate quantity and availability for each source. |
| Configurable parent availability ignores source-item status | A child has positive quantity but its source item is explicitly out of stock. The parent is incorrectly reported in stock at that source. | Parent availability requires an enabled child source item at the current source as well as positive reservation-adjusted quantity. |

The first configurable regression failed before the fix, with `simple_20` missing from the generated rows. The Local Inventory unit regressions also failed before their respective fixes: a parent row was missing, and a disabled source item produced `in_stock`. Runtime SQL inspection separately established the early grouped-product filter.

## Verification

The retained Magento Open Source 2.4.8 and 2.4.9 Docker stores use PHP 8.4.26 and production mode. Native product APIs created configurable, grouped, fixed-price bundle, dynamic-price bundle, downloadable, sale, disabled, backorder, and unmanaged-stock fixtures. A separate attribute set contains 5,000 synthetic products for volume checks. These stores contain no serving upload destinations.

Completed checks for the fixes:

- 26 semantic product scenarios on each platform, including all three complex-product modes and feed-controlled stock exclusion.
- 15 native MSI scenarios per platform: all three configurable modes across baseline, reservation, compensation, source-item disablement, and restoration. Magento's stock APIs and indexers establish the stock; the reservation APIs append and compensate the reservation. No checkout order is claimed.
- Regular, special, single-unit tier, tax-inclusive, tax-exclusive, and USD/EUR prices on both platforms. A ten-unit tier does not replace the single-unit offer. The generator restores the original store currency after conversion.
- 805 unit tests on Magento 2.4.8, Magento 2.4.9, and the Mage-OS 3.5.0 framework from `mageos-latest`.
- 18 official Magento integration tests and 40 assertions on each Docker platform, including configurable, simple, and grouped stock visibility.
- Production dependency compilation on both Docker platforms. Generated code was built separately and then activated with the previous generated code retained.
- Repository validation and Magento coding standards: no coding-standard errors. The configured gate ignores warnings; the full scan still reports 3,588 warnings across 404 files.

This report records the fixes and their focused verification. The final local acceptance record must also establish the deployed commit, cron completion, rollback results, and fixture cleanup. It is not an external recipient or release approval.

## Extension compatibility

Stock settings, source mappings, and the database schema retain their existing formats. Both supported Magento versions resolve the new grouped collection dependency through normal dependency injection.

Custom PHP subclasses that override the `Composite` or `Configurable` adapter constructor must forward the added `linkedProductCollectionFactory` argument before `data`. Recompile dependency injection after deploying these fixes in production mode. Existing constructor overrides should be reviewed as part of a downstream upgrade.

## Local evidence

Private evidence is retained under `/private/tmp/shopping-feed-magento-docker-20261002/{248,249}/evidence/acceptance/`. It includes failing and passing regression logs, generated rows, native stock-index observations, reservation results, pricing results, compile logs, and full database backups. Do not publish database backups or generated test credentials with this report.
