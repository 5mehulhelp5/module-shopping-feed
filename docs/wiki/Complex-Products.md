# Complex products

Configurable, grouped, and bundle products can produce parent rows, associated rows, or both. Decide the row model first, then configure inheritance, URLs, stock, price, and duplicate handling around it.

> Documentation baseline: release `v1.1.0`. Last reviewed: 2026-09-28.

## Associated-product modes

Each complex product type offers these modes:

* **Only parent**, with no associated-product rows
* **Only associated products**, with no parent row
* **Both parent and associated products**

The correct choice depends on the recipient and the catalog. Test row counts and identifiers before applying one model to every product type.

## Configurable products

Available controls include:

* How associated products are added
* Whether out-of-stock children are allowed
* Whether a child inherits the parent's out-of-stock state
* Whether not-visible children receive unique deep links
* The separator used when attribute values are merged
* Per-column inheritance between parent and associated product

Google Shopping defaults include item grouping and standard variant columns. Confirm that each child has a stable ID, the intended `item_group_id`, a working deep link, and the correct `color`, `size`, `material`, `pattern`, `gender`, and `age_group` values where applicable.

With default stock statuses and parent out-of-stock inheritance enabled, the module checks the parent's Magento salability before checking the child's stock. A configurable parent's own quantity can be zero while its children remain purchasable. Since 1.0.0, the module does not treat that zero parent quantity alone as out of stock. Custom stock attributes and disabled inheritance retain their configured behavior.

Google Local Inventory removes the unique-link and attribute-separator controls from the Configurable Products section. Its source-level rows focus on inventory context.

## Grouped products

Available controls include:

* How associated products are added
* Whether out-of-stock associated products are allowed
* Whether not-visible associated products receive unique URLs
* **Minimal price** or **Sum of associated products prices**
* Per-column inheritance between parent and associated product

The summed price mode uses default associated quantities when they are available. If the grouped product has no usable default quantities, the mapper can fall back to minimal price.

## Bundle products

Available controls include:

* How option products are added
* Whether shipping weight is calculated as the combined weight of selected items

Test fixed and dynamic price or weight bundles separately. Optional and required selections can change both the minimum viable row and its calculated values.

## Complex Product Context Prioritization

The **Complex Product Context Prioritization** setting in General Configuration (General in the candidate) can prioritize individually visible simple products in their complex-product context, including Search-only children. With it enabled, a child created before its parent retains the same parent grouping as a child created later. Enable it when standalone processing produces duplicate or contextually wrong rows, then measure the generation cost.

## Verification matrix

For each product type, record:

| Check | Parent | Associated or option item |
| --- | --- | --- |
| Included or excluded |  |  |
| ID |  |  |
| URL |  |  |
| Price and sale price |  |  |
| Availability and quantity |  |  |
| Image |  |  |
| Category and taxonomy |  |  |
| Inherited columns |  |  |

Run the matrix for in-stock and out-of-stock combinations. For MSI Local Inventory, repeat it across at least two sources linked to the selected website stock.
