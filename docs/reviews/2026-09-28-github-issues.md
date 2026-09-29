# GitHub issues #3 through #7, 2026-09-28

The five open reports were checked against the 1.1 release candidate at `35f434f`. All five described remaining defects. Generic comma serialization already addressed part of #6, but the declared enclosure and empty-value settings were still ignored. This follow-up corrects the reported behavior and adds regression coverage.

## Corrections

| Issue | Correction | Evidence |
| --- | --- | --- |
| [#3](https://github.com/mage-os-lab/module-shopping-feed/issues/3) | Local Inventory configurable children use parent salability under default stock, then check their own stock. Custom availability attributes and disabled parent inheritance retain their behavior. | In-stock children export quantity 25 and `in_stock` while the parent's legacy quantity is zero, with MSI enabled and with all 74 Inventory modules disabled. |
| [#4](https://github.com/mage-os-lab/module-shopping-feed/issues/4) | Guest and all-group tiers available at quantity one participate in sale detection and price calculation. The Tier Price directive excludes bulk-only tiers, uses the website-adjusted value, and does not change the product's customer-group context. | Six generated-price cases match the guest price index and rendered guest product pages. Expired special dates do not constrain an active tier discount; a lower active special price retains its dates. |
| [#5](https://github.com/mage-os-lab/module-shopping-feed/issues/5) | Complex Product Context Prioritization includes Search-only children. | A Search-visible child created before the configurable parent and another created after it both export once with the same parent SKU in `item_group_id`. |
| [#6](https://github.com/mage-os-lab/module-shopping-feed/issues/6) | The writer reads `output_params_enclose_cell`, `output_params_enclose_escape`, and `output_params_default_value`. Generic Admin controls expose them. Enclosed fields preserve embedded delimiters; comma output keeps double-quote defaults when the controls are blank. | Generated comma, pipe, and single-quote-enclosed output parses with exact punctuation, Unicode, column order, empty defaults, and zero values. Browser-saved settings survive reload and control the downloaded file. |
| [#7](https://github.com/mage-os-lab/module-shopping-feed/issues/7) | Notices allow safe anchors, bold text, and line breaks. Internal links use preserved fragment URLs. Tab lookup accepts IDs on either the anchor or tab container. Reported typos are corrected. | All seven affected tabs render without literal HTML. Four notice links switch to the expected tab on Mage-OS's default Admin theme. Unsafe markup is removed by the real Magento Escaper in a unit test. |

The initial focused PHP run had 11 failures across 20 tests before the fixes. A JavaScript regression reproduced the sanitized-link failure. Browser verification then exposed the default Mage-OS Admin theme's repeated tab ID: the lookup returned the tab container and calculated index `-1`. That case failed a separate regression before changing the lookup to use the closest tab container.

## Verification

| Check | Result |
| --- | --- |
| PHPUnit 12.5.36 | 433 tests, 1,011 assertions passed |
| PHPUnit 9.6.37 and 10.5.65 | 433 tests, 846 assertions passed on each runner |
| Application integration | 10 tests, 26 assertions passed inside a rollback transaction |
| JavaScript | 19 tests passed |
| Generated feeds and stock modes | 42 checks passed across Google, Generic, MSI Local Inventory, and legacy Local Inventory |
| Browser-saved custom feed | Four database/output checks passed; HTTP 200 download parsed strictly as nine rows and seven columns |
| Guest storefront prices | Six product pages returned HTTP 200 and rendered the expected final price |
| Admin browser | All seven notice tabs, four navigation links, safe formatting, output controls, save/reload, and zero recorded JavaScript errors |
| Application setup | Fresh core installation, module enable/upgrade, indexing, DI compilation, Admin static-content deployment, and cache flush passed |
| PHP/PHTML syntax | 470 files passed |
| XML contracts | 24 files passed |
| Magento coding standard | Zero errors, 3,218 warnings under the repository's warning-tolerant exit policy |

The application used Mage-OS 3.5.0 with PHP 8.4.24, MySQL 8.4, OpenSearch 2.19.6, and the `MageOS/m137-admin-theme` Admin theme. No Nebula packages were present. A fresh database and localhost-only containers were used with the dependency tree retained from the preceding disposable installation. All products, accounts, feeds, and configuration changes were synthetic. Existing stores were not changed.

The original broad [Google/custom-feed acceptance](2026-09-28-google-custom-feed-fixes.md), [Nebula acceptance](2026-09-25-admin-compatibility.md), and [installation without Nebula](2026-09-28-without-nebula-acceptance.md) remain separate historical records. This follow-up did not repeat the entire Nebula, Hyva, transport, or catalog matrix. It does not establish Merchant Center approval or a live recipient upload.

Private fixture scripts, generated files, screenshots, logs, and the candidate manifest are retained under `/private/tmp/shopping-feed-issues-20260928`. The reproducible regression tests are committed with the fixes. Use the PR's CI results for the exact pushed commit; local checks do not replace that verification.

## Upgrade review

Run the normal 1.1 schema upgrade. These five fixes add no further schema changes and do not rewrite saved column maps. Review existing enclosure, escape, and empty-value settings because previously ignored values now take effect. Check recipients that used bulk-only Tier Price values, and compare guest prices with the selected store view. Enable Complex Product Context Prioritization when individually visible children must retain their parent's grouping.

The README, changelog, release notes, and relevant operator guides were updated. The public GitHub Wiki remains a separate publication step after the release; its source is maintained in `docs/wiki`.
