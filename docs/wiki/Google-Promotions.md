# Google Promotions

Google Promotions is a companion file generated from an enabled Google Shopping feed. It maps selected Magento cart price rules into promotion rows and can add matching promotion IDs to product rows.

> Documentation baseline: public repository commit `b77605d`. Last reviewed: 2026-08-29.

## Prerequisites

* A configured Google Shopping feed
* Eligible Magento cart price rules
* Access to Google's Promotions program
* A non-serving Promotions test destination

Review Google's current [Promotions data specification](https://support.google.com/merchants/answer/2906014?hl=en) and editorial requirements. Program and account eligibility remain Google's decision.

## Configure product linkage

In the Google Shopping feed's Columns Map, add:

| Column | Directive |
| --- | --- |
| `promotion_id` | Promotion ID |

The header is singular. The directive returns IDs for configured promotions that apply to the product.

## Configure the companion feed

Open the **Google Promotions** tab:

1. Set **Enable feed** to **Yes**.
2. Select the cart price rules to include.
3. Review each promotion title.
4. Review effective and display date ranges.
5. Confirm coupon-code requirements.
6. Save the feed before generating it.

The Admin validates several title rules and warns when a shipping promotion has no coupon. Google remains the authority for final policy and editorial acceptance.

## Generated file

The default companion filename is:

```text
pub/media/mageos-shopping-feed/mageos_shopping_feed_promotion_<feed_id>.txt
```

The file includes promotion ID, product applicability, title, effective dates, display dates, redemption channel, Shopping ads and free-listing destinations, offer type, coupon code, and minimum purchase amount.

## Change detection

The module hashes the saved promotion configuration. If the configuration has not changed and the existing file is still present, it does not rebuild or re-upload the companion file.

Use **Submit as new promotion** when an already approved promotion must receive incremented IDs. Save the feed after incrementing, then generate again.

## Upload behavior

When a new Promotions file is created, it uses the Google Shopping feed's configured upload destination. If gzip is enabled for that destination, the module compresses the Promotions file for transfer and removes the temporary gzip file afterward.

## Verification

1. Confirm the product feed contains expected `promotion_id` values.
2. Confirm only selected and eligible cart rules appear in the Promotions file.
3. Check titles, dates, coupon codes, applicability, destinations, and minimum purchase values.
4. Submit to a non-serving Promotions destination.
5. Record Google processing results before enabling a production schedule.
