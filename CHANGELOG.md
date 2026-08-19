# Changelog

All notable changes to this project will be documented here.

## Unreleased

### Added

- New `MageOS_ShoppingFeed` module and `mage-os/module-shopping-feed` package identity
- Consolidated Generic, Google Shopping, Google Local Inventory, and Google Promotions feed support
- Isolated database, configuration, route, cron, CLI, event, layout, UI, JavaScript, log, and output identifiers
- Isolated dependency-injection array keys and application cache identifiers
- Isolated feed output, promotion cache, process lock, and log paths
- Isolated Admin session keys used when restoring failed form submissions
- Declarative schema whitelist regenerated from the consolidated database schema
- Repository validation and CI checks for identity isolation, merged feed definitions, XML, Composer metadata, and PHP syntax
- Portable Magento test bootstrap and an expanded regression suite
- Integration coverage for module dependency wiring and feed queue database contracts
- CI installation, unit, integration, coding-standard, and dependency-injection compilation checks across supported Magento Open Source and Mage-OS releases
- An explicit Mage-OS 3.4.0 compatibility matrix while the upstream matrix provider still reports an older Mage-OS release
- Private vulnerability reporting policy
- Explicit MSI source-code to Google store-code mapping for Local Inventory
- Streaming gzip generation for FTP and SFTP uploads
- Current Google Ads `view_item` events for selected configurable variants
- Production acceptance plan covering store, feed, MSI, delivery, recovery, and release checks

### Fixed

- Removed the duplicated Google Shopping `shipping_weight` default column
- Added the required encoding to the Local Inventory feed definition
- Replaced legacy DoubleClick remarketing pixels and globals with Google tag events
- Made configurable deep links work independently of the dynamic remarketing setting
- Added support for configurable dropdown deep links as well as swatches
- Updated Promotions enums and repeated destinations for Shopping ads and free listings
- Updated schema.org offer URLs to HTTPS
- Corrected cached uploader reuse so each FTP or SFTP destination uses its own credentials
- Applied the saved gzip setting to product and Promotions uploads with cleanup after transfer
- Fixed post-upload event data so Promotions uploads receive the destination object instead of a Boolean result
- Removed the original paid-module product skip attribute from the new module's runtime behavior
- Excluded historical data patches that could inspect or move legacy-package data and log files
- Fixed PHP 8 failures in filter sorting, empty delimiter handling, generator state restoration, and additional image mapping
- Hardened price formatting against nonnumeric strings and restored label mapping for array-valued select attributes
- Passed modern configurable-product dependencies explicitly to avoid runtime service-locator fallbacks
- Replaced the service-locator serializer wrapper with Magento's serializer interface
- Made stock handling safe when no legacy stock item is returned and avoided duplicate stock-status reads
- Made empty-column replacement and option concatenation safe for incomplete configuration data
- Corrected log-handler replacement so stale handlers are not retained
- Restricted feed, Promotions, and log output to approved Magento directories and safe file extensions
- Added explicit Admin ACL resources and POST-only contracts for feed mutations
- Protected grid AJAX and export data sources with the feed-view ACL
- Made manual and scheduled queue creation share the queue model's persistence invariants
- Made required feed and queue database fields safe for new manual queue entries
- Secured Google taxonomy downloads with HTTPS, locale validation, response-status checks, and deterministic connection cleanup
- Guaranteed cron generation locks are released and closed after all PHP failures
- Made the generation CLI return a failure exit code when queue processing fails
- Made both CLI commands compatible with Symfony Console 7 return-type contracts
- Made unit and integration tests compatible with PHPUnit 9 through 12
- Made Magento XML validation safe when the module is already installed in the validation checkout
- Made the shared Promotions cache hash-aware and atomically replaceable across concurrent feed processes
- Removed PHP 8.2 dynamic-property deprecations from the legacy unit-test fixtures
- Made catalog-rule sale dates use Magento's configured store timezone instead of the server timezone
- Made edited schedules eligible to run again on the same store day
- Preserved associated-product inventory context while generating source-specific Local Inventory rows
- Removed Local Inventory's production test-mode workaround and skipped duplicate rechecks only during source remapping
- Removed PHPUnit 12 mock notices from the unit suite
- Corrected the Google Shopping promotion header from `promotions_id` to `promotion_id`
- Added current Google item-group titles, variant options, and standard variant attributes to Shopping feeds
- Kept Google rows aligned with their headers without trailing tabs
- Preserved Google sale-price column names while writing feed headers
- Omitted Google sale prices and effective dates when the rendered sale price is not lower than the regular price

### Migration

- No automatic migration from the Rocket Web packages is performed
- Existing package installations and data remain untouched

### Changed

- Aligned Composer metadata, source notices, and the bundled license on OSL-3.0
- Split Magento Open Source and Mage-OS CI matrices so each distribution installs from its official Composer repository
- Updated the reusable Magento extension checks to `graycoreio/github-actions-magento2` 8.9.0
