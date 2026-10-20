# 1.2.4 documentation review

Reviewed October 20, 2026 against published tag `v1.2.4`, commit `75dd335f930aef78ae693f62c3039e469c2d26f6`. This is a documentation-only follow-up on branch `docs/1.2.4-documentation`. It does not modify module behavior or repeat runtime acceptance.

## Release and publication evidence

- GitHub main, the annotated tag's resolved commit, and the [published release](https://github.com/mage-os-lab/module-shopping-feed/releases/tag/v1.2.4) identify the same commit.
- All 37 [post-merge CI jobs](https://github.com/mage-os-lab/module-shopping-feed/actions/runs/39372608509) passed for that exact commit.
- Packagist's Composer P2 metadata for `mage-os/module-shopping-feed` 1.2.4 has matching source and dist references. This review checks metadata; it does not repeat the earlier fresh-install/archive comparison.
- The public wiki was still at `4840a022cd473732affd19a5eb23b108aec575ab`, with 44 Markdown files and 1.2.3 on Home. Before this refresh, it differed from the released wiki source on 21 existing pages and lacked `Release-1-2-4.md`.
- The refreshed source contains 45 Markdown files: 43 user-facing pages and two navigation files. It changes 39 existing public-wiki files and adds one, with no deletions. Module documentation and wiki publication require separate commits and approvals under [the maintenance workflow](../WIKI-MAINTENANCE.md).

## Coverage

The review covers the README, changelog, installation, security policy, contributor guide, migration/coexistence, provenance and licensing notices, acceptance plan, editor developer contract, Magento 2.4.7-p10 guidance, wiki maintenance/content map, and all 45 wiki files. Current instructions were compared with the released Composer metadata, workflow, ACL/controllers/buttons/grid projections, form/upload model and transports, generator/logger, presets/validators, inventory and promotion changes, and `.gitattributes`.

All 36 operating guides now name the reviewed 1.2.4 runtime baseline and October 20 review date. The seven release-summary pages retain their version identities. Dated release notes, reviews, the editor acceptance report, and plans retain their original test scope and history. A release disposition was added above the October 20 pre-release verification snapshot rather than rewriting its original uncommitted state or test totals. The [documentation index](../README.md) distinguishes current guidance from historical evidence.

## Corrections

| Area | Finding and correction |
| --- | --- |
| OpenAI provider drift | The current public product specification no longer contains the linked Google-compatible section. The 1.2.4 preset lacks required seller data and current URL/variant fields and uses `preorder` instead of `pre_order`. The [guide](../wiki/OpenAI-ChatGPT.md), README, feed/status pages, and current release documentation explain the historical beta and require explicit sample acceptance. No current-format implementation or ingestion acceptance is claimed. |
| Preview authorization | The README, operating guide, and developer contract incorrectly allowed Test Feed with grid access. The 1.2.4 controller and editor buttons require `MageOS_ShoppingFeed::generate`. Both grid projections still expose a Test Feed link to read-only users; the docs distinguish this visible link from server authorization. |
| SFTP and credentials | Replace standards-branch wording with released behavior; carry fingerprint/schema requirements into migration, setup, troubleshooting, operational references, and acceptance. Document encrypted model data and `Upload::getDecryptedPassword()` for custom transports, retained clone credentials/pins, and clone rollback. |
| Migration installation | The optional companion guide still selected core `^1.2.3`. It now selects `^1.2.4` and requires a separately trusted SFTP key before activating imported destinations. No companion release or automatic migration is implied. |
| Text limits | The default-key mismatch is still present in 1.2.4. Meta's validator does not check text lengths; the old blanket claim that every catalog validator rejects overlong text was false. Correct it and document explicit limits and recipient review. |
| Log rotation | Explain size/SHA256 verification before source truncation, preservation on failure, and the default 512 KiB threshold. Logging Level is read at Default Config scope despite its broader field visibility. |
| Acceptance | Add baseline/static-analysis/audit/wiki checks, SFTP rejection before credentials, fingerprint persistence, encrypted model data and clone rollback, preview roles, complete archive equality/failure, missing promotion timezone, and actual CSP testing. Update the final platform gate from Mage-OS 3.5.0 to 3.6.0 and distinguish CLI behavior when cron is disabled. These remain unchecked procedures. |
| Runtime and platform claims | Separate the historical `133af71` deployment and older theme/browser runs from 1.2.4's Mage-OS 3.6 evidence. Link current exact-commit CI; preserve historical workflow URLs only for historical runs. The declared Magento 2.4.6 floor is not installed-store acceptance. |
| CSP | Describe Luma secure-renderer registration without treating it as proof for every enforced policy or strict Hyvä theme. Keep strict Hyvä acceptance separate. |
| Repository and archive links | Composer dist archives exclude `docs/` and `dev/`. The distributed README, changelog, and migration document now link to repository documentation; screenshot URLs are pinned to the release. Contributor acceptance guidance now links to the current evidence index instead of the superseded October 1 failure report. |
| Navigation and upgrade history | Align the current content map and maintenance baseline, move the retained 1.2.0 upgrade anchor to its proper section, explain cumulative upgrade requirements and caret constraints, and qualify source-install Composer dependencies. |
| Current fixes | Add relevant MSI interface, configurable-weight/tax-helper, prefixed complex-parent lookup, and promotion UTC-fallback notes to their operating guides. |

## Provider reference checks

All 31 distinct non-GitHub public documentation/support/package links checked returned HTTP 200. The former Rocket Web product URL redirects to [the current Mage-OS Shopping Feeds page](https://rocketweb.com/mage-os-shopping-feeds); active documentation now uses that destination.

Google's product, identifier, availability-date, Local Inventory, Promotions, automatic-update, and dynamic-remarketing references remain reachable. The retrieved Local Inventory specification retains its physical-store availability vocabulary, and Google's availability-date page retains the up-to-one-year requirement. Microsoft, TikTok, and Pinterest references were retrieved and reviewed for the documented format, shipping, image, stock, and sale-date points. Meta's rendered product specification confirms brand, `in stock`/`out of stock`, 200-character titles, and 9999-character descriptions. These checks do not prove recipient account eligibility or ingestion.

OpenAI's current [product specification](https://developers.openai.com/commerce/specs/file-upload/products) requires `seller_name` per row and current variant structure; its [upload guide](https://developers.openai.com/commerce/specs/file-upload/overview) describes supported compressed formats and snapshot/removal behavior. The former `#google-compatible-product-data-feeds` anchor is absent. The local validator's older requirements are now described as local behavior rather than the current provider contract. Updating that preset is separate implementation work.

## Validation

Run from the documentation worktree:

```sh
composer validate --strict --no-check-publish
php dev/tests/validate.php
php dev/tests/validate-wiki.php
git diff --check
```

The documentation review also checks file/page/image links, anchors, and same-repository pinned references across all Markdown files; renders the documentation locally; compares the prepared wiki with `docs/wiki` byte-for-byte; and confirms the source diff contains Markdown only. Temporary review evidence is outside the repository. The final command results are recorded with the prepared local review artifacts.

| Check | Result |
| --- | --- |
| Strict Composer validation | Passed |
| Consolidation validation | Passed: 26 XML files, eight feed types, isolated runtime identifiers |
| Wiki validator | Passed: 43 user-facing pages and 43 sidebar targets |
| Documentation target/reference/anchor check | Passed: 419 targets across 106 Markdown files, zero failures |
| External reference retrieval | 31 distinct public links returned HTTP 200; Meta's specification was also inspected in the browser |
| Local Markdown render | 106 files rendered; OpenAI comparison table, README permissions, and all five README images inspected |
| Prepared wiki equality | All 45 source files match byte-for-byte; 39 existing public pages changed, one added, none deleted |
| Whitespace | Module and prepared wiki diffs passed `git diff --check` |
| Change isolation | Markdown-only diff; the original main checkout retains its existing untracked review file |

Runtime PHPUnit/integration suites and store/provider acceptance were not repeated for this Markdown-only change. Published 1.2.4 CI and dated runtime records remain evidence for their exact commits and fixtures.

## Remaining boundaries

- The OpenAI preset needs a separate current-contract implementation and recipient acceptance if that integration is wanted. This review documents the gap.
- The preset output-limit mismatch and read-only Test Feed grid-link visibility remain runtime behavior; this refresh does not change them.
- Native Nebula UI Bridge rendering, strict Hyvä CSP themes, external provider ingestion, and production-catalog capacity remain separate acceptance targets.
- The public wiki remains unchanged until the reviewed documentation source is merged and the matching wiki publication is approved. No module or wiki commit, push, merge, release edit, or production deployment is performed by this review.
