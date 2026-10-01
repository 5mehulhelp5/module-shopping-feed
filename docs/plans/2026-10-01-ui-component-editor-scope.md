# Default UI Component editor migration scope

Prepared October 1, 2026 against `feat/1.2-release` at `f8488b1258cf52f8bb38dc53b983cc745ba28599`.

Status: source-based scope and estimates. No application implementation or runtime acceptance was performed for this scope.

## Recommendation

Replace the legacy feed creation/editing screen with one Magento UI Component form as the default editor for every feed type. Reuse the existing feed configuration and persistence model, with an explicit adapter between form data and that model. Preserve the existing 12 logical sections and their behavior.

Include the small Test Feed form and log-page layout compatibility in the completion scope. These routes also trigger the current Nebula theme fallback, so changing only the main form cannot retire that fallback completely.

Treat Nebula compatibility as a tested rendering path for the shared form. First prove the difficult controls against the intended Nebula version and UI Bridge, then choose the minimum adapters needed. A UI Component form alone does not guarantee that custom JavaScript will translate to Nebula.

The grid is a separate workstream. Retaining its current implementation allows this migration to focus on editor correctness. A later bridge acceptance pass could remove substantially more Nebula-specific code.

## Current surface

The source contains 12 editor sections: General, Columns, Categories, Filters, Options, Configurable, Grouped, Bundle, Shipping, Schedule, Uploads, and Promotions. Some are hidden or changed by feed type.

There are eight configured feed types: Generic, Google Shopping, Google Local Inventory, Meta Catalog, Microsoft Merchant Center, TikTok Catalog, Pinterest Catalog, and the gated OpenAI Google-compatible beta. Promotions is a Google Shopping section, not a ninth editor type.

The migration surface includes:

- 56 PHP files under `Block/Adminhtml/Feed/Edit`, totaling 5,811 physical lines.
- 35 templates under `view/adminhtml/templates/feed/edit`, totaling 2,313 physical lines.
- Two editor JavaScript files totaling 189 physical lines, plus substantial inline JavaScript in the templates.
- 19 directive parameter-renderer PHP classes. These can share a smaller set of input components; they do not imply 19 new JavaScript components.
- Save, Edit, NewAction, Builder, Converter, type-specific form observers, tab-visibility plugins, and editor layout wiring outside those counts.

Counts include comments and license headers. They describe the affected surface, not an estimate of new code or a claim that every file can be deleted.

Source entry points: [editor layout](../../view/adminhtml/layout/mageos_shopping_feed_feed_edit.xml), [form block](../../Block/Adminhtml/Feed/Edit/Form.php), [save controller](../../Controller/Adminhtml/Feed/Save.php), [converter](../../Model/Feed/Converter.php), and [type-specific form events](../../etc/events.xml).

## Proposed implementation

| Area | Replacement | Main acceptance requirement |
| --- | --- | --- |
| Form shell and ordinary fields | `mageos_shopping_feed_form.xml`, a form DataProvider, button providers, standard inputs/selects/multiselects, fieldsets, validation, and declarative dependencies | New/edit/save/save-and-continue work for all eight types; current labels, defaults, help, and field visibility remain correct |
| Type-specific behavior | A small metadata modifier pool for feed type, store context, and allowed actions | Google microdata and filters, Local Inventory differences, and preset notices remain correct |
| Columns and replace-empty rules | Shared DynamicRows-based mapping component with a parameter input selected from metadata | Preserve ordering, duplicate mappings, scalar/array parameters, per-row defaults, unknown saved attributes, and SKU grouping |
| Other repeated configuration | Standard DynamicRows for find/replace, output limits, price buckets, inheritance, schedules, and upload destinations | Add/edit/delete every row, including the final row; preserve child IDs and explicit deletion semantics |
| Category taxonomy | Dedicated component with observable state, hierarchy, category enablement, priority, taxonomy/type values, and suggestions | Preserve store-root filtering, inherited values, inactive categories, bulk operations, and serialization |
| Option category selection | Standard UI select/tree behavior backed by the existing scoped category data | Preserve selected category IDs and store scoping; adapt the suggestion response if its shape differs |
| Promotions | Standard field/date components and rows, with a small component for incrementing the promotion counter | Preserve selected cart rules, coupon/title/date values, counter behavior, and generated promotion IDs |
| Test Feed and logs | Small UI Component test form and compatible result/log layouts | Same preview result, permissions, escaping, popup navigation, and return behavior in each supported Admin environment |

Use ordinary UI Components wherever their behavior fits. Avoid embedding the old Prototype forms in `htmlContent` as the final implementation: that would retain the dependency and lifecycle problems behind the new form shell.

The first implementation slice should combine General fields, one real column-mapping row with changing parameter types, and an upload row. This tests the hardest data and rendering boundaries before converting all the easy fields.

Suggested new locations:

- `view/adminhtml/ui_component/mageos_shopping_feed_form.xml`
- `Ui/DataProvider/Feed/FormDataProvider.php`
- `Ui/DataProvider/Feed/Modifier/` for the few genuinely dynamic metadata concerns
- `Model/Adminhtml/FeedFormData.php` for explicit form projection and input normalization
- `Block/Adminhtml/Feed/Edit/Button/` for UI form buttons
- `view/adminhtml/web/js/form/` and `view/adminhtml/web/template/form/` for the custom controls

Reuse existing option source classes and feed type definitions. Keep business rules in PHP services so custom renderers do not become the source of defaults or persistence behavior.

## Data and extension compatibility

The existing form has different display and submission shapes. `Converter::createArrayFromObject()` emits `config_<path>` display keys, while saves expect nested `config[<path>]`, plus top-level `schedules` and `uploads`. Category taxonomy is submitted as a JSON string. The new form must define these mappings deliberately through data scopes and a normalization boundary.

Required behavior:

1. Keep the current database schema, configuration keys, directive identifiers, feed types, and generated output contracts. No bulk feed-data migration is proposed.
2. Explicitly allowlist fields returned to the browser. The existing model returns upload data with decrypted passwords; the current PHTML masks these at rendering time. The new DataProvider must mask them before JSON serialization and must never serialize raw model data blindly.
3. Preserve the upload password sentinel, ciphertext preservation, new-password validation, and feed ownership checks for uploads/schedules. Failed-save recovery must not put existing or newly entered secrets into browser JSON, logs, or redisplayed form data; require re-entry where appropriate.
4. Map DynamicRows deletion markers to the existing save contract. Removing a row from the browser array alone does not delete an existing schedule/upload. Also define empty-array, unchecked, hidden, and omitted-field behavior so clearing a setting is distinct from leaving it unchanged.
5. Use DataPersistor for failed-save restoration, with errors returned to the same entity/type. Put conversion and validation within the handled save-error path. Preserve existing route, ACL, form-key, save-and-continue, and prepare-save behavior.
6. Preserve unrecognized stored configuration and unavailable attribute values during ordinary edits. Unknown parameter renderers must not silently reset a row or submit an empty value.
7. Separate UI-only row identifiers and position/delete flags from the stored configuration where appropriate. Keep ordering stable and preserve the existing intentional legacy SKU-default normalization.

There are also customization contracts to handle. Existing `...prepare_form_<type>` observers receive PHP form objects, and directive definitions name PHP/PHTML renderer classes. UI Component metadata does not preserve those APIs automatically. Convert the six built-in form observers and the two tab-visibility plugins to metadata behavior, provide documented modifier/parameter-editor extension points, and audit any installed downstream customizations before removing the old classes. Keep directive renderer mappings compatible where practical; arbitrary external PHTML renderers require an explicit migration path.

This should be a separate editor release from the current feed-template work. Select the version after deciding whether existing customization hooks will have a compatibility period or a documented breaking change.

## Reduction in Nebula-specific customization

The current dedicated integration totals approximately 575 runtime/configuration/template lines, plus 219 test lines. Those are physical line counts including comments and whitespace.

| Current customization | Effect of this migration | Condition for removal |
| --- | --- | --- |
| `FeedEditorTheme.php`, 56 lines | Stops forcing new/edit/test/log routes into `Magento/backend`, rewriting page layout names, injecting a classic menu, and removing Nebula assets | Every affected route works in the supported Nebula rendering path, including preview and logs |
| `Feed/Edit/Menu.php`, 12 lines | Removes the special menu cache key used for theme switching | Theme fallback is no longer used |
| `Feed/Edit/Form/Element/Dependence.php`, 8 lines | Replaces the legacy dependency-block workaround with UI field dependencies | General form has migrated |
| Approximately four lines of editor DI wiring | Removes the plugins for editor theme/layout switching | Theme fallback is no longer needed for supported installations |
| Remaining approximately 495 lines | Native grid JSON, layout injection, row rendering/data projection, toolbar/options, mass-action translation, export and URL/ACL handling remain | A separate grid migration/bridge acceptance proves equivalent behavior |

Thus the upper bound for retiring the existing editor fallback is about 80 of 575 lines, or 14%, before accounting for any new bridge adapters. The associated 41-line `FeedEditorThemeTest` would be replaced by tests of the new supported behavior. Most Nebula code is currently about the grid, so a form-only migration should not be sold as removing most of the integration.

The architectural benefit is larger than those 80 lines: one form definition, one set of data/validation rules, fewer legacy JavaScript lifecycle assumptions, and fewer theme-specific exceptions to maintain. The module can continue working without any required Nebula dependency.

Local evidence matters here. On October 1, the adjacent Mage-OS application's Composer lock lists `qoliber/nebula-admin-theme:0.9.0`; its module configuration contains no `Qoliber_NebulaUiBridge` or `Qoliber_NebulaUiTransformer`. The inspected UI-removal plugins allow unmatched UI Components to proceed through Magento, but that source behavior does not prove the new complex form will render and behave correctly in the theme. Test that path explicitly. If it fails, retain a narrowly scoped fallback for that supported configuration until a working replacement is verified.

Qoliber documents a separate UI Bridge that translates UI Component definitions and offers converters for bespoke components. It does not establish automatic execution of this extension's custom JavaScript or parameter controls. A native form in Nebula must be accepted with the actual bridge/version combination. Unsupported editable controls must prevent a destructive partial save rather than disappear unnoticed. [Nebula UI Bridge documentation](https://qoliber.com/nebula-ui-bridge.html)

Optional follow-up: test the existing Magento UI Component grid through the bridge and retire the parallel native grid if it matches filtering, sorting, pagination, exports, links, bulk actions, ACL, and safe data projection. This is the workstream that could eliminate much of the remaining 495 lines. Some shared formatting/security behavior may move rather than disappear, and remaining bridge-specific adapters would need to be counted before claiming a net reduction.

## Delivery sequence and effort

These are preliminary engineering-effort estimates based on source inspection, not elapsed-time promises. They assume a developer familiar with Magento Admin, available disposable test environments, and access to the intended bridge package. Re-estimate after the first slice.

| Stage | Deliverable | Estimate |
| --- | --- | --- |
| 1. Contracts and representative slice | Captured synthetic data/output fixtures; default form skeleton; mapping-parameter and masked-upload proof in standard Admin; Nebula capability probe | 1-2 days |
| 2. Form foundation | DataProvider, input adapter, buttons, standard fields, conditional metadata, failed-save behavior, type-specific rules | 2-3 days |
| 3. Mapping and repeated controls | Column parameters and fallback mappings, filters, inheritance, schedules, uploads, category selectors | 3-5 days |
| 4. Taxonomy and promotions | Hierarchical taxonomy editing, suggestions, promotion rows/dates/counter | 2-3 days |
| 5. Compatibility and acceptance | Preview/logs, Nebula decision and fallback cleanup, complete browser/data/output parity, documentation | 2-4 days |
| Total for default editor migration | All default editor behavior verified; exact Nebula compatibility and retained adapters documented | **10-17 engineering days** |

A separate grid bridge evaluation/removal is provisionally another 1-3 days if bridge coverage is adequate. Missing bridge converters, a complex external renderer integration, or a substantial category-performance issue would expand these estimates. A production-tested native Nebula form is contingent on the stage-one result; budget separately if substantial custom converters are required.

## Acceptance and rollback

- All eight types: create, load an existing feed, save without edits, change fields, save-and-continue, revisit each applicable section, and recover a failed save. Keep the OpenAI beta's existing gating and notices.
- Compare normalized stored configuration and generated feed artifacts before and after an editor round trip using deterministic synthetic catalog fixtures. Allow only documented existing normalizations; include Google/Local Inventory, custom CSV settings, and each newer preset.
- Exercise every parameter-renderer family, missing attributes, multiple parameters, ordering, explicit empty/zero/false values, literal quotes/backslashes/HTML, deleting the final row, and hidden fields.
- Verify password masking in HTML and UI JSON, unchanged-password saves, replacement passwords, failed saves, ciphertext preservation, and cross-feed child-ID rejection. Keep server-side authorization and form-key checks authoritative.
- Category and promotion acceptance covers the current hierarchy, suggestions, inheritance, store roots, dates, and counter semantics. Include a representative larger category tree and column map to catch payload/rendering regressions.
- Verify the standard Magento Admin, Mage-OS default Admin, a fresh installation with no Nebula packages, and each explicitly supported Nebula/bridge combination. Record the exact versions and whether the screen is bridge-rendered, stock-rendered within Nebula, or uses a fallback.
- Add focused PHP tests for data projection/normalization and modifiers, JavaScript behavior tests for custom components, and browser coverage for actual UI initialization and save/reload. Port existing template-dependent tests to the replacement behavior before deleting old templates.
- Run existing unit/integration/database checks, XML and UI naming-contract validation, coding checks, DI compilation and production static deployment, followed by browser acceptance. Existing CI is not a substitute for browser acceptance of the new form.

Develop in an isolated branch/test installation. Switch the public new/edit routes only when all form sections are complete. The finished release should have one default editor; retain only a narrowly justified environment fallback if required by the supported Nebula matrix.

Because schema and persisted formats remain compatible, rollback should be a package/code rollback followed by the appropriate DI/static/cache rebuild. Rehearse old-code reading and editing of a feed saved by the new editor, including unknown configuration and child rows. Test data belongs in disposable fixtures; no production write or database restore is part of this scope.

Reference patterns checked for this scope: [Adobe Form component](https://developer.adobe.com/commerce/frontend-core/ui-components/components/form) and [Adobe DynamicRows](https://developer.adobe.com/commerce/frontend-core/ui-components/components/dynamic-rows).
