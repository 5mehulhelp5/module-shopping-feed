# Magento Open Source 2.4.7-p10

This profile uses PHP 8.3 and Magento's standard Admin. It does not require a different Shopping Feed editor or a change to the module's Composer requirements. See the [acceptance record](../reviews/2026-10-03-magento-247-acceptance.md) for the exact tested scope.

Local acceptance passes 809 unit tests, 21 native integration tests, production compilation, all eight preset save/output comparisons, category and promotion editing, DynamicRows, preview recovery, and six Admin role profiles. The compatibility correction adds PHPUnit 9 data-provider annotations alongside the existing attributes; it does not change application runtime code. Extended catalog, transfer, and recurring-cron results from other platform profiles remain separate evidence.

## Dependency blocker

As checked on October 3, 2026, Magento 2.4.7-p10 requires `league/flysystem ^2.4`. Its available 2.x releases are affected by `PKSA-w9tt-7782-78jx`, also identified as [GHSA-cxf4-7mrp-vvpr / CVE-2026-102601](https://github.com/thephpleague/flysystem/security/advisories/GHSA-cxf4-7mrp-vvpr). Composer 2.9.8 blocks dependency resolution before it reaches the extension's tests. The published fix is in Flysystem 3.35.3; forcing that major version into Magento's 2.x constraint is not a supported resolution.

The advisory concerns malformed UTF-8 bypassing the default path normalizer's control-character check. The extension does not directly depend on Flysystem. Compatibility with the platform does not resolve this upstream security issue.

## Disposable CI profile

The dedicated `Magento 2.4.7-p10 compatibility` job retains the platform's coverage while leaving the other Magento and Mage-OS jobs unchanged. It applies one exception to the temporary Magento project's root `composer.json`, never the extension package:

* `config.audit.block-insecure` remains enabled.
* The exact advisory uses `apply: block`, permitting resolution while retaining the audit finding. See [Composer audit configuration](https://getcomposer.org/doc/06-config.md#audit).
* The job prints the locked dependency audit and fails on any additional advisory, hidden advisory, or malformed report. It also fails if the expected finding disappears, prompting removal or review of the exception.
* Unit tests, integration tests, and DI compilation use the original platform dependency. The optional backport is tested in a separate library copy.

The test-configuration helper rejects other project versions, unrelated exceptions, and broad policy overrides. It is not a production installation script. Do not disable Composer security blocking globally or copy the test exception into a store as a substitute for mitigation.

## Optional Flysystem 2.5.0 backport

The repository includes a narrow [patch](../../dev/patches/league-flysystem-2.5.0-malformed-utf8.patch) adapted from [upstream commit ef4a9a5](https://github.com/thephpleague/flysystem/commit/ef4a9a557d769b5d472c403125716706a0d9cc77). It treats a PCRE error as a corrupted path, matching the upstream correction. This is a project-maintained backport for **Flysystem 2.5.0**, not a new upstream release or a complete platform security certification. It is not applied automatically by the module.

For a store that must remain on this platform:

1. Review the store's exact locked dependencies and current upstream remedies. Prefer an official compatible correction when one becomes available. Record the security owner's decision before making a production exception.
2. Copy the patch and [regression probe](../../dev/tests/security/flysystem-path-normalizer.php) into the store's version-controlled patch/deployment tooling. Verify the installed library is exactly 2.5.0. Keep the existing vendor artifact and Composer lock file available for rollback.
3. On a staging build with a fresh vendor tree, run the following from the Magento root, adjusting the two artifact paths to their reviewed locations:

   ```bash
   composer show league/flysystem --locked
   patch --batch --fuzz=0 --dry-run -d vendor/league/flysystem -p1 < patches/league-flysystem-2.5.0-malformed-utf8.patch
   patch --batch --fuzz=0 -d vendor/league/flysystem -p1 < patches/league-flysystem-2.5.0-malformed-utf8.patch
   php tools/flysystem-path-normalizer.php "$PWD"
   composer audit --locked
   ```

4. Integrate the patch into the store's normal Composer patch manager or deterministic build step. Fail the build if the patch cannot apply cleanly or the probe fails. A one-time edit under `vendor/` will be lost on reinstall and is insufficient. If dependency resolution needs an exception before patching can run, scope it to this advisory in the store's root project, retain audit reporting, and bind it to the approved patch build and follow-up review.
5. Test the store's media, filesystem adapters, uploads, and other affected integrations before deploying the reviewed artifact. Preserve the vendor/lock rollback pair. The module's local normalizer checks do not cover every store integration.

The probe covers ordinary and Unicode paths, separator and relative-path normalization, control characters, malformed UTF-8, and traversal rejection. The unpatched 2.5.0 copy fails three malformed-path cases; the patched copy passes all 12 cases. The acceptance instance remains unpatched to keep the platform compatibility result independent.

Composer audits package versions rather than local file patches, so **the advisory remains visible after this backport**. Keep that finding and its mitigation record visible until an upstream dependency change resolves it. Remove the temporary exception and backport when a compatible official fix is installed and verified.
