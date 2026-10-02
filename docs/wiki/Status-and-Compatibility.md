# Status and compatibility

Mage-OS Shopping Feed 1.1.0 is the current stable release. See [Release 1.1.0](Release-1-1-0) for changes and recorded acceptance. Existing Rocket Web installations are not upgraded or migrated automatically.

> Documentation baseline: release `v1.1.0`, with explicitly marked unreleased `9f07e46` editor notes. Last reviewed: 2026-10-02.

The unreleased [UI Component editor candidate](Admin-UI-Component-Forms) keeps the Composer requirements. Magento Open Source 2.4.8 and 2.4.9 passed the recorded production-mode Docker browser/output tests, 16 integration tests, four database tests, and six-role permission checks. The candidate is now deployed and tested on Mage-OS 3.5.0 at `mageos-latest`, with two further preview fixes. The final unit suite passes 801 tests on each framework. Those two follow-ups are deployed but uncommitted; this is not a release. See `docs/reviews/2026-10-02-mageos-latest-deployment-acceptance.md` in the repository for current evidence. Native Nebula bridge rendering remains unverified.

## Package identity

| Surface | Value |
| --- | --- |
| Composer package | `mage-os/module-shopping-feed` |
| Magento module | `MageOS_ShoppingFeed` |
| PHP namespace | `MageOS\ShoppingFeed` |
| License | OSL-3.0 |
| Configuration section | `mageos_shopping_feed` |
| Admin route | `mageos_shopping_feed` |
| Cron group | `mageos_shopping_feed` |
| CLI prefix | `mage-os:shopping-feed` |

## Platform requirements

The package currently declares:

* PHP 8.1 through PHP 8.5
* `magento/framework` 103.0.6-p15 or later in the 103.x series
* A PHP version supported by the selected Mage-OS or Magento Open Source release
* Magento cron for scheduled queue creation and processing
* Magento Inventory APIs for source-level Local Inventory output

Mage-OS 3.4.0 is an explicit CI target. CI also installs the module into supported Magento Open Source projects, runs unit and integration tests, checks Magento coding standards, and compiles dependency injection.

Mage-OS 3.5.0 on PHP 8.4.24 was verified locally on Magebox with Hyva, including full-store Google Shopping generation, price and stock comparisons, and configurable deep links. See [Release 1.0.0](Release-1-0-0) for the exact scope and limits. Mage-OS 3.5.0 is a local acceptance profile, not an additional CI matrix entry.

Compatibility in CI is not a production acceptance result. Test the exact module commit against a representative store, catalog, inventory setup, and external destination before enabling production schedules or uploads.

## Admin theme compatibility

Released version 1.1.0 retains Magento's standard Admin grid and provides an optional native grid for Nebula Admin. Nebula installations use its native filtering, sorting, selection, and pagination controls. Feed editing, product previews, and logs open in Magento's standard Admin layout, with the existing configuration tabs and widgets. Back and Save return to the Nebula list. This is a native grid integration with the standard editor, not a replacement editor built with Nebula forms.

The integration activates automatically when the Nebula modules and theme are active. It adds no required Nebula package and does not change unrelated Admin screens. Mass actions retain the module's existing POST, form-key, and ACL checks. The grid excludes feed configuration and upload credentials.

The tested local combination and remaining limits are recorded in the [Admin compatibility report](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-25-admin-compatibility.md). Storefront Hyva support is a separate feature.

A separate fresh Mage-OS 3.5.0 installation with no Nebula packages passed Composer installation, schema updates, DI compilation, production-mode generation, and standard Admin browser checks. This covered both the default Mage-OS Admin grid and Magento's classic Admin theme. See the [installation without Nebula report](https://github.com/mage-os-lab/module-shopping-feed/blob/v1.1.0/docs/reviews/2026-09-28-without-nebula-acceptance.md) for the exact scope and the additional keyword-search and editor-initialization fixes.

## Feed support

| Capability | Status |
| --- | --- |
| Generic feeds | Included |
| Google Shopping | Included |
| Google Local Inventory | Included |
| Multi-Source Inventory | Optional, required for source-level rows |
| Google Promotions | Included as part of a Google Shopping feed |
| FTP and SFTP upload | Included |
| Gzip upload | Included |
| Automatic migration from Rocket Web packages | Not included |

## Distribution status

The package is listed on [Packagist](https://packagist.org/packages/mage-os/module-shopping-feed). Install the stable 1.1 line with `composer require 'mage-os/module-shopping-feed:^1.1'`, or use the tagged source installation described in [Installation and upgrade](Installation-and-Upgrade).

Do not infer release availability from the presence of source code alone. Check the repository's releases and the configured Composer repository at the point of installation.

## Storefront assumptions

Simple-product custom-option deep links use RequireJS on Luma. On Hyvä, a [`hyva_` layout handle](https://docs.hyva.io/hyva-themes/writing-code/layout-and-templates/the-hyva_-layout-handles.html) selects a native JavaScript template that waits for Alpine initialization and dispatches option change events. Dropdown, multiselect, radio, and checkbox options use the existing `#optionId=valueId` URL format. This integration requires [`hyva.alpineInitialized`](https://docs.hyva.io/hyva-themes/writing-code/the-window-hyva-object.html#hyvaalpineinitializedcallback), available since Hyvä 1.2.8 and 1.3.4, and registers the inline script with Hyvä CSP when that helper is available.

Configurable deep links include numeric attribute IDs for Hyva's native selection handling and retain attribute codes for legacy swatch renderers. All 38 available variants in the local acceptance feed selected correctly with matching prices. See [Configurable product deep links](Configurable-Product-Deep-Links).

These are Hyva compatibility corrections, not confirmed Mage-OS 3.5 regressions. The Google Ads event bridge still uses RequireJS and needs a separate Hyva integration; native variant selection does not imply Google Ads event delivery.

Run the focused frontend checks with `node --test dev/tests/frontend/*.test.cjs` (Node.js 22+ and PHP with SimpleXML). Validate the exact product page against the storefront theme in use. A passing backend feed generation test does not prove that microdata, configurable deep links, or Google Ads events work in a customized theme.
