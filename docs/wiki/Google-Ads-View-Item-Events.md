# Google Ads view_item events

The configurable-product integration dispatches a `view_item` event when a complete option selection resolves to an associated product. It exposes a custom browser event for consent and tag-manager integrations, and can call `gtag` directly when enabled.

This event bridge uses Magento's RequireJS integration. Version 1.1.0 supports native Hyva configurable deep-link selection, but does not add a Hyva Google Ads event bridge. Verify or provide a separate theme integration before relying on events there.

> Documentation baseline: release 1.2.1 (`v1.2.1`); earlier acceptance is identified by version. Last reviewed: 2026-10-04.

## Event data

The event detail contains:

```json
{
  "value": 29.99,
  "items": [
    {
      "id": "CHILD-SKU",
      "google_business_vertical": "retail"
    }
  ]
}
```

`value` comes from the selected option's final price. The item ID uses the associated product SKU when available.

## Custom event

After a new complete selection, the module dispatches:

```text
mageos:shopping-feed:view-item
```

This browser event is dispatched independently of direct `gtag` delivery. A consent platform or tag manager can listen for it and decide when or whether to forward the data.

## Direct Google tag delivery

To permit a direct Google tag call:

1. Open **Stores > Configuration > Mage-OS > Mage-OS Shopping Feed**.
2. Set **Enable Google Ads Dynamic Remarketing Events** to **Yes**.
3. Optionally set **Google Ads Destination ID**, such as `AW-123456789`.

If `window.gtag` exists, the module calls:

```javascript
gtag('event', 'view_item', eventData)
```

When a destination ID is configured, the event includes `send_to`. Leave it empty when Google Tag Manager or another integration owns routing.

**Unreleased scope correction:** direct-delivery enablement and the destination ID now use the current store view, inheriting website and global values when no override exists. Earlier code ignored the scoped values. Existing overrides take effect after upgrade; review them and clear applicable caches. Store-scope configuration passes native checks on Magento Open Source 2.4.8 and 2.4.9; actual consent-managed delivery remains a separate acceptance step.

## Consent boundary

The module does not install a Google tag or a consent-management platform. The storefront must load and govern those systems. Do not enable direct delivery until the site's consent behavior has been reviewed for the applicable jurisdictions and policies.

## Duplicate protection

The script remembers the last resolved product ID and does not emit another event for the same selection until the resolved child changes.

## Verify

1. Test a configurable product with swatches.
2. Test one with dropdowns.
3. Confirm no event fires for an incomplete selection.
4. Confirm one event fires when a new child resolves.
5. Confirm ID and value match the selected child.
6. Confirm the consent and tag-manager behavior in allowed and denied states.

See Google's current [dynamic remarketing documentation](https://developers.google.com/tag-platform/devguides/dynamic-remarketing) for the receiving platform requirements.
