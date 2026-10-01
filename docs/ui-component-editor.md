# Admin UI Component editor

The default New/Edit Feed screen and Test Feed screen use Magento UI Component forms. The editor keeps the existing routes, feed types, configuration keys, database schema, generators, and save permissions. General settings, mappings, categories, filters, product options, product relationships, shipping, schedules, uploads, and Google promotions remain available, with the existing type-specific differences.

## Form architecture

- `mageos_shopping_feed_form.xml` declares the provider, submit route, and buttons. `Ui/DataProvider/Feed/FormDataProvider` supplies data and applies the `ShoppingFeedFormModifierPool` modifiers.
- `Form/Fields` and `Form/Metadata` define fields, dependencies, and DynamicRows. `Form/Options` reuses the existing option sources. `Form/Parameters` maps the bundled directive renderer identifiers to declarative parameter controls.
- `Model/Adminhtml/FeedFormData` projects only editor fields and masks upload passwords before they reach provider JSON. Initial data uses a protected JSON envelope decoded after UI template initialization, preserving literal `${...}` text and nested array shapes through Magento's metadata sanitizer. The JavaScript provider also sends a JSON envelope so empty collections, nested parameters, zero, false, and literal text survive serialization.
- Mapping rows keep their explicit Order values, including duplicate priorities. New rows default after the largest existing order. Other configuration row lists preserve drag-and-drop positions when saving.
- DynamicRows removes deleted records. The provider remembers the initial schedule/upload IDs and submits explicit deletion markers for removed persisted children. The existing model still checks that each child belongs to the feed.
- The converter applies submitted configuration keys to the loaded feed. Unexposed custom configuration remains stored. Selects preserve unavailable saved options, and unknown parameter renderers preserve their values with a read-only notice.
- Failed saves use `DataPersistor` keyed to the feed ID, type, and store. New and changed passwords must be entered again; decrypted passwords and typed replacement passwords are not restored into provider JSON.

The old column widget required stripping newlines and coercing all mapping values to strings before saving. That workaround is removed. The new form preserves literal text and structured parameters. Normal existing feed output is unchanged; deliberately entering literal whitespace can now affect that column's output.

## Extending the editor

PHP form-block `prepare_form_*` events and plugins on the legacy tab blocks no longer customize the default editor. Their six bundled observers and two tab-visibility plugins have been replaced by metadata behavior. The legacy class/template files remain in the package for a transition period, but the default routes do not render them.

Use a Magento UI data-provider modifier implementing `Magento\Ui\DataProvider\Modifier\ModifierInterface`. Register it in your module's `etc/adminhtml/di.xml`, with a module sequence after `MageOS_ShoppingFeed`:

```xml
<virtualType name="ShoppingFeedFormModifierPool" type="Magento\Ui\DataProvider\Modifier\Pool">
    <arguments>
        <argument name="modifiers" xsi:type="array">
            <item name="vendor_feed_fields" xsi:type="array">
                <item name="class" xsi:type="string">Vendor\Module\Ui\FeedModifier</item>
                <item name="sortOrder" xsi:type="number">100</item>
            </item>
        </argument>
    </arguments>
</virtualType>
```

`modifyMeta()` receives fieldsets named `general`, `columns`, `categories`, `filters`, `options`, `configurable`, `grouped`, `bundle`, `shipping`, `schedule`, `uploads`, and, for Google Shopping, `promotions`. Metadata uses `arguments/data/config`. `modifyData()` receives `[$feedId => $fields]`, or `['' => $fields]` for a new feed. Configurable settings use a `config.<existing_key>` data scope. A modifier adding a stored setting must supply its current value in `modifyData()` as well as its field metadata. The current feed is registered as `feed` in Magento's registry.

For a custom directive's parameter editor, add its existing PHP renderer class identifier to the `renderers` array argument of `MageOS\ShoppingFeed\Ui\DataProvider\Feed\Form\Parameters`. Supported definitions are `input`, `textarea`, `select`, `multiselect`, and `none`; use `label`, `notice`, and `options`, or an option `source` registered with `Form\Options`. The directive's configured `param` supplies the default when the administrator changes directives. More complex controls can replace the mapping parameter component through metadata. Arbitrary PHP/PHTML renderer output is not executed by the new form.

The `mageos_shopping_feed_feed_prepare_save` event remains available and receives decoded form parameters. Existing non-UI save requests remain supported. Do not rely on browser validation for authorization or data ownership.

## Nebula support

This is one shared Magento form implementation and has no required Nebula dependency. It removes the default editor's reliance on the old PHP form renderer, Prototype-era row widgets, global editor scripts, and the custom PHP dependency element. Future theme support can use the same form metadata and persistence contract.

The existing `FeedEditorTheme` fallback and menu cache handling remain. The inspected Nebula 0.9.0 installation does not include UI Bridge, and translating the custom parameter/category controls through a bridge has not been accepted. Nebula installations therefore continue using Magento/backend for the editor, preview, and log routes. The separate Nebula grid integration is unchanged. This migration does **not** claim that most of the approximately 575 lines of Nebula integration have been removed; roughly 495 lines concern the grid, and removing the editor fallback requires separate native Nebula acceptance.

Existing users with custom observers, tab plugins, or parameter renderers should migrate those editor integrations before upgrading. This is an extension customization API change, independent of Magento platform compatibility.

## Rollback

There is no database migration. Reverting the package code restores the previous editor after the normal DI compilation, static asset deployment, and cache refresh for that installation. Retain a normal deployment backup and test any site-specific custom parameter structures against the older editor, which normalizes mapping values more aggressively.
