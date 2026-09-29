# Changelog

All notable changes to this project will be documented here.

## Unreleased

### Added for 1.2

- OpenAI / ChatGPT (Google-compatible, beta) TSV preset with required brand, explicit identifier exemptions, GTIN checksums, availability dates, configurable grouping, and sale validation. Includes the mobile-subscription zero-price exception, short expiration metadata, and onboarding guidance. OpenAI ingestion acceptance remains unverified.

- Pinterest Catalog preset with quoted UTF-8 TSV, native availability values, configurable grouping checks, five-level product category paths, and field validation. Includes primary and supplemental feed setup guidance. Pinterest import acceptance remains a release check.
- TikTok Catalog preset with quoted UTF-8 CSV, required `sku_id`, native availability values, configurable variants, comma-separated additional images, and field validation. TikTok import acceptance remains a release check.
- Microsoft Merchant Center preset with UTF-8 tab-delimited TXT, native availability and sale-price formatting, configurable variants, identifier mappings, and field validation. The required ID is emitted last to avoid trailing tabs. Microsoft import acceptance remains a release check.
- Meta Catalog (Facebook and Instagram) template with quoted UTF-8 TSV, currency prices, configurable variants, Google taxonomy, and editable identifier mappings.
- A reusable value-map formatter with Meta product-feed availability defaults. Backorders and preorders export as `out of stock` until available.
- Meta required-field (including brand), condition, availability, price-format, and URL validation in generation and Test Feed. Invalid rows are skipped with reasons; missing identifiers produce warnings.
- Meta setup guidance in the Admin and a catalog feed guide. Commerce Manager acceptance remains a release check.

## 1.1.0 - 2026-09-28

### Added

- Optional native Nebula Admin feed grid, with the existing standard Admin editor, preview, and logs retained. Standard installations require no Nebula package.
- Fresh Mage-OS 3.5.0 installation and browser acceptance without Nebula, plus Google and custom-feed regression coverage.

### Fixed

- Stopped inferring `identifier_exists=FALSE` from incomplete catalog identifiers; absence now requires explicit confirmation and no supplied brand, GTIN, or MPN.
- Replaced new Google feeds' default SKU-to-MPN mapping with empty MPN and GTIN attribute mappings. Existing saved mappings are preserved.
- Added an `availability_date` placeholder to new Google feeds and skip backorder/preorder rows with missing, invalid, expired, or more-than-one-year-ahead dates, with a log warning and skipped count.
- Prevented online backorders from overriding Local Inventory quantities and respected disabled source items.
- Serialized Generic comma-delimited feeds as quoted CSV, preserving embedded commas and escaping double quotes.
- Fixed Local Inventory configurable-child availability when the parent has zero legacy quantity. Parent inheritance uses salability under default stock (#3).
- Included guest and all-group single-unit tier discounts in sale detection and price calculation, without applying bulk-only tiers. The Tier Price directive respects quantity one and preserves the product's customer-group context (#4).
- Included Search-only simple products in Complex Product Context Prioritization so their grouping does not depend on product ID order (#5).
- Read the declared enclosure, escape, and empty-value configuration keys. Added Generic Admin controls and preserved embedded delimiters when enclosure is configured (#6).
- Rendered safe tab-notice links and formatting, restored tab navigation after sanitization, and corrected notice typos (#7).

- Preserved encrypted upload credentials through masked and repeated saves, rejected unreadable credentials, and expanded ciphertext storage from `varchar(255)` to `text`.
- Scoped saved upload and schedule IDs to the current feed.
- Recovered interrupted queue rows on the next worker run, with lock rechecks and restart from the beginning to avoid duplicated partial output.
- Included multiple selected promotion rules correctly and fixed the Admin promotion counter's initialization.
- Counted UTF-8 characters without splitting multibyte text when applying column limits.
- Required an explicitly selected microdata feed and preserved native price metadata when no eligible feed is available.
- Treated storefront option values as literal values, with safe handling of malformed URL fragments.
- Escaped preview values without translating product data and kept generation traces out of the Admin response.
- Added the full-text feed-name index for standard-grid keyword search and corrected category-map form initialization.
- Corrected optional Nebula grid filtering, pagination, exports, ACL-controlled actions, and standard-editor routing.
- Made regression data providers and mock responses compatible with PHPUnit 9, 10, and 12.

### Upgrade

- Run `bin/magento setup:upgrade` for the password column and feed-name index.
- Review existing identifier mappings and add real availability dates for Google backorders/preorders. Saved column maps are preserved.
- Check custom recipients before resuming uploads. Comma output uses CSV quoting by default; saved enclosure, escape, and empty-value settings now take effect. Review feeds using Tier Price for previous bulk-only values.

### Documentation

- Added 1.1.0 release notes, wiki navigation, and a complete upgrade checklist.
- Documented identifier confirmation, real availability dates, local inventory status, CSV parsing, and the settings to review when upgrading existing feeds.

## 1.0.0 - 2026-09-09

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

- Initialized simple-product custom options on Hyva without RequireJS, including option price updates
- Included numeric attribute IDs in configurable swatch URLs so Hyva selects the advertised variant while retaining legacy attribute codes
- Used parent salability instead of parent quantity when inheriting configurable stock status
- Restricted request-selected microdata to enabled children of the current configurable product on the current website
- Isolated queue queries so an already queued feed does not prevent other due feeds from being scheduled
- Sanitized encoded tabs and line breaks after HTML entity decoding to preserve feed column alignment
- Stopped FTP/SFTP validation and upload when the configured remote directory cannot be entered
- Preserved nonstandard ports in simple and grouped product URLs
- Preserved JSON-looking scalar settings and structured arrays as distinct types, including malformed legacy text
- Normalized empty column defaults before sanitization so saving feeds does not emit PHP 8.1+ deprecation notices
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
- Skipped shipping mapping when `shipping_country` is not an array instead of passing invalid configuration to `array_filter()`

### Migration

- No automatic migration from the Rocket Web packages is performed
- Existing package installations and data remain untouched

### Changed

- Aligned Composer metadata, source notices, and the bundled license on OSL-3.0
- Split Magento Open Source and Mage-OS CI matrices so each distribution installs from its official Composer repository
- Updated the reusable Magento extension checks to `graycoreio/github-actions-magento2` 8.9.0
