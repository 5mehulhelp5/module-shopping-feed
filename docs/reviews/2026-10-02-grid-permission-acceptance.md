# Standard Admin grid permission acceptance

Tested October 2, 2026, on `feat/ui-component-editor` after form repair commit `c8092ca`. This follow-up resolves the control-visibility finding in the [Magento Docker acceptance report](2026-10-02-magento-docker-acceptance.md). It changes the standard grid's presentation; controller ACL enforcement already denied unauthorized requests.

## Repair

The Create New Feed block checks save permission. The row-action column independently checks save and generate permissions. The grid's mass-action component filters each action by its declared ACL resource and disables the component when no choices remain. Custom actions without an ACL declaration keep their existing visibility, and explicitly disabled actions remain disabled.

`FeedActions` receives an `AuthorizationInterface` constructor argument after `$urlBuilder`. Magento DI supplies the dependency; downstream subclasses that explicitly call the parent constructor need to pass it. The [developer guide](../ui-component-editor.md#grid-permissions-and-extensions) documents custom action permissions.

## Browser results

Both retained Docker installations, Magento Open Source 2.4.8 and 2.4.9, passed this matrix in production mode:

| Role, in addition to grid access | Create New Feed | Row actions | Bulk actions |
| --- | --- | --- | --- |
| Read-only | Hidden | Test Feed, View Log | Menu omitted |
| Save only | Visible | Configure, Test Feed, View Log | Enable, Disable, Clone |
| Generate only | Hidden | Run Now, Test Feed, View Log | Menu omitted |
| Delete only | Hidden | Test Feed, View Log | Delete |
| Full access | Visible | Run Now, Configure, Test Feed, View Log | Enable, Disable, Clone, Delete |
| No module access | Grid denied | None | None |

For every role with grid access, all 16 fixture rows and Export remain available, and Test Feed and View Log open without error. The editor opens for save-only and full-access users. Tests open the rendered row and bulk menus and compare their visible choices with the role's permissions.

Across the restricted roles, all 32 forbidden-route checks per version return HTTP 403. These cover edit, save, generate, delete, mass clone, mass enable, mass disable, and mass delete according to each role's permissions. All 12 fresh browser sessions record zero JavaScript errors. This follow-up checks control visibility and permitted page access; the preceding Docker report supplies the successful mutation and generation workflow evidence.

## Automated verification

The new regressions first failed on the unfiltered behavior: unauthorized Create New Feed, Configure, Run Now, and bulk choices were present. After repair:

| Check | Magento 2.4.8 | Magento 2.4.9 |
| --- | --- | --- |
| PHP unit suite | 799 tests, 1,864 assertions | 799 tests, 2,124 assertions |
| Official Magento integration harness | 16 tests, 32 assertions | 16 tests, 32 assertions |
| Magento XML validation | 26 files | 26 files |
| Production DI compilation | Passed | Passed |
| UI naming contracts | 3 components | 3 components |

The unit cases cover independent permission combinations, actual grid XML ACL declarations, custom actions with and without ACL resources, and explicitly disabled actions.

The first integration rerun read production-generated DI metadata while installing its separate test application with a different enabled-module set. It failed resolving a TwoFactorAuth interface before tests ran. Mounting a separate `generated/metadata` directory only in the integration container resolved the harness conflict; the production metadata stayed intact. The passing run uses the official harness, a dedicated integration database, and tests instantiation of both changed grid components.

## Scope and evidence

The module requires no new dependency or schema migration. The Nebula integration and server-side mutation controllers are unchanged. These results do not establish native Nebula bridge rendering or acceptance of downstream customizations. The deployment on `mageos-latest` has not received these repairs.

Evidence is retained under `/private/tmp/shopping-feed-magento-docker-20261002/{248,249}/evidence/`, including `grid-acl-before.log`, `grid-acl-unit.log`, production compilation/XML logs, and `shopping-feed-grid-<version>-<role>.jsonl`. The failing regression log is on 2.4.8; passing suites and browser records exist for both versions.

The local Admin addresses remain `http://127.0.0.1:8128/admin/` and `http://127.0.0.1:8129/admin/`. Login details remain in the protected local `admin-access.json` file described by the preceding report.

All 408 runtime/package paths match the repository in both installed modules. The wiki validator passes all 37 pages and sidebar targets. Both applications finish in production mode with 16 disabled feeds, 1,073 configuration rows, and no schedules, upload destinations, or queued work.

The initial integration failure printed generated local database credentials in its verbose command. The retained failure logs were redacted, those credentials were rotated, and old-credential rejection was verified. The runner now redacts captured errors before display. Both applications passed fresh Admin grid, editor, preview, log, and JavaScript checks after the database containers and PHP services restarted with the updated configuration. No external store credentials were involved.
