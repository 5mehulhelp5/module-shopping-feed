# Status and compatibility

Mage-OS Shopping Feed is a new module identity prepared for Mage-OS Lab. Existing Rocket Web installations are not upgraded or migrated automatically.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

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

Compatibility in CI is not a production acceptance result. Test the exact module commit against a representative store, catalog, inventory setup, and external destination before enabling production schedules or uploads.

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

Composer installation depends on the package being available through a repository configured in the target Magento project. Until that distribution path is confirmed for the intended release, use the source installation method described in [Installation and upgrade](Installation-and-Upgrade).

Do not infer release availability from the presence of source code alone. Check the repository's releases and the configured Composer repository at the point of installation.

## Storefront assumptions

Simple-product custom-option deep links use RequireJS on Luma. On Hyvä, a [`hyva_` layout handle](https://docs.hyva.io/hyva-themes/writing-code/layout-and-templates/the-hyva_-layout-handles.html) selects a native JavaScript template that waits for Alpine initialization and dispatches option change events. Dropdown, multiselect, radio, and checkbox options use the existing `#optionId=valueId` URL format. This integration requires [`hyva.alpineInitialized`](https://docs.hyva.io/hyva-themes/writing-code/the-window-hyva-object.html#hyvaalpineinitializedcallback), available since Hyvä 1.2.8 and 1.3.4, and registers the inline script with Hyvä CSP when that helper is available.

This addresses a Hyvä compatibility issue; it is not evidence of a Mage-OS 3.5 regression. Configurable-product selection and Google Ads events still use the existing RequireJS integration and need separate theme compatibility verification.

Run the focused frontend checks with `node --test dev/tests/frontend/*.test.cjs` (Node.js 22+ and PHP with SimpleXML). Validate the exact product page against the storefront theme in use. A passing backend feed generation test does not prove that microdata, configurable deep links, or Google Ads events work in a customized theme.
