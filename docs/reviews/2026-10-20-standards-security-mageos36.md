# Standards security fixes and Mage-OS 3.6 verification

> Release disposition, checked October 20, 2026: this work is included in published `v1.2.4`, commit `75dd335f930aef78ae693f62c3039e469c2d26f6`. All 37 [post-merge CI jobs](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/39372608509) passed. See [release notes](../releases/1.2.4.md) for the final verification scope and exact-version PHP 8.1 advisory policy. The report below preserves the original pre-release local snapshot, including its then-uncommitted state and test totals.

Verified locally on October 20, 2026. The changes are uncommitted on `chore/module-standards-compliance`, based on `61094c5`. No push, merge, release, or production deployment was performed.

The supplied findings are addressed, with two corrections to their descriptions below. The additional review found and fixed a silent fallback from an unknown upload mode to unencrypted FTP. One previously documented preset-limit issue remains deferred because fixing it changes existing feed output.

## Findings and changes

| Area | Result |
| --- | --- |
| MSI source filtering | Use `SourceItemInterface::getSourceCode()` and strict source-code matching. The supplied claim that every concrete MSI source item fatals was inaccurate: the current Mage-OS model inherits array access. Interface-only implementations do reproduce the failure. Unit tests cover that case; an MSI integration test verifies enabled, disabled, and unlinked physical sources. |
| SFTP server identity | A module-scoped transport verifies the destination's pinned SHA256 host-key fingerprint before authentication. Missing, malformed, unreadable, or mismatched keys prevent login. The key is stored per destination and survives Admin save/reopen and cloning. No global Magento transport preference was added. |
| FTP exposure | Retain explicitly selected FTP for compatibility and label it `FTP (unencrypted)`. SFTP remains the default. FTP still sends credentials and feed data without encryption. |
| Unknown upload mode | Reject unknown modes before creating a transport. The previous fallback to FTP was reproduced with a regression test. |
| Password exposure | Loaded and saved upload model data retain ciphertext. The transport explicitly requests decryption when connecting. Masked saves, long passwords, repeated saves, original model data, and cloned destinations are covered. Clone persistence is transactional, and a failed destination copy rolls back the new feed. |
| Log rotation | Stream the locked source into an exclusively created, uniquely named gzip archive. Check byte counts, finalize and flush the archive, then compare its decompressed SHA256 and length with the original before truncating. Archive failure retains the source. |
| Configurable shipping weight | Treat absent minimum-price product IDs as an empty list. The actual failure was a null `in_array()` haystack, rather than a string operation on a null weight. |
| Promotion coupon | Remove redundant escaping and render the conditional CSS class as an escaped attribute. The claimed double encoding does not reproduce with the current Magento escaper, which disables double encoding. A rendered-template test verifies coupon text and prevents HTML interpretation. Restoring the old extra escape preserves the test result, as expected. |
| Small cleanup | Remove the unused singular renderer-factory property and correct the `= !` assignment spacing. |
| Preview authorization | Require `MageOS_ShoppingFeed::generate` on the Test controller and hide both current and retained legacy editor preview buttons without that permission. Encode the legacy popup URL as JSON. |

The SFTP implementation follows phpseclib's [verify-before-login guidance](https://phpseclib.com/docs/3.0/ssh2/connect#host-key-verification). A fingerprint must be confirmed through a trusted channel; scanning a server key alone does not establish trust.

## Review scope and remaining issue

The review covered Admin authorization and request validation, feed/form serialization, child-row ownership, output/log path containment and symlinks, SQL construction, JSON deserialization, frontend/template escaping, upload transport selection and credentials, compression cleanup, scheduling/queue recovery, and locked dependencies. No additional confirmed Critical or High issue was found in those reviewed paths.

The previously documented preset-limit mismatch remains: the preset converter produces `filters_filters_output_limit`, while the product filter reads `filters_output_limit`. A new Google Shopping feed on the installed candidate returned `null` for the read key and the configured description/title limits for the miskeyed key. This preserves the existing decision in the Unreleased changelog to handle the output change separately. Merchants can set explicit **Limit column output** rows in the meantime.

## Local installation

- Mage-OS CLI: `3.6.0 (based on Magento 2.4.9)`.
- Main test runtime: PHP 8.4.26, MySQL 8.4, OpenSearch 3.8.0, standard Magento Admin.
- Instance: `/private/tmp/shopping-feed-standards-mageos36-20261019/store`.
- Admin: `http://127.0.0.1:8176/admin`, user `localadmin`. The generated password is in the private `.admin-password` file beside `store`.
- Dedicated Docker project: `shopping-feed-standards36`; localhost database port 33086 and search port 9203. Integration tests use a separate database and search prefix.
- The installed module is a copy of this candidate without its development `vendor` tree. The final runtime-file hash manifest is in the evidence directory.
- The existing `/Users/matt/code/mageos-latest` installation and its catalog were used only as a source for the isolated platform files. Its configuration and database were not changed.

Fresh installation, dependency injection compilation, and `setup:db:status` passed. The Admin grid and editor rendered, a feed was created and reopened, its fingerprint persisted, and its password stayed masked. A subsequent masked save succeeded.

Two disposable products with MSI stock generated two feed rows. The complete generated file was uploaded to a private localhost SFTP fixture with gzip and matched the remote decompressed payload. The temporary gzip disappeared. A pre-existing log larger than the rotation threshold was preserved byte-for-byte in its archive.

## Verification

| Check | Result |
| --- | --- |
| Full unit suite, PHP 8.4 | 939 tests, 2565 assertions passed, with Clover coverage collected |
| Full unit suite, PHP 8.5 | 939 tests, 2565 assertions passed |
| Mage-OS integration suite | 30 tests, 88 assertions passed |
| Real database persistence suite | 4 tests, 18 assertions passed using connection-local temporary tables |
| JavaScript and actual Magento UI form tests | 66 passed |
| Magento coding standard | Zero errors or warnings |
| PHPStan level 8 | Passed with the existing reviewed baseline; reduced from 1716 entries/2405 errors to 1715 entries/2404 errors |
| Baseline growth check | Passed against `HEAD` |
| PHP syntax | All 543 PHP/PHTML files passed individually on PHP 8.1 and 8.5 |
| Composer metadata | Strict validation passed |
| Module and development-tool dependency audit | Zero advisories; four abandoned upstream dependencies reported |
| Installed platform dependency audit | Zero advisories; eight abandoned dependencies reported |
| Module and Magento XML validation | 26 XML files passed; eight feed types validated |
| Wiki validation | 42 pages and 42 sidebar targets passed |
| UI component naming contracts | Three layout-referenced components passed |
| Flysystem traversal regression | 12 checks passed |
| Real transfer fixtures | SFTP and FTP, plain and gzip payloads matched |
| SFTP rejection | Missing and mismatched pins caused no authentication attempt |
| End-to-end generation | Two rows, zero skips; local/remote equality, gzip cleanup, and complete log archive verified |

The first unit run had 930 passing tests. Added or strengthened tests were observed failing for the MSI interface violation, null shipping list, plaintext model data, preview ACL, and FTP downgrade. Targeted reversions also confirmed detection of a mismatched host key and source-log truncation on archive failure. Clover records execution of the actual upload model, source-filtering method, SFTP verification method, and log rotator. The real transfer fixture exercises client creation and protocol I/O separately from unit mocks.

These results cover the local Mage-OS 3.6 candidate. Other supported Magento/Mage-OS versions, native Nebula, real merchant destinations, and production volume acceptance were not run in this task.

## Evidence and operation

Evidence and the local instance instructions are under `/private/tmp/shopping-feed-standards-mageos36-20261019`. The `evidence` directory contains test logs, coverage, dependency audit JSON, regression/reversion results, a saved Admin screenshot, generated-file checksums, and the final candidate manifest. The local `LOCAL-INSTANCE.md` documents starting and stopping the isolated services.

The installation is available for local review. Existing SFTP destinations require a trusted fingerprint after the normal `setup:upgrade` step. No external recipient was contacted.

## Attribution follow-up

The package credits now use [Rocket Web](https://rocketweb.com/). Nineteen imported source headers explicitly state OSL-3.0 while retaining their original copyright owners and years. Migration and provenance documentation refer to existing modules without suggesting separate commercial terms. Third-party material and the canonical `LICENSE.txt` are unchanged.

This follow-up changes source comments, attribution metadata, and documentation. All four affected PHP files have identical executable tokens to their pre-cleanup copies, and all fifteen affected XML/XSD documents are identical with comments excluded. Targeted PHPCS, consolidation validation, Magento XML validation, Wiki validation, strict Composer validation, and whitespace checks passed. The installed local copy and its file-hash evidence were refreshed after the cleanup.
