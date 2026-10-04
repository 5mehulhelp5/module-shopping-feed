# Release 1.2.1

> Documentation baseline: release 1.2.1 (`v1.2.1`). Last reviewed: 2026-10-04.

Version 1.2.1 addresses [#9](https://github.com/mage-os-lab/module-shopping-feed/issues/9), [#10](https://github.com/mage-os-lab/module-shopping-feed/issues/10), and [#11](https://github.com/mage-os-lab/module-shopping-feed/issues/11), including the description-cleaning follow-up.

- Required configurable-child option pricing preserves free values, calculates percentages against the child's base price through Magento, and honors explicit defaults regardless of value order.
- Backorder availability honors inherited settings and salable configurable children. In-stock MSI sources take precedence over backordered sources. Local Inventory retains its quantity-based rules.
- Page Builder description cleaning removes raw and escaped markup and style/script content, decodes nested entities, and applies text limits after cleaning.

The release also introduces discovery for the separately installed [Rocket Web migration companion](Rocket-Web-Migration). Core upgrades do not install it or run migrations. The companion has its own 1.0.0 release and installation process.

The 32 new regression cases produced 22 failures before the runtime fixes and passed afterward. The full suite passed 846 tests on Mage-OS 3.5/PHPUnit 12 and Magento 2.4.8/PHPUnit 10; 47 JavaScript checks also passed. Current CI is linked from the repository badge. Existing-store and recipient acceptance remain staging checks. See [Installation and upgrade](Installation-and-Upgrade#upgrading-from-120-to-121-candidate) for staging checks.
