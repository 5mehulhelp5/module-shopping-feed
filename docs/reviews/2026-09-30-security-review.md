# Security and bug review, 2026-09-30

Reviewed commit: `55ee46717406c7e2166ed75e76d27724e9a34486`, branch `feat/1.2-release`, clean working tree.

Scope: all 297 PHP source files (excluding `Test/`), all 44 `.phtml` templates, admin and frontend JavaScript, layout and config XML, `composer.json`, and the CI workflow. This review covers the whole module, with findings from the 2026-09-09 and 2026-09-24 reviews re-verified as fixed where they intersected this pass.

## Summary

No critical or high severity issues found. The module's highest-risk surfaces (output path traversal, upload credential storage, admin ACL enforcement, SQL construction, feed ID ownership checks) are well defended, and several prior findings in those areas were confirmed fixed.

Three moderate findings: two stored-XSS vectors in the feed edit form and one request-parsing bug in the feed builder that affects every admin controller. The remainder are low-severity robustness bugs and hardening notes.

| # | Severity | Type | Location |
| --- | --- | --- | --- |
| 1 | Moderate | Stored XSS | `view/adminhtml/templates/feed/edit/tab/filters/find-replace.phtml:127-128` |
| 2 | Moderate | Stored XSS | `view/adminhtml/templates/feed/edit/tab/columns/columns-map.phtml:246` |
| 3 | Moderate | Bug (request parsing) | `Controller/Adminhtml/Feed/Builder.php:71,75,84` |
| 4 | Low | Stored XSS | `Ui/Component/Listing/Column/Schedules.php:74` via `Model/Feed/Schedule.php:111` |
| 5 | Low | Bug (promotions broken) | `Model/Promotions/Provider/Map.php:140-152` |
| 6 | Low | Bug (TypeError) | `Model/Generator.php:286` |
| 7 | Low | Bug (missing isset) | `Model/Feed.php:558`, `Model/Promotions/Provider/Map.php:92,103,115-116`, `Ui/Component/Listing/Column/Status.php:107` |
| 8 | Low | Bug (edge cases) | `Model/Product/Processors/Option.php`, `Model/Product/Mapper/Generic/Simple/Url.php:47-50` |
| 9 | Low | Unescaped grid HTML | `Ui/Component/Listing/Column/File.php:96-110`, `Ui/Component/Listing/Column/File/Plugin.php:56-64` |
| 10 | Informational | CSV formula injection | `Model/Generator.php:597-609`, `Observer/Promotions/Generator.php:118-120` |
| 11 | Informational | Hardening | `view/adminhtml/templates/feed/edit/tab/categories/category-taxonomy.phtml:112`, CI workflow pinning |

## Findings

### 1. Moderate: stored XSS in find/replace filters via `escapeJsQuote`

`view/adminhtml/templates/feed/edit/tab/filters/find-replace.phtml:127-129` emits saved find/replace values into a `<script>` block:

```php
findReplaceControl.addItem(
    '<?php echo $block->escapeJsQuote($item['find']) ?>',
    '<?php echo $block->escapeJsQuote($item['replace']) ?>',
```

Magento's `Escaper::escapeJsQuote()` is `addcslashes($data, $quote)`: it escapes only the quote character. It does not neutralize `</script>`, which terminates the script element at the HTML parser level regardless of JavaScript string context. Find and replace are free-text fields, so a value such as `</script><script>alert(document.domain)</script>` round-trips from the database into the page unchanged and executes for any admin who opens the feed edit page.

Exploitation requires the `MageOS_ShoppingFeed::save` ACL (the Save controller validates the form key and ownership), so this is an admin-with-limited-rights to admin privilege escalation / persistent payload, not an unauthenticated vector. The same payload also corrupts the page for other admins until the feed is fixed.

Status: verified by code inspection (Escaper behavior is framework-confirmed; the template path from POST to DB to template was traced). Not executed against a live admin session.

Fix: use `$escaper->escapeJs()` (hex-encodes all non-alphanumerics, safe inside script) or `jsonEncode()` with `JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT`. Do not add `escapeHtml`, because entities are not decoded inside script data and would corrupt literal find strings such as `&`.

### 2. Moderate: stored XSS in columns map array params via `jsonEncode` without HEX flags

`view/adminhtml/templates/feed/edit/tab/columns/columns-map.phtml:246`:

```php
param = <?php /* @noEscape */ echo $this->helper('Magento\Framework\Json\Helper\Data')->jsonEncode($item['param']); ?>;
```

`Magento\Framework\Json\Helper\Data::jsonEncode()` delegates to `Zend_Json::encode()` without `JSON_HEX_TAG`, so a `</script>` sequence inside an array param value breaks out of the script block. Array params occur for multi-select directives (for example variant attributes). The Save controller does not validate posted `param[]` values against the attribute list, so a tampered POST stores an arbitrary string that later renders raw.

The adjacent single-value branch (line 249) is safe: it uses `escapeJs`, which hex-encodes `<`. The column and attribute values (lines 252-253) are safe: `escapeHtml` runs before `escapeJsQuote`.

Exploitation requires the same save ACL as finding 1. Status: verified by code inspection.

Fix: pass HEX flags. The helper does not accept flags, so use `Magento\Framework\Serialize\Serializer\Json` with `json_encode($value, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT)`, matching the pattern already used in `view/frontend/templates/product/view/configurable/selection.phtml:20`.

### 3. Moderate: cast/ternary precedence bug passes raw request values as feed ID, store ID, microdata flag

`Controller/Adminhtml/Feed/Builder.php:71,75,84`:

```php
$feedId = (int) isset($formData['id']) ? $formData['id'] : 0;
$feed->setStoreId((int) isset($formData['store_id']) ? $formData['store_id'] : 0);
$feed->setUseMicrodata((int) isset($formData['use_microdata']) ? $formData['use_microdata'] : $use_microdata);
```

`(int)` binds tighter than `?:`, so the cast applies to `isset(...)` and the ternary returns the raw, uncast request value. Verified with PHP 8.5.9:

```
php -r '$d=["id"=>"5abc"]; var_dump((int) isset($d["id"]) ? $d["id"] : 0);'   # string(4) "5abc"
php -r '$d=["id"=>["1"]];  var_dump((int) isset($d["id"]) ? $d["id"] : 0);'   # array(1) { [0]=> "1" }
```

Consequences:

- `?id[]=1` (array) reaches `$feed->load($feedId)`, which throws; the catch at line 89 logs a critical and leaves a new, empty feed. Delete, Generate, Test, Viewlog, and Edit then operate on feed ID 0 / a phantom feed, and the user sees a misleading "Feed wasn't found" style outcome instead of a validation error.
- String IDs such as `5abc` or `5 OR 1=1` pass through to the DB layer. No SQL injection: the framework quotes the value, and `5abc` loads feed 5 under MySQL's coercion rules, which is itself surprising behavior worth removing.
- `store_id` and `use_microdata` similarly accept arrays/strings.

Every admin feed controller funnels through `build()`, so the malformed-input handling is uniform and uniformly wrong. Status: verified locally (precedence behavior reproduced; downstream load behavior is framework inference).

Fix: `$feedId = (int) ($formData['id'] ?? 0);` and reject non-scalar input explicitly, for example `if (isset($formData['id']) && !is_scalar($formData['id'])) { throw ... }`.

### 4. Low: stored XSS in the feeds grid via unvalidated `batch_limit`

`Model/Feed.php:493-494` saves `batch_limit` from POST with no validation. `Model/Feed/Schedule.php:111` interpolates it into a translated string containing `<br />`, and `Ui/Component/Listing/Column/Schedules.php:74` places that string unescaped into the grid cell HTML:

```php
$formattedSchedules[] = sprintf('<p %s>%s</p>', self::SCHEDULE_PARAGRAPH_CLASS, $schedule);
```

The edit form itself int-casts the value (`manage-schedules.phtml:157`), so the payload only fires in the grid. Same ACL prerequisite and impact class as findings 1 and 2. Status: verified by code inspection.

Fix: cast to int at save time (`(int) $schedule['batch_limit']`) and escape the formatted schedule in the column (keeping the intended `<br />` via the escaper's allowed-tags parameter).

### 5. Low: `mapMinimumPurchaseAmount` reads the wrong condition level and its legacy fallback cannot work

`Model/Promotions/Provider/Map.php:140-152`:

```php
$res = array('conditions' => array((array)json_decode($conditions)));
...
foreach ($conditions['conditions'] as $condition) {
    if (in_array($condition['attribute'], array('base_subtotal')) ...
```

Two problems:

1. For every modern (JSON-serialized) cart rule, the wrapper makes `$condition` the root combine node, which has `type`/`aggregator`/`value` keys and no `attribute` key. `$condition['attribute']` raises an undefined-key warning, which Magento's registered error handler converts to a `PhpException`, aborting promotions generation for any included rule. Even if the warning were suppressed, the lookup is at the wrong nesting level, so `minimum_purchase_amount` would always be empty. The actual subtotal condition sits two levels down (`conditions[].conditions[]` through a `Product/Found` node).
2. The fallback for legacy PHP-serialized conditions (line 142) injects `SerializerInterface`, which resolves to the JSON serializer. Passing a PHP-serialized string to it throws `InvalidArgumentException`, so the fallback cannot parse the format it exists for.

No unit test covers this method (`Test/Unit/Model/Promotions/Provider/MapTest.php` does not reference it). The promotions observer (`Observer/Promotions/Generator.php:216`) calls it without a local try/catch, so the exception propagates and fails the feed run. Status: static analysis; the error-handler conversion is standard framework behavior, and the wrong-level read follows from the serialized rule shape. Not executed against a live rule.

Fix: walk the condition tree recursively looking for `attribute === 'base_subtotal'`, guard key access with `?? null`, and either drop the legacy branch or use `Magento\Framework\Serialize\Serializer\Serialize` for it inside a try/catch.

Related, same file: `mapEffectiveDates` (line 92), `mapDisplayDates` (line 103), and `mapDates` (lines 115-116) read `$row['date']`, `$row['display']`, `$row['from']`, `$row['to']` without `isset`. Saved widget rows always contain these keys today, but a crafted or legacy config row triggers the same warning-to-exception path. Also `Observer/Promotions/Generator.php:96` reads `$config['promotion']` unconditionally.

### 6. Low: non-numeric log rotation setting throws TypeError during generation

`Model/Generator.php:286`:

```php
if (is_file($logFile) && filesize($logFile) > 1024 * $this->scopeConfig->getValue(self::XML_LOG_ROTATE)) {
```

`mageos_shopping_feed/log/rotate` is an unvalidated text field in `etc/adminhtml/system.xml` (default 512 in `etc/config.xml`). In PHP 8, `1024 * "abc"` throws `TypeError`, and `1024 * ""` throws as well (both verified with PHP 8.5.9). The cron worker catches `Throwable` and marks the feed errored, so impact is a failed generation run with a confusing message, limited to stores that saved a bad value. Status: verified locally for the arithmetic; the cron catch is code inspection.

Fix: add `validate-number` to the system.xml field and cast/defend at the point of use: `$rotateKb = (int) $this->scopeConfig->getValue(self::XML_LOG_ROTATE) ?: 512;`.

### 7. Low: missing `isset` guards on request/config-derived array keys

Each of these throws (via the framework error handler) on crafted input or stale data rather than failing cleanly:

- `Model/Feed.php:558`: `$uploadObject->getId() && $upload['delete']` reads `delete` without `isset`. The UI always posts the field, so only a crafted POST triggers it; the save then fails with an exception instead of a validation message. The ownership check two lines above (rejecting uploads belonging to another feed) is correct and was verified.
- `Ui/Component/Listing/Column/Status.php:107`: `$messages['progress'] . '%'` assumes the key exists whenever status is "processing". A feed left in processing with empty or legacy messages (for example after the interrupted-queue scenario fixed on 2026-09-24) breaks the entire feeds grid render. Guard with `$messages['progress'] ?? null` and fall back to the plain status label.
- `Model/Generator.php:434`: `unset($product, $productAdapter, $row)` references `$productAdapter`, which does not exist in that scope (the variable is `$adapter`). Harmless no-op, but it signals the intended cleanup never happens. Trivial.

### 8. Low: URL and option processor edge cases

- `Model/Product/Mapper/Generic/Simple/Url.php:47-50`: `parse_url()` results are used without checking for `false`. A malformed product URL or base URL produces undefined-key warnings (exceptions under the framework handler) mid-generation. The port-loss bug from the 2026-09-09 review is fixed; current code preserves the port.
- `Model/Product/Processors/Option.php:281`: `parse_url($link)` returning false flows into `parse_str($parts['fragment'], ...)` with the same warning outcome, and the item link is silently emptied.
- `Model/Product/Processors/Option.php` (`_updateConcatenate`): `str_replace($oldValue, ...)` with an empty or null `$oldValue` inserts the replacement between every character of the link. Guard with `if ($oldValue === '' || $oldValue === null) return ...`.

All three require unusual configuration or data; impact is a failed or corrupted row, not a security issue. Status: code inspection.

### 9. Low: unescaped values in grid file columns

`Ui/Component/Listing/Column/File.php:96-110` builds the cell as raw HTML:

```php
$pathHasPub = strpos($_SERVER['DOCUMENT_ROOT'], '/pub');
$path = $pathHasPub > 0 ? str_replace('pub/', '', $filepath) : $filepath;
$item[$name] = '<a href="' . $url . '" target="_blank">' . $url . '</a><br />' . __(...);
```

- `$url` (store base URL plus feed file path) is interpolated into `href` and link text without `escapeUrl`/`escapeHtml`. The path component is constrained by `OutputPath` validation, so practical risk is low, but the value should still be escaped at output.
- `str_replace('pub/', '', $filepath)` replaces every occurrence, not just the leading `pub/`, so a feed directory named `pub-something/pub/x.txt` would produce a wrong URL. Prefix-anchored replacement (`preg_replace('#^/?pub/#', ...)` or `substr`) is the intent.
- `$_SERVER['DOCUMENT_ROOT']` is read unguarded; unset under some SAPIs (deprecated notice in PHP 8.1+, warning for the array key). The message fields (`added`, `exported`, `skipped`, `date`) are generator-written ints/date strings, low risk, also unescaped.
- Line 121: `'Feed file not ready.'` is a hardcoded English string, unlike the translated branch above it.

`Ui/Component/Listing/Column/File/Plugin.php:56-64` repeats the pattern for the promotion file: `str_replace(BP, '', $filepath)` silently does nothing when `BP` is not a path prefix, leaking the absolute server path into the admin grid, and the resulting URL is unescaped. Status: code inspection.

### 10. Informational: spreadsheet formula injection in generated feeds

`Model/Generator.php:597-609` writes mapped attribute values to CSV/TSV without neutralizing leading `=`, `+`, `-`, `@` characters, and `Observer/Promotions/Generator.php:118-120` does the same for promotion titles. A product named `=HYPERLINK(...)` produces a cell that spreadsheet applications execute as a formula when a merchant opens the feed locally. Feed consumers (Google, Meta, TikTok) treat the file as data, so the realistic exposure is merchant-side spreadsheet use, which the merchant controls. Most feed modules accept this tradeoff; flagging for awareness. If desired, prefix cells matching `/^[=+\-@]/` with a tab or apostrophe for CSV outputs only.

### 11. Informational: hardening notes

- `view/adminhtml/templates/feed/edit/tab/categories/category-taxonomy.phtml:112` emits the Google taxonomy list (remote content fetched from `google.com`, cached 30 days) via `jsonEncode()` without HEX flags. The fetch itself is safe: the locale is regex-validated (`Model/Taxonomy/Type/GoogleShopping.php:142-146`) so the URL cannot be steered, and HTTPS is used. A compromised or malicious upstream response containing `</script>` would break out of the script block. Low likelihood, trivial fix (same HEX-flags pattern as finding 2).
- The resolved taxonomy `id` question from earlier reviews: `GoogleShopping::getTaxonomyList()` sets `'id' => $key` (sequential index), which is fine. The stored mapping value is the category path label, and the ID is only a UI key.
- `.github/workflows/ci.yml`: third-party actions (`actions/checkout@v6`, `shivammathur/setup-php@v2`, `graycoreio/github-actions-magento2/...@v8.9.0`) are pinned by mutable tag, not commit SHA, and the reusable `check-extension.yaml` workflow receives `secrets.COMPOSER_AUTH`. Pinning to SHAs limits supply-chain exposure of that credential. Top-level `permissions: contents: read` is good.
- `Model/Generator/Memory.php:104-105`: if `memory_limit` is a non-numeric string that fails the regex, `$matches[1]` is read undefined. Edge case; guard with a `preg_match` result check.
- `Model/Product/Shipping.php:503`: `foreach ($methods as $m)` runs on `getConfig('shipping_methods')`, which has no default in `etc/mageos_shopping_feed.xml`, so an unconfigured feed passes null. The calling mapper wraps this in try/catch and logs, so impact is a logged error and empty shipping cell, but `(array)` casting at the source would silence it.

## Verified clean

These areas were specifically targeted and found solid:

- **Path traversal**: `Model/Feed/OutputPath.php` enforces a base-directory whitelist, per-segment regex, realpath checks, and an extension whitelist. Feed output is confined to `pub/media/mageos-shopping-feed`.
- **Upload credentials**: `Model/Feed/Upload.php` encrypts passwords with `EncryptorInterface`, handles the masked `******` placeholder correctly, and stores in a `text` column (2026-09-24 findings confirmed fixed). `UploaderFactory` falls back to a known class for unknown modes.
- **ACL and CSRF**: every admin controller declares `ADMIN_RESOURCE`; all mutating actions implement `HttpPostActionInterface`; mass actions validate IDs strictly (`Plugin/Adminhtml/NebulaMassAction.php` enforces ctype_digit, range, and a 200-ID cap).
- **IDOR**: `Feed::saveSchedules()` and `Feed::saveUploads()` verify child rows belong to the feed being saved and reject foreign IDs.
- **SQL**: the raw query in `ResourceModel/Generator/Queue/Collection::clean()` is static; `SuggestCategories` uses `addLikeEscape`; category IDs are int-cast; promotion rule loading goes through the ORM.
- **Dangerous functions**: no `eval`, `exec`, `shell_exec`, `system`, `passthru`, native `unserialize` on user data, `create_function`, or variable includes anywhere in the module. All serialization is Magento JSON.
- **Frontend**: `Block/Product/View/Microdata.php` validates the `aid` parameter thoroughly (ctype_digit, child-of-parent membership, enabled status, website assignment), re-verifying the 2026-09-09 fix. Frontend templates escape correctly; `selection.phtml` uses json_encode with full HEX flags; the Hyvä autoselect script validates option names and values.
- **XXE**: feed type XML is read through `Magento\Framework\Config\Dom`, which disables network and entity loading.
- **ReDoS**: find/replace uses `str_replace`, not user-controlled regex.
- **Concurrency**: cron uses flock, and feed/promotion files are written to a temp path then atomically renamed.
- **Templates overall**: beyond the findings above, escaping is consistent and often exemplary (`Model/Adminhtml/FeedRow.php`, `viewlog.phtml`, `test/result.phtml`, the promotions widget).

## Method and limitations

- Manual line-by-line review of all controllers, models, blocks, plugins, observers, UI components, templates, JS, and XML, plus pattern greps for dangerous functions, unescaped output, request parameter flow, and file operations.
- `php -l` passed on all 297 PHP files (PHP 8.5.9).
- Behavior probes run with `php -r` where noted (ternary/cast precedence, PHP 8 arithmetic TypeErrors).
- **Not run**: the PHPUnit suite. `Test/Unit/bootstrap.php` requires `MAGENTO_ROOT` pointing at a full Magento/Mage-OS checkout, which is not present in this environment. phpcs, phpstan, and psalm are not installed. No live admin session, database, or storefront was exercised.
- Findings are labeled "verified" where behavior was reproduced locally or traced end to end in code, and "code inspection" / "framework inference" where confirmation needs a running Magento instance. Findings 5 and 7 in particular depend on the framework's warning-to-exception error handler, which is standard but was not executed here.
